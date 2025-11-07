<?php
session_start();

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Ambil kelas dari API nyata
function getDaftarKelas() {
    $api_kelas_url = "https://ortuconnect.atwebpages.com/api/admin/absensi.php"; 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_kelas_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("cURL Error for Kelas: " . curl_error($ch));
        curl_close($ch);
        return [];
    }
    curl_close($ch);
    $data = json_decode($response, true);
    return $data['data'] ?? [];
}

// Ambil parameter filter
$selected_class = $_GET['kelas'] ?? '';
$selected_date = $_GET['tanggal'] ?? date('Y-m-d');

// Ambil daftar kelas
$kelasList = getDaftarKelas();

// Ambil daftar absensi dari API
$absensiList = [];
if ($selected_class) {
    $api_absensi_url = "https://ortuconnect.atwebpages.com/api/admin/absensi.php?kelas=" . urlencode($selected_class) . "&tanggal=" . urlencode($selected_date);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_absensi_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);

    if (curl_errno($ch)) $response = json_encode(['data' => []]);
    curl_close($ch);

    $data = json_decode($response, true);
    $absensiList = $data['data'] ?? [];

    // Set default status 'Hadir' jika kosong
    $absensiList = array_map(function($abs) {
        $abs['status_absensi'] = $abs['status_absensi'] ?? 'Hadir';
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
    <div id="sidebar" class="sidebar bg-primary text-white p-3 expanded">
        <div class="text-center mb-4">
            <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="../dashboard_admin/home_admin.php" class="nav-link"><img src="../assets/Dashboard.png" class="icon"><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="../admin data guru/DataGuru.php" class="nav-link"><img src="../assets/Data Guru.png" class="icon"><span>Data Guru</span></a></li>
            <li class="nav-item"><a href="../admin data siswa/DataSiswa.php" class="nav-link"><img src="../assets/Data Siswa.png" class="icon"><span>Data Murid</span></a></li>
            <li class="nav-item"><a href="../admin absensi/Absensi.php" class="nav-link active"><img src="../assets/absensi.png" class="icon"><span>Absensi</span></a></li>
            <li class="nav-item"><a href="../admin perizinan/Perizinan.php" class="nav-link"><img src="../assets/Perizinan.png" class="icon"><span>Perizinan</span></a></li>
            <li class="nav-item"><a href="../admin kalender/Kalender.php" class="nav-link"><img src="../assets/Kalender.png" class="icon"><span>Kalender</span></a></li>
        </ul>
    </div>

    <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
            <h4 class="fw-bold text-primary m-0">Absensi</h4>
            
            <div class="profile-container" id="profileToggle">
                <div class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="profile-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
                
                <div class="card profile-popup" id="profilePopup">
                    <h6 class="fw-bold text-primary m-0"><?= ucfirst($_SESSION['role']) ?></h6>
                    <p class="text-muted mb-2" style="font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
                    <hr style="border-color: rgba(0,0,0,0.1); margin: 8px 0;">
                    <a href="../logout/logout.php?from=absensi" class="logout-link">
                        <svg class="logout-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M160 96c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32V416c0 17.7-14.3 32-32 32H192c-17.7 0-32-14.3-32-32V384h32v32H416V96H192v32H160V96zM96 256c0 17.7 14.3 32 32 32H320v-64H128c-17.7 0-32 14.3-32 32zm80 80v64H320V336H176zm16-80V96H320v144H192zm-16-16H320v-64H176v64zm16 80v64H320V336H192zM128 256h192v64H128V256zM48 256a208 208 0 1 1 416 0A208 208 0 1 1 48 256z"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
            </div>

        <form id="filterForm" class="d-flex gap-3 align-items-center mb-5" action="Absensi.php" method="GET">
            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" onchange="this.form.submit()">
            <select name="kelas" class="form-select" onchange="this.form.submit()">
                <option value="">Pilih Kelas</option>
                <?php foreach($kelasList as $kelas): ?>
                    <option value="<?= htmlspecialchars($kelas) ?>" <?= $selected_class === $kelas ? 'selected' : '' ?>>Kelas <?= htmlspecialchars($kelas) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-primary" onclick="showSaveOptions()">Simpan</button>
        </form>

        <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
            <h5 class="fw-bold mb-4 text-primary">Daftar Absensi</h5>
            <form id="formAbsensi">
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($selected_date) ?>">
                <input type="hidden" name="kelas" value="<?= htmlspecialchars($selected_class) ?>">

                <?php if(empty($absensiList)): ?>
                    <div class="text-center text-muted p-5">Tidak ada data murid untuk Kelas <?= htmlspecialchars($selected_class) ?> pada tanggal ini.</div>
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
<div id="overlay"></div>

<div id="saveOptions">
    <h5 class="mb-3 text-primary fw-bold">Pilih Aksi</h5>
    <button class="btn btn-success w-100" onclick="simpanAbsensi()">💾 Simpan Saja</button>
    <button class="btn btn-danger w-100" onclick="exportAbsensi('pdf')">📄 Ekspor ke PDF</button>
    <button class="btn btn-warning w-100" onclick="exportAbsensi('excel')">📊 Ekspor ke Excel</button>
    <button class="btn btn-secondary w-100 mt-2" onclick="closeSaveOptions()">Batal</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showNotif(message, isSuccess = true) {
    const notifBox = document.getElementById('notifBox');
    notifBox.textContent = message;
    notifBox.style.backgroundColor = isSuccess ? '#28a745' : '#dc3545';
    notifBox.style.display = 'block';
    setTimeout(() => notifBox.style.display = 'none', 3000);
}

// Sidebar dan Profil Toggle
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.status-absensi-select').forEach(updateStatusColor);
    
    const sidebar = document.getElementById('sidebar');
    const toggleSidebar = document.getElementById('toggleSidebar');
    toggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });

    const profileToggle = document.getElementById('profileToggle');
    const profilePopup = document.getElementById('profilePopup'); // ID disesuaikan
    
    if (profileToggle && profilePopup) {
        profileToggle.addEventListener('click', e => { 
            e.stopPropagation(); 
            profilePopup.classList.toggle('show'); 
        });
        
        document.addEventListener('click', e => { 
            const profileContainer = document.getElementById('profileToggle'); 
            if (profileContainer && !profileContainer.contains(e.target)) {
                profilePopup.classList.remove('show');
            }
        });
    }
});

