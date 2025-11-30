<?php
session_name('SESS_GURU');
session_start();
$active_page = 'absensi_siswa';

// Verifikasi role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// === Fungsi ambil daftar kelas ===
function getDaftarKelas() {
    $api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?mode=kelas";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return $data['data'] ?? [];
}

$selected_class = $_GET['kelas'] ?? '';
$selected_date  = $_GET['tanggal'] ?? date('Y-m-d');
$kelasList      = getDaftarKelas();
$absensiList    = [];

if ($selected_class) {
    $api = "https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?kelas=".urlencode($selected_class)."&tanggal=".urlencode($selected_date);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    $absensiList = $data['data'] ?? [];

    // Tambah flag: sudah diabsen atau belum
    $absensiList = array_map(function($a) {
        $a['is_recorded'] = !empty($a['status_absensi']);
        return $a;
    }, $absensiList);
}

// Untuk profil.php
$from_param = 'absensi_siswa';
$_GET['from'] = $from_param;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Absensi | OrtuConnect</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS - Gunakan absolute path -->
    <link rel="stylesheet" href="../guru/absensi_siswa.css">
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../guru/sidebar.css">
</head>
<body>

<div class="d-flex">
    <?php include '../guru/sidebar.php'; ?>

    <div class="flex-grow-1 main-content bg-absensi-admin">
        <div class="container-fluid py-3">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                <h4 class="fw-bold text-primary m-0 page-title">Absensi Guru</h4>
                <div class="profile-area">
                    <?php 
                    $_GET['from'] = $from_param;
                    include "../profil/profil.php"; 
                    ?>
                </div>
            </div>

            <!-- FILTER KELAS & TANGGAL -->
            <form id="filterForm" class="d-flex gap-3 align-items-center mb-5 flex-wrap" method="GET">
                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" onchange="this.form.submit()" style="max-width:160px;">
                <select name="kelas" class="form-select" onchange="this.form.submit()" style="max-width:160px;">
                    <option value="">Pilih Kelas</option>
                    <?php foreach($kelasList as $k): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $selected_class === $k ? 'selected' : '' ?>><?= htmlspecialchars($k) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-primary" onclick="simpanAbsensi()">Simpan</button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
                    Export PDF
                </button>
            </form>

            <!-- DAFTAR ABSENSI -->
            <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                <h5 class="fw-bold mb-4 text-primary">Daftar Absensi</h5>
                <form id="formAbsensi">
                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($selected_date) ?>">
                    <input type="hidden" name="kelas" value="<?= htmlspecialchars($selected_class) ?>">

                    <?php if (empty($absensiList)): ?>
                        <div class="text-center text-muted p-5">
                            <?= $selected_class ? "Tidak ada murid di kelas ini pada tanggal tersebut." : "Silakan pilih kelas terlebih dahulu." ?>
                        </div>
                    <?php else: ?>
                        <?php $no = 1; foreach($absensiList as $a): ?>
                            <div class="absensi-item d-flex align-items-center py-3 border-bottom">
                                <div class="col-1 fw-bold"><?= $no++ ?></div>
                                <div class="col-5 fw-semibold"><?= htmlspecialchars($a['nama_siswa'] ?? 'N/A') ?></div>
                                <div class="col-6 d-flex justify-content-end">
                                    <input type="hidden" name="absensi[<?= $a['id_siswa'] ?>][id_siswa]" value="<?= $a['id_siswa'] ?>">

                                    <?php if ($a['is_recorded']): ?>
                                        <span class="badge bg-<?= 
                                            $a['status_absensi']==='Hadir' ? 'success' : 
                                            ($a['status_absensi']==='Izin' ? 'warning' : 
                                            ($a['status_absensi']==='Sakit' ? 'info' : 'danger')) 
                                        ?>" style="font-size:0.95rem; padding:0.5rem 0.75rem;">
                                            <?= htmlspecialchars($a['status_absensi']) ?>
                                        </span>
                                    <?php else: ?>
                                        <select name="absensi[<?= $a['id_siswa'] ?>][status]" class="form-select status-absensi-select" onchange="updateStatusColor(this)">
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

