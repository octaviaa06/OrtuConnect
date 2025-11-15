<?php
session_name('ORTUCONNECT_SESSION');
session_start();
$active_page = 'absensi';

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

    $absensiList = array_map(function ($abs) {
        // Jika status kosong, set default 'Hadir' tapi mark sebagai belum diabsen
        if (empty($abs['status_absensi'])) {
            $abs['status_absensi'] = ''; // Tetap kosong agar bisa dibedakan
            $abs['is_recorded'] = false;  // Flag: belum diabsen
        } else {
            $abs['is_recorded'] = true;   // Flag: sudah diabsen
        }
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
            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" onchange="this.form.submit()" style="max-width: 150px;">
            <select name="kelas" class="form-select" onchange="this.form.submit()" style="max-width: 150px;">
                <option value="">Pilih Kelas</option>
                <?php foreach($kelasList as $kelas): ?>
                    <option value="<?= htmlspecialchars($kelas) ?>" <?= $selected_class === $kelas ? 'selected' : '' ?>><?= htmlspecialchars($kelas) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-primary" onclick="simpanAbsensi()">Simpan</button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </button>
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
                        <?php 
                            // Gunakan flag is_recorded untuk cek apakah sudah diabsen
                            $isAbsentRecorded = $abs['is_recorded'] ?? false;
                            $currentStatus = $abs['status_absensi'] ?? '';
                        ?>
                        <div class="absensi-item d-flex align-items-center py-3 border-bottom">
                            <div class="col-1 fw-bold"><?= $no++ ?></div>
                            <div class="col-5 fw-semibold"><?= htmlspecialchars($abs['nama_murid'] ?? 'N/A') ?></div>
                            <div class="col-6 d-flex justify-content-end">
                                <input type="hidden" name="absensi[<?= htmlspecialchars($abs['id_murid']) ?>][id_murid]" value="<?= htmlspecialchars($abs['id_murid']) ?>">
                                
                                <?php if($isAbsentRecorded): ?>
                                    <!-- Jika sudah ada absensi, tampilkan badge bukan dropdown -->
                                    <span class="badge bg-<?= 
                                        $abs['status_absensi'] === 'Hadir' ? 'success' : 
                                        ($abs['status_absensi'] === 'Izin' ? 'warning' : 
                                        ($abs['status_absensi'] === 'Sakit' ? 'info' : 'danger')) 
                                    ?>" style="font-size: 0.95rem; padding: 0.5rem 0.75rem;">
                                        <?= htmlspecialchars($abs['status_absensi']) ?>
                                    </span>
                                <?php else: ?>
                                    <!-- Jika belum ada absensi, tampilkan dropdown -->
                                    <select name="absensi[<?= htmlspecialchars($abs['id_murid']) ?>][status]" class="form-select status-absensi-select" onchange="updateStatusColor(this)">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Alpa">Alpa</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
    </div>
</div>

<!-- Modal Export PDF -->
<div class="modal fade" id="exportPdfModal" tabindex="-1" aria-labelledby="exportPdfLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportPdfLabel">Export PDF Absensi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formExportPdf">
            <div class="mb-3">
                <label for="exportClass" class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                <select id="exportClass" class="form-select" required>
                    <option value="">Pilih Kelas</option>
                    <?php foreach($kelasList as $kelas): ?>
                        <option value="<?= htmlspecialchars($kelas) ?>" <?= $selected_class === $kelas ? 'selected' : '' ?>><?= htmlspecialchars($kelas) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="exportFilter" class="form-label fw-semibold">Filter Periode <span class="text-danger">*</span></label>
                <select id="exportFilter" class="form-select" onchange="updateExportDateInput()">
                    <option value="hari">Per Hari</option>
                    <option value="minggu">Per Minggu</option>
                    <option value="bulan">Per Bulan</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="exportDate" class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                <input type="date" id="exportDate" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" required>
                <small class="text-muted" id="exportDateInfo">Pilih tanggal untuk periode yang diinginkan</small>
            </div>

            <div class="alert alert-info" id="exportPeriodInfo" style="display:none;">
                <strong>Periode:</strong> <span id="periodText"></span>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" onclick="exportPDF()">
            <i class="bi bi-download"></i> Download PDF
        </button>
      </div>
    </div>
  </div>
</div>

<div id="notifBox" class="notif"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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

function getDateRange(type, date) {
    const dateObj = new Date(date);
    let start, end;

    switch(type) {
        case 'minggu':
            const day = dateObj.getDay();
            const diff = dateObj.getDate() - day + (day === 0 ? -6 : 1);
            start = new Date(dateObj.setDate(diff));
            end = new Date(start);
            end.setDate(end.getDate() + 6);
            break;
        case 'bulan':
            start = new Date(dateObj.getFullYear(), dateObj.getMonth(), 1);
            end = new Date(dateObj.getFullYear(), dateObj.getMonth() + 1, 0);
            break;
        default:
            start = new Date(date);
            end = new Date(date);
    }

    return {
        start: start.toISOString().split('T')[0],
        end: end.toISOString().split('T')[0]
    };
}

function formatDateIndo(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
}

function updateExportDateInput() {
    const filter = document.getElementById('exportFilter').value;
    const exportDate = document.getElementById('exportDate').value;
    const dateInfo = document.getElementById('exportDateInfo');
    const periodInfo = document.getElementById('exportPeriodInfo');
    const periodText = document.getElementById('periodText');

    let infoText = '';
    switch(filter) {
        case 'hari':
            infoText = 'Pilih tanggal untuk export';
            break;
        case 'minggu':
            infoText = 'Pilih tanggal dalam minggu yang diinginkan';
            break;
        case 'bulan':
            infoText = 'Pilih tanggal dalam bulan yang diinginkan';
            break;
    }
    
    dateInfo.textContent = infoText;

    if (exportDate) {
        const range = getDateRange(filter, exportDate);
        if (filter === 'hari') {
            periodText.textContent = formatDateIndo(range.start);
        } else {
            periodText.textContent = formatDateIndo(range.start) + ' - ' + formatDateIndo(range.end);
        }
        periodInfo.style.display = 'block';
    }
}

async function exportPDF() {
    const selectedClass = document.getElementById('exportClass').value;
    const selectedDate = document.getElementById('exportDate').value;
    const filterType = document.getElementById('exportFilter').value;

    if (!selectedClass) {
        showNotif("Silakan pilih kelas terlebih dahulu.", false);
        return;
    }

    if (!selectedDate) {
        showNotif("Silakan pilih tanggal terlebih dahulu.", false);
        return;
    }

    const exportBtn = event.target;
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengunduh...';

    try {
        const response = await fetch('export_pdf.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                kelas: selectedClass,
                tanggal: selectedDate,
                filter_type: filterType
            })
        });

        if (!response.ok) {
            throw new Error('Gagal mengunduh PDF');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Absensi_${selectedClass}_${new Date().toISOString().slice(0,10)}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showNotif("PDF berhasil diunduh.", true);
        bootstrap.Modal.getInstance(document.getElementById('exportPdfModal')).hide();
    } catch (err) {
        console.error('Error:', err);
        showNotif("Gagal mengunduh PDF: " + err.message, false);
    } finally {
        exportBtn.disabled = false;
        exportBtn.innerHTML = '<i class="bi bi-download"></i> Download PDF';
    }
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
        const res = await fetch("simpan_absensi.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        
        if (data.status === "success") {
            showNotif(data.message || "Absensi berhasil disimpan.", true);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotif(data.message || "Gagal menyimpan absensi.", false);
        }
    } catch (err) {
        showNotif("Terjadi kesalahan koneksi atau server.", false);
    } finally {
        btn.disabled = false;
        btn.textContent = "Simpan";
    }
}

document.getElementById('exportDate').addEventListener('change', updateExportDateInput);
document.addEventListener('DOMContentLoaded', function() {
    updateExportDateInput();
});
</script>
</body>
</html>