// Warna status
function updateStatusColor(select) {
    select.className = 'form-select status-absensi-select';
    select.classList.add('status-' + select.value.toLowerCase());
}

// Popup save options
function showSaveOptions() {
    document.getElementById("saveOptions").classList.add("active");
    document.getElementById("overlay").classList.add("active");
}
function closeSaveOptions() {
    document.getElementById("saveOptions").classList.remove("active");
    document.getElementById("overlay").classList.remove("active");
}

async function simpanAbsensi() {
    closeSaveOptions();
    const form = document.getElementById('formAbsensi');
    const formData = new FormData(form);
    const absensiUpdates = [];
    const regex = /absensi\[(\d+)\]\[status\]/;

    for(let [key, value] of formData.entries()) {
        const match = key.match(regex);
        if(match){
            const id = match[1];
            const selectEl = document.querySelector(`[name="absensi[${id}][status]"]`);
            if(value !== selectEl.dataset.initialStatus){
                absensiUpdates.push({id_murid: id, status: value});
            }
        }
    }

    if(absensiUpdates.length === 0){
        showNotif("Tidak ada perubahan status absensi.", false);
        return;
    }

    const payload = {
        tanggal: form.querySelector('input[name="tanggal"]').value,
        kelas: form.querySelector('input[name="kelas"]').value,
        absensi: absensiUpdates
    };

    try {
        const res = await fetch("https://ortuconnect.atwebpages.com/api/admin/absensi.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(data.status === "success"){
            showNotif("Absensi berhasil disimpan!", true);
            location.reload();
        } else {
            showNotif("Gagal menyimpan absensi.", false);
        }
    } catch(err){
        showNotif("Terjadi kesalahan koneksi.", false);
    }
}

// Ekspor PDF / Excel (dummy link)
function exportAbsensi(type) {
    closeSaveOptions();
    const kelas = document.querySelector('input[name="kelas"]').value;
    const tanggal = document.querySelector('input[name="tanggal"]').value;
    const url = `export_absensi.php?kelas=${kelas}&tanggal=${tanggal}&type=${type}`;
    window.location.href = url;
}
</script>
</body>
</html>