<!-- MODAL EXPORT PDF -->
<div class="modal fade" id="exportPdfModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export PDF Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formExportPdf">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select id="exportClass" class="form-select" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelasList as $k): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $selected_class===$k?'selected':'' ?>><?= htmlspecialchars($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter Periode <span class="text-danger">*</span></label>
                        <select id="exportFilter" class="form-select" onchange="updateExportDateInput()">
                            <option value="hari">Per Hari</option>
                            <option value="minggu">Per Minggu</option>
                            <option value="bulan">Per Bulan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="exportDate" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" required>
                        <small class="text-muted" id="exportDateInfo">Pilih tanggal untuk periode</small>
                    </div>
                    <div class="alert alert-info" id="exportPeriodInfo" style="display:none;">
                        <strong>Periode:</strong> <span id="periodText"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="exportPDF()">Download PDF</button>
            </div>
        </div>
    </div>
</div>

<div id="notifBox" class="notif"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// Notifikasi
function showNotif(msg, success = true) {
    const box = document.getElementById('notifBox');
    box.textContent = msg;
    box.style.backgroundColor = success ? '#28a745' : '#dc3545';
    box.style.borderLeft = `4px solid ${success ? '#1e7e34' : '#c82333'}`;
    
    // Reset dan show dengan animasi
    box.style.display = 'block';
    box.style.transform = 'translateX(400px)';
    box.style.opacity = '0';
    
    setTimeout(() => {
        box.style.transform = 'translateX(0)';
        box.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        box.style.transform = 'translateX(400px)';
        box.style.opacity = '0';
        setTimeout(() => box.style.display = 'none', 400);
    }, 3000);
}

// Update warna select
function updateStatusColor(sel) {
    sel.className = 'form-select status-absensi-select status-' + sel.value.toLowerCase();
}

// Validasi sebelum simpan absensi
async function simpanAbsensi() {
    const kelas = document.querySelector('select[name="kelas"]').value;
    const tanggal = document.querySelector('input[name="tanggal"]').value;
    
    // Validasi kelas
    if (!kelas) {
        showNotif('⚠ Harap pilih kelas terlebih dahulu!', false);
        return;
    }
    
    // Validasi tanggal
    if (!tanggal) {
        showNotif('⚠ Harap pilih tanggal terlebih dahulu!', false);
        return;
    }

    const form = document.getElementById('formAbsensi');
    const btn = document.querySelector('.btn.btn-primary');
    btn.disabled = true; 
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    const formData = new FormData(form);
    const updates = [];
    for (let [key, val] of formData.entries()) {
        if (key.includes('[status]')) {
            const id = key.match(/\[(\d+)\]/)[1];
            updates.push({ id_murid: id, status: val });
        }
    }

    if (updates.length === 0) {
        showNotif('Tidak ada perubahan untuk disimpan.', false);
        btn.disabled = false; 
        btn.innerHTML = 'Simpan';
        return;
    }

    const payload = {
        tanggal: form.querySelector('[name=tanggal]').value,
        kelas: form.querySelector('[name=kelas]').value,
        absensi: updates
    };

    try {
        const res = await fetch('simpan_absensi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        showNotif(data.message || (data.status === 'success' ? '✓ Absensi berhasil disimpan!' : '❌ Gagal menyimpan absensi'), data.status === 'success');
        if (data.status === 'success') setTimeout(() => location.reload(), 1500);
    } catch (err) {
        showNotif('❌ Koneksi error, periksa jaringan Anda', false);
    } finally {
        btn.disabled = false; 
        btn.innerHTML = 'Simpan';
    }
}

// Validasi sebelum export PDF
async function exportPDF() {
    const kelas = document.getElementById('exportClass').value;
    const tanggal = document.getElementById('exportDate').value;
    
    // Validasi di modal export
    if (!kelas) {
        showNotif('⚠ Harap pilih kelas di modal export!', false);
        return;
    }
    
    if (!tanggal) {
        showNotif('⚠ Harap pilih tanggal di modal export!', false);
        return;
    }

    const btn = event.target;
    btn.disabled = true; 
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengunduh...';

    try {
        const res = await fetch('export_pdf.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                kelas, 
                tanggal, 
                filter_type: document.getElementById('exportFilter').value 
            })
        });
        
        if (!res.ok) {
            throw new Error('Gagal mengambil data');
        }
        
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; 
        a.download = `Absensi_${kelas}_${tanggal}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        showNotif('✅ PDF berhasil diunduh!', true);
        bootstrap.Modal.getInstance(document.getElementById('exportPdfModal')).hide();
    } catch (err) {
        showNotif('❌ Gagal export PDF: ' + err.message, false);
    } finally {
        btn.disabled = false; 
        btn.innerHTML = 'Download PDF';
    }
}

// Update info periode di modal
function updateExportDateInput() {
    const filter = document.getElementById('exportFilter').value;
    const date = document.getElementById('exportDate').value;
    const info = document.getElementById('exportDateInfo');
    const period = document.getElementById('exportPeriodInfo');
    const text = document.getElementById('periodText');

    if (!date) { 
        period.style.display = 'none'; 
        return; 
    }
    
    period.style.display = 'block';
    text.textContent = date;
}

// Validasi saat modal export dibuka
document.getElementById('exportPdfModal').addEventListener('show.bs.modal', function() {
    const currentKelas = document.querySelector('select[name="kelas"]').value;
    if (currentKelas) {
        document.getElementById('exportClass').value = currentKelas;
    }
});

document.getElementById('exportDate').addEventListener('change', updateExportDateInput);
document.addEventListener('DOMContentLoaded', updateExportDateInput);
</script>

</body>
</html>