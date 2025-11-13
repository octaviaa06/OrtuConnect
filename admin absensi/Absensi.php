<?php
session_start();
$active_page = 'absensi';
//include '../admin/sidebar.php';

// Cek login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Ambil daftar kelas dari API
function getDaftarKelas() {
    $api_url = "http://ortuconnect.atwebpages.com/api/admin/absensi.php?mode=kelas";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (empty($response) || $httpCode !== 200) {
        error_log("API Error - Kelas: HTTP $httpCode - Response: $response");
        return [];
    }

    $data = json_decode($response, true);
    return $data['data'] ?? [];
}

$selected_class = $_GET['kelas'] ?? '';
$selected_date = $_GET['tanggal'] ?? date('Y-m-d');
$kelasList = getDaftarKelas();

// Ambil daftar absensi dari API
$absensiList = [];
if ($selected_class) {
    $api_absensi_url = "http://ortuconnect.atwebpages.com/api/admin/absensi.php?kelas=" . urlencode($selected_class) . "&tanggal=" . urlencode($selected_date);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_absensi_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) $response = json_encode(['data' => []]);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        $data = json_decode($response, true);
        $absensiList = $data['data'] ?? [];
    }

    // Set default 'Hadir' untuk yang belum punya status
    $absensiList = array_map(function ($abs) {
        $abs['status_absensi'] = !empty($abs['status_absensi']) ? $abs['status_absensi'] : 'Hadir';
        return $abs;
    }, $absensiList);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Absensi | OrtuConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="absensi.css">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <?php include '../admin/sidebar.php'; ?>

    <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover;">
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
            <h4 class="fw-bold text-primary m-0">Absensi</h4>
            <div class="profile-btn" id="profileToggle">
                <div class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <span class="fw-semibold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>
                <div class="profile-card" id="profileCard">
                    <h6><?= ucfirst($_SESSION['role']) ?></h6>
                    <p><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
                    <hr>
                    <a href="../logout/logout.php?from=absensi" class="logout-btn">
                        <img src="../assets/keluar.png" alt="Logout"> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form id="filterForm" class="d-flex gap-3 align-items-center mb-5" method="GET">
            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" onchange="this.form.submit()">
            <select name="kelas" class="form-select" onchange="this.form.submit()">
                <option value="">Pilih Kelas</option>
                <?php foreach($kelasList as $kelas): ?>
                    <option value="<?= htmlspecialchars($kelas) ?>" <?= $selected_class === $kelas ? 'selected' : '' ?>><?= htmlspecialchars($kelas) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-primary" onclick="simpanAbsensi()">Simpan</button>
        </form>

        <!-- Daftar Absensi -->
        <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
            <h5 class="fw-bold mb-4 text-primary">Daftar Absensi</h5>
            <form id="formAbsensi">
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($selected_date) ?>">
                <input type="hidden" name="kelas" value="<?= htmlspecialchars($selected_class) ?>">

                <?php if(empty($absensiList)): ?>
                    <div class="text-center text-muted p-5">
                        <?= $selected_class ? "Tidak ada data murid untuk kelas ".htmlspecialchars($selected_class)." pada tanggal ini." : "Silakan pilih kelas terlebih dahulu." ?>
                    </div>
                <?php else: ?>
                    <?php $no = 1; foreach($absensiList as $abs): ?>
                        <div class="absensi-item d-flex align-items-center py-3 border-bottom">
                            <div class="col-1 fw-bold"><?= $no++ ?></div>
                            <div class="col-5 fw-semibold"><?= htmlspecialchars($abs['nama_murid'] ?? 'N/A') ?></div>
                            <div class="col-6 d-flex justify-content-end">
                                <input type="hidden" name="absensi[<?= htmlspecialchars($abs['id_murid']) ?>][id_murid]" value="<?= htmlspecialchars($abs['id_murid']) ?>">
                                <select name="absensi[<?= htmlspecialchars($abs['id_murid']) ?>][status]" class="form-select status-absensi-select" data-initial-status="<?= htmlspecialchars($abs['status_absensi']) ?>" onchange="updateStatusColor(this)">
                                    <option value="Hadir" <?= $abs['status_absensi'] === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                    <option value="Izin" <?= $abs['status_absensi'] === 'Izin' ? 'selected' : '' ?>>Izin</option>
                                    <option value="Sakit" <?= $abs['status_absensi'] === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                    <option value="Alpa" <?= $abs['status_absensi'] === 'Alpa' ? 'selected' : '' ?>>Alpa</option>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
    </div>
</div>

<div id="notifBox" class="notif"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showNotif(message, isSuccess = true) {
    const notifBox = document.getElementById('notifBox');
    notifBox.textContent = message;
    notifBox.style.backgroundColor = isSuccess ? '#28a745' : '#dc3545';
    notifBox.style.display = 'block';
    setTimeout(() => notifBox.style.display = 'none', 3000);
}

function updateStatusColor(select) {
    select.className = 'form-select status-absensi-select status-' + select.value.toLowerCase();
}

async function simpanAbsensi() {
    const form = document.getElementById('formAbsensi');
    const btn = document.querySelector('.btn.btn-primary');
    btn.disabled = true;
    btn.textContent = "Menyimpan...";

    const formData = new FormData(form);
    const absensiUpdates = [];
    const regex = /absensi\[(\d+)\]\[status\]/;

    for (let [key, value] of formData.entries()) {
        const match = key.match(regex);
        if (match) {
            const id = match[1];
            absensiUpdates.push({ id_murid: id, status: value });
        }
    }

    if (absensiUpdates.length === 0) {
        showNotif("Tidak ada data untuk disimpan.", false);
        btn.disabled = false;
        btn.textContent = "Simpan";
        return;
    }

    const payload = {
        tanggal: form.querySelector('input[name="tanggal"]').value,
        kelas: form.querySelector('input[name="kelas"]').value,
        absensi: absensiUpdates
    };

    try {
        console.log("DEBUG - Payload:", JSON.stringify(payload, null, 2));
        
        const res = await fetch("simpan_absensi.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        console.log("DEBUG - Response API:", data);
        
        if (data.status === "success") {
            showNotif(data.message || "Absensi berhasil disimpan.", true);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotif(data.message || "Gagal menyimpan absensi.", false);
        }
    } catch (err) {
        console.log("DEBUG - Error:", err);
        showNotif("Terjadi kesalahan koneksi atau server.", false);
    } finally {
        btn.disabled = false;
        btn.textContent = "Simpan";
    }
}
</script>
</body>
</html>