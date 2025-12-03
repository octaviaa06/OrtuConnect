<?php
session_name('SESS_GURU');
session_start();
$active_page = 'absensi_siswa';

// Verifikasi role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// Ambil kelas yang diajar guru dari session (diambil dari data login)
$kelas_guru_session = $_SESSION['kelas'] ?? ''; // Mengambil data kelas dari session login

// Jika kelas dari session adalah string, ubah ke array
if ($kelas_guru_session) {
    // Cek jika kelas adalah string tunggal atau multiple (dipisah koma)
    if (strpos($kelas_guru_session, ',') !== false) {
        $kelas_guru = array_map('trim', explode(',', $kelas_guru_session));
    } else {
        $kelas_guru = [$kelas_guru_session];
    }
} else {
    $kelas_guru = [];
}

// Kelas default adalah kelas pertama jika ada
$kelas_default = !empty($kelas_guru) ? $kelas_guru[0] : '';

// Batasan tanggal: 5 hari ke belakang dari hari ini
$today = date('Y-m-d');
$min_date = date('Y-m-d', strtotime('-5 days'));
$max_date = $today;

// === Fungsi ambil daftar kelas dari API ===
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

// Dapatkan semua kelas dari API
$all_kelas = getDaftarKelas();

// Filter hanya kelas yang diajar guru (jika ada data kelas_guru di session)
$kelasList = [];
if (!empty($kelas_guru)) {
    // Filter array: hanya ambil kelas yang ada di $kelas_guru
    foreach ($all_kelas as $kelas) {
        if (in_array($kelas, $kelas_guru)) {
            $kelasList[] = $kelas;
        }
    }
    
    // Jika tidak ada kelas yang match dengan data API, gunakan kelas dari session langsung
    if (empty($kelasList) && !empty($kelas_guru)) {
        $kelasList = $kelas_guru;
    }
} else {
    // Jika tidak ada data kelas di session, tampilkan semua kelas
    $kelasList = $all_kelas;
}

// Atur kelas yang dipilih
$selected_class = $_GET['kelas'] ?? $kelas_default;
$selected_date  = $_GET['tanggal'] ?? date('Y-m-d');

// Validasi: jika guru mencoba memilih kelas yang bukan miliknya
if ($selected_class && !empty($kelas_guru) && !in_array($selected_class, $kelas_guru)) {
    $selected_class = $kelas_default; // Kembalikan ke kelas default
    echo "<script>
        alert('❌ Anda hanya bisa mengakses kelas yang Anda ajar!');
        window.location.href='absensi_siswa.php?tanggal=$selected_date&kelas=$selected_class';
    </script>";
    exit;
}

// Validasi tanggal yang dipilih
if ($selected_date > $max_date) {
    echo "<script>
        alert('❌ Tidak bisa mengakses tanggal masa depan!');
        window.location.href='absensi_siswa.php?tanggal=$max_date&kelas=$selected_class';
    </script>";
    exit;
} elseif ($selected_date < $min_date) {
    echo "<script>
        alert('❌ Hanya bisa mengakses absensi 5 hari terakhir!');
        window.location.href='absensi_siswa.php?tanggal=$min_date&kelas=$selected_class';
    </script>";
    exit;
}

$absensiList = [];

// Ambil data absensi jika kelas dipilih
if ($selected_class) {
    $api = "https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?kelas=".urlencode($selected_class)."&tanggal=".urlencode($selected_date);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
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
                <div>
                    <h4 class="fw-bold text-primary m-0 page-title">
                        <i class="bi bi-calendar-check me-2"></i>Absensi Siswa
                    </h4>
                    <?php if (!empty($kelas_guru)): ?>
                        <div class="mt-2">
                            <span class="kelas-info-badge">
                                <i class="bi bi-mortarboard"></i>
                                Kelas yang diajar: <?= htmlspecialchars(implode(', ', $kelas_guru)) ?>
                            </span>
                            <?php if ($kelas_default && $selected_class === $kelas_default): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="bi bi-check-circle me-1"></i>Kelas Default
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-area">
                    <?php 
                    $_GET['from'] = $from_param;
                    include "../profil/profil.php"; 
                    ?>
                </div>
            </div>

            <!-- FILTER KELAS & TANGGAL -->
            <form id="filterForm" class="d-flex gap-3 align-items-center mb-5 flex-wrap" method="GET">
                <div>
                    <label class="form-label small text-muted mb-1">Tanggal</label>
                    <input 
                        type="date" 
                        name="tanggal" 
                        class="form-control" 
                        value="<?= htmlspecialchars($selected_date) ?>" 
                        min="<?= $min_date ?>"
                        max="<?= $max_date ?>"
                        onchange="this.form.submit()" 
                        style="max-width:160px;">
                </div>
                
                <div>
                    <label class="form-label small text-muted mb-1">Kelas</label>
                    <?php if (empty($kelasList)): ?>
                        <div class="alert alert-warning py-2" style="max-width:200px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Tidak ada kelas
                        </div>
                    <?php else: ?>
                        <select name="kelas" class="form-select" onchange="this.form.submit()" style="max-width:200px;">
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelasList as $k): ?>
                                <option value="<?= htmlspecialchars($k) ?>" 
                                    <?= $selected_class === $k ? 'selected' : '' ?>
                                    <?= $k === $kelas_default ? 'data-default="true"' : '' ?>>
                                    <?= htmlspecialchars($k) ?>
                                    <?php if ($k === $kelas_default): ?>
                                        <span class="text-success">(Default)</span>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-primary" onclick="simpanAbsensi()" id="btnSimpan">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportPdfModal" <?= empty($kelasList) ? 'disabled' : '' ?>>
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </form>

            <!-- INFO TANGGAL & KELAS -->
            <?php if ($selected_class): ?>
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Kelas:</strong> <?= htmlspecialchars($selected_class) ?> | 
                        <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($selected_date)) ?>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-calendar-range me-1"></i>
                        Rentang tanggal: <?= date('d/m/Y', strtotime($min_date)) ?> - <?= date('d/m/Y', strtotime($max_date)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- DAFTAR ABSENSI -->
            <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                <?php if (!$selected_class): ?>
                    <div class="text-center text-muted p-5">
                        <i class="bi bi-funnel display-6 text-muted mb-3"></i>
                        <p class="fs-5">Silakan pilih kelas terlebih dahulu untuk melihat daftar absensi.</p>
                    </div>
                <?php elseif (empty($absensiList) && $selected_class): ?>
                    <div class="text-center text-muted p-5">
                        <i class="bi bi-people display-6 text-muted mb-3"></i>
                        <p class="fs-5">Tidak ada data siswa di kelas <?= htmlspecialchars($selected_class) ?>.</p>
                        <p class="text-muted">Mungkin belum ada siswa yang terdaftar di kelas ini.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold text-primary">
                            <i class="bi bi-list-check me-2"></i>
                            Daftar Absensi
                        </h5>
                        <div class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">
                            <i class="bi bi-person-video3 me-1"></i>
                            Total: <?= count($absensiList) ?> Siswa
                        </div>
                    </div>
                    
                    <form id="formAbsensi">
                        <input type="hidden" name="tanggal" value="<?= htmlspecialchars($selected_date) ?>">
                        <input type="hidden" name="kelas" value="<?= htmlspecialchars($selected_class) ?>">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="40%">Nama Siswa</th>
                                        <th width="25%">Status Saat Ini</th>
                                        <th width="30%" class="text-center">Status Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $no = 1; 
                                $total_hadir = 0;
                                $total_izin = 0;
                                $total_sakit = 0;
                                $total_alpa = 0;
                                $total_belum = 0;
                                
                                foreach($absensiList as $a): 
                                    // Hitung statistik
                                    if ($a['is_recorded']) {
                                        switch($a['status_absensi']) {
                                            case 'Hadir': $total_hadir++; break;
                                            case 'Izin': $total_izin++; break;
                                            case 'Sakit': $total_sakit++; break;
                                            case 'Alpa': $total_alpa++; break;
                                        }
                                    } else {
                                        $total_belum++;
                                    }
                                ?>
                                    <tr>
                                     <td><?= $no++ ?></td>
<td>
    <?= htmlspecialchars($a['nama_siswa'] ?? 'N/A') ?>
</td>

                                        <td>
                                            <?php if ($a['is_recorded']): ?>
                                                <?php
                                                $badge_class = [
                                                    'Hadir' => 'success',
                                                    'Izin' => 'warning',
                                                    'Sakit' => 'info',
                                                    'Alpa' => 'danger'
                                                ][$a['status_absensi']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $badge_class ?> status-badge">
                                                    <i class="bi bi-<?= 
                                                        $a['status_absensi']==='Hadir' ? 'check-circle' : 
                                                        ($a['status_absensi']==='Izin' ? 'exclamation-circle' : 
                                                        ($a['status_absensi']==='Sakit' ? 'activity' : 'x-circle')) 
                                                    ?> me-1"></i>
                                                    <?= htmlspecialchars($a['status_absensi']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary status-badge">
                                                    <i class="bi bi-dash-circle me-1"></i> Belum diabsen
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="absensi[<?= $a['id_siswa'] ?>][id_siswa]" value="<?= $a['id_siswa'] ?>">
                                            
                                            <?php if ($a['is_recorded']): ?>
                                                <span class="text-muted small">
                                                    <i class="bi bi-lock-fill me-1"></i> Terkunci
                                                </span>
                                            <?php else: ?>
                                                <select name="absensi[<?= $a['id_siswa'] ?>][status]" 
                                                        class="form-select form-select-sm status-absensi-select" 
                                                        onchange="updateStatusColor(this)"
                                                        style="max-width:150px; margin:0 auto;">
                                                    <option value="Hadir">Hadir</option>
                                                    <option value="Izin">Izin</option>
                                                    <option value="Sakit">Sakit</option>
                                                    <option value="Alpa">Alpa</option>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- STATISTIK -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold mb-3">
                                            <i class="bi bi-bar-chart me-2"></i>Statistik Absensi
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-3 col-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-success me-2">●</span>
                                                    <span>Hadir:</span>
                                                    <span class="fw-bold ms-auto"><?= $total_hadir ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-warning me-2">●</span>
                                                    <span>Izin:</span>
                                                    <span class="fw-bold ms-auto"><?= $total_izin ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info me-2">●</span>
                                                    <span>Sakit:</span>
                                                    <span class="fw-bold ms-auto"><?= $total_sakit ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-danger me-2">●</span>
                                                    <span>Alpa:</span>
                                                    <span class="fw-bold ms-auto"><?= $total_alpa ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($total_belum > 0): ?>
                                            <div class="alert alert-warning mt-3 py-2">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                Masih ada <strong><?= $total_belum ?> siswa</strong> yang belum memiliki absensi.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EXPORT PDF -->
<div class="modal fade" id="exportPdfModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                    Export PDF Absensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formExportPdf">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select id="exportClass" class="form-select" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelasList as $k): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $selected_class===$k?'selected':'' ?>>
                                    <?= htmlspecialchars($k) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter Periode</label>
                        <select id="exportFilter" class="form-select" onchange="updateExportDateInput()">
                            <option value="hari">Per Hari</option>
                            <option value="minggu">Per Minggu</option>
                            <option value="bulan">Per Bulan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input 
                            type="date" 
                            id="exportDate" 
                            class="form-control" 
                            value="<?= htmlspecialchars($selected_date) ?>"
                            min="<?= $min_date ?>"
                            max="<?= $max_date ?>"
                            required>
                        <small class="text-muted" id="exportDateInfo">Pilih tanggal untuk periode</small>
                    </div>
                    <div class="alert alert-info" id="exportPeriodInfo" style="display:none;">
                        <strong>Periode:</strong> <span id="periodText"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="exportPDF()" id="btnExportPdf">
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

// Update warna select status
function updateStatusColor(sel) {
    // Hapus semua kelas status sebelumnya
    sel.classList.remove('status-hadir', 'status-izin', 'status-sakit', 'status-alpa');
    // Tambah kelas sesuai nilai
    sel.classList.add('status-' + sel.value.toLowerCase());
}

// Validasi sebelum simpan absensi
async function simpanAbsensi() {
    const kelas = document.querySelector('select[name="kelas"]').value;
    const tanggal = document.querySelector('input[name="tanggal"]').value;
    const btn = document.getElementById('btnSimpan');
    
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

    // Ambil data kelas guru dari PHP (dari session)
    const guruKelas = <?= json_encode($kelas_guru) ?>;
    
    // Validasi: pastikan kelas yang dipilih adalah kelas yang diajar
    if (guruKelas.length > 0 && !guruKelas.includes(kelas)) {
        showNotif('❌ Anda hanya bisa menyimpan absensi untuk kelas yang Anda ajar!', false);
        return;
    }

    btn.disabled = true; 
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    const form = document.getElementById('formAbsensi');
    const formData = new FormData(form);
    const updates = [];
    
    // Kumpulkan data yang akan diupdate
    for (let [key, val] of formData.entries()) {
        if (key.includes('[status]')) {
            const idMatch = key.match(/\[(\d+)\]/);
            if (idMatch) {
                const id = idMatch[1];
                updates.push({ id_murid: id, status: val });
            }
        }
    }

    if (updates.length === 0) {
        showNotif('Tidak ada perubahan untuk disimpan.', false);
        btn.disabled = false; 
        btn.innerHTML = '<i class="bi bi-save"></i> Simpan';
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
        
        if (data.status === 'success') {
            showNotif('✓ Absensi berhasil disimpan!', true);
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotif('❌ ' + (data.message || 'Gagal menyimpan absensi'), false);
        }
    } catch (err) {
        showNotif('❌ Koneksi error, periksa jaringan Anda', false);
    } finally {
        btn.disabled = false; 
        btn.innerHTML = '<i class="bi bi-save"></i> Simpan';
    }
}

// Validasi sebelum export PDF
async function exportPDF() {
    const kelas = document.getElementById('exportClass').value;
    const tanggal = document.getElementById('exportDate').value;
    const btn = document.getElementById('btnExportPdf');
    
    // Validasi di modal export
    if (!kelas) {
        showNotif('⚠ Harap pilih kelas di modal export!', false);
        return;
    }
    
    // Ambil data kelas guru dari PHP (dari session)
    const guruKelas = <?= json_encode($kelas_guru) ?>;
    
    // Validasi: pastikan guru hanya bisa export kelas yang dia ajar
    if (guruKelas.length > 0 && !guruKelas.includes(kelas)) {
        showNotif('❌ Anda hanya bisa export kelas yang Anda ajar!', false);
        return;
    }
    
    if (!tanggal) {
        showNotif('⚠ Harap pilih tanggal di modal export!', false);
        return;
    }

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
        btn.innerHTML = '<i class="bi bi-download"></i> Download PDF';
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
    
    // Format teks berdasarkan filter
    const dateObj = new Date(date);
    if (filter === 'minggu') {
        const startOfWeek = new Date(dateObj);
        startOfWeek.setDate(dateObj.getDate() - dateObj.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        text.textContent = `Minggu: ${formatDate(startOfWeek)} s/d ${formatDate(endOfWeek)}`;
    } else if (filter === 'bulan') {
        const firstDay = new Date(dateObj.getFullYear(), dateObj.getMonth(), 1);
        const lastDay = new Date(dateObj.getFullYear(), dateObj.getMonth() + 1, 0);
        text.textContent = `Bulan: ${formatDate(firstDay)} s/d ${formatDate(lastDay)}`;
    } else {
        text.textContent = `Hari: ${formatDate(dateObj)}`;
    }
}

// Format tanggal ke DD/MM/YYYY
function formatDate(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

// Inisialisasi warna select saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Update warna select yang sudah ada
    const selects = document.querySelectorAll('.status-absensi-select');
    selects.forEach(select => {
        updateStatusColor(select);
    });
    
    // Update info periode
    updateExportDateInput();
    
    // Validasi saat modal export dibuka
    document.getElementById('exportPdfModal').addEventListener('show.bs.modal', function() {
        const currentKelas = document.querySelector('select[name="kelas"]').value;
        if (currentKelas) {
            document.getElementById('exportClass').value = currentKelas;
        }
    });
    
    // Listen perubahan tanggal export
    document.getElementById('exportDate').addEventListener('change', updateExportDateInput);
});

// Validasi form saat submit (mencegah submit tanpa kelas)
document.getElementById('filterForm').addEventListener('submit', function(e) {
    const kelas = document.querySelector('select[name="kelas"]').value;
    if (!kelas) {
        e.preventDefault();
        showNotif('⚠ Harap pilih kelas terlebih dahulu!', false);
    }
});
</script>

</body>
</html>