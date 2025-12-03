<?php
ob_start();
session_name('SESS_GURU');
session_start();
$active_page = 'dashboard guru';

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Silakan+login+sebagai+Guru");
    exit;
}

// === DETEKSI BASE URL OTOMATIS (INI YANG MEMBUAT TAMPILAN SAMA DI LOKAL & HOSTING) ===
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';


// Fungsi callAPI
function callAPI($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_FORBID_REUSE => true
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("CURL Error: " . curl_error($ch));
        return false;
    }
    curl_close($ch);
    return json_decode($response, true);
}

function fetchApiData($url) {
    return callAPI($url);
}

// Ambil data dashboard
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/dashboard_admin.php?t=" . time();
$data = fetchApiData($api_url) ?: [];
$siswa = $data['siswa'] ?? 0;

// Ambil data izin menunggu
$izin_response = fetchApiData("https://ortuconnect.pbltifnganjuk.com/api/perizinan.php?t=" . time());
$izin = [];
if (isset($izin_response['data']) && is_array($izin_response['data'])) {
    foreach ($izin_response['data'] as $item) {
        $status = strtolower($item['status'] ?? '');
        if ($status === 'menunggu' || $status === 'pending') {
            $izin[] = $item;
        }
    }
} elseif (isset($izin_response['izin_menunggu'])) {
    $izin = $izin_response['izin_menunggu'];
} elseif (isset($data['izin_menunggu'])) {
    $izin = $data['izin_menunggu'];
}

// Hitung siswa masuk hari ini
$siswa_masuk_hari_ini = 0;
$today = date('Y-m-d');
$kelas_data = fetchApiData("https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?mode=kelas");
$kelas_list = $kelas_data['data'] ?? [];

foreach ($kelas_list as $kelas) {
    $absensi_data = fetchApiData("https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?kelas=" . urlencode($kelas) . "&tanggal=" . urlencode($today)) ?: [];
    foreach ($absensi_data['data'] ?? [] as $abs) {
        if (!empty($abs['status_absensi']) && $abs['status_absensi'] === 'Hadir') {
            $siswa_masuk_hari_ini++;
        }
    }
}
$siswa_tidak_masuk = $siswa - $siswa_masuk_hari_ini;

// Ambil agenda terdekat (7 hari ke depan)
$current_month = date('m');
$current_year = date('Y');
$agenda_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";
$agenda_data = fetchApiData($agenda_url) ?: [];
$all_agenda = $agenda_data['data'] ?? [];

$today_datetime = new DateTime();
$upcoming_agenda = array_filter($all_agenda, function($item) use ($today_datetime) {
    $tgl_str = $item['tanggal'] ?? '';
    if (!$tgl_str) return false;
    try {
        $tgl = new DateTime($tgl_str);
        $diff = $today_datetime->diff($tgl);
        $days = (int)$diff->format('%r%a');
        return $days >= 0 && $days <= 7;
    } catch (Exception $e) {
        return false;
    }
});
usort($upcoming_agenda, function($a, $b) {
    return strcmp($a['tanggal'] ?? '', $b['tanggal'] ?? '');
});
$agenda = array_slice($upcoming_agenda, 0, 5);

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru | OrtuConnect</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSS dengan base_url otomatis (INI YANG MEMBUAT TAMPILAN SAMA DI SEMUA HOSTING) -->
    <link rel="stylesheet" href="<?= $base_url ?>guru.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_url ?>../profil/profil.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_url ?>../guru/sidebar.css?v=<?= time() ?>">
    
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <style>
        /* Background dashboard */
        .main-content {
            background-image: url('<?= $base_url ?>../assets/background/Dashboard Admin.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast" role="alert" aria-live="polite"></div>

    <div class="d-flex">
        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Dashboard Guru</h4>
                    <div class="profile-area"><?php include "../profil/profil.php"; ?></div>
                </div>

                <!-- CARD STATISTIK -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body stat-card-body p-4">
                                <p class="stat-label">Jumlah Siswa</p>
                                <p class="stat-value"><?= htmlspecialchars((string)$siswa) ?></p>
                                <div class="stat-change"><span>↑</span> <span>Total</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body stat-card-body p-4">
                                <p class="stat-label">Izin Menunggu</p>
                                <p class="stat-value"><?= count($izin) ?></p>
                                <div class="stat-change"><span>↑</span> <span>Proses</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body stat-card-body p-4">
                                <p class="stat-label">Siswa Masuk Hari Ini</p>
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-primary mb-3 mt-4">Akses Cepat</h5>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <a href="../guru absensi/absensi_siswa.php" class="text-decoration-none">
                            <div class="card access-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                    <img src="<?= $base_url ?>../assets/Absensi Biru.png" class="access-icon mb-2" alt="Absensi">
                                    <p class="access-text">Kelola Absensi</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="../guru perizinan/perizinan.php" class="text-decoration-none">
                            <div class="card access-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                    <img src="<?= $base_url ?>../assets/Perizinan Biru.png" class="access-icon mb-2" alt="Perizinan">
                                    <p class="access-text">Proses Perizinan</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="../guru kalender/kalender.php" class="text-decoration-none">
                            <div class="card access-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                    <img src="<?= $base_url ?>../assets/Kalender_Biru.png" class="access-icon mb-2" alt="Kalender">
                                    <p class="access-text">Lihat Kalender</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- IZIN & AGENDA -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-primary shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-primary d-flex align-items-center gap-2 mb-3">
                                    <img src="<?= $base_url ?>../assets/Pesan.png" width="22" alt="Izin"> 
                                    Izin Menunggu 
                                    <span class="badge bg-primary"><?= count($izin) ?></span>
                                </h6>
                                <div class="border-top pt-2" id="izinContainer" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (empty($izin)): ?>
                                        <p class="text-muted small mb-0">Tidak ada izin menunggu</p>
                                    <?php else: ?>
                                        <?php foreach ($izin as $i): ?>
                                            <?php
                                            $id_izin = $i['id_izin'] ?? $i['id'] ?? $i['id_perizinan'] ?? 0;
                                            $nama_siswa = $i['nama_siswa'] ?? $i['nama'] ?? $i['nama_lengkap'] ?? 'N/A';
                                            $tanggal_mulai = $i['tanggal_mulai'] ?? $i['tanggal'] ?? '';
                                            $tanggal_selesai = $i['tanggal_selesai'] ?? $i['tanggal_akhir'] ?? '';
                                            $tanggal_display = !empty($tanggal_mulai) ? date('d/m/Y', strtotime($tanggal_mulai)) : '';
                                            if (!empty($tanggal_selesai) && $tanggal_selesai !== $tanggal_mulai) {
                                                $tanggal_display .= ' - ' . date('d/m/Y', strtotime($tanggal_selesai));
                                            }
                                            ?>
                                            <div class="mb-3 pb-2 border-bottom izin-item" data-id="<?= (int)$id_izin ?>">
                                                <p class="mb-1 small">
                                                    <strong><?= htmlspecialchars($nama_siswa) ?></strong>
                                                    <?php if (!empty($i['kelas'])): ?>
                                                        <span class="badge bg-secondary ms-1"><?= htmlspecialchars($i['kelas']) ?></span>
                                                    <?php endif; ?>
                                                    <br>
                                                    <span class="text-muted">
                                                        <?= htmlspecialchars($i['jenis_izin'] ?? 'Izin') ?>
                                                        <?php if ($tanggal_display): ?> • <?= $tanggal_display ?><?php endif; ?>
                                                    </span>
                                                </p>
                                                <?php if (!empty($i['keterangan'])): ?>
                                                    <p class="mb-2 small text-muted fst-italic">"<?= htmlspecialchars(substr($i['keterangan'], 0, 50)) ?><?= strlen($i['keterangan']) > 50 ? '...' : '' ?>"</p>
                                                <?php endif; ?>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-success btn-sm flex-fill btn-setujui" data-id="<?= (int)$id_izin ?>" data-nama="<?= htmlspecialchars($nama_siswa) ?>">Setujui</button>
                                                    <button class="btn btn-danger btn-sm flex-fill btn-tolak" data-id="<?= (int)$id_izin ?>" data-nama="<?= htmlspecialchars($nama_siswa) ?>">Tolak</button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-primary shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-primary d-flex align-items-center gap-2 mb-3">
                                    <img src="<?= $base_url ?>../assets/Kalender_Biru.png" width="22" alt="Agenda"> Agenda Terdekat
                                </h6>
                                <div class="agenda-simple">
                                    <?php if (empty($agenda)): ?>
                                        <div class="text-center py-4">
                                            <p class="text-muted small mb-0">Tidak ada agenda</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($agenda as $a): ?>
                                            <div class="agenda-item-simple">
                                                <div class="agenda-date-simple">
                                                    <?= !empty($a['tanggal']) ? date('d M', strtotime($a['tanggal'])) : '-- ---' ?>
                                                </div>
                                                <div class="agenda-content-simple">
                                                    <strong class="agenda-title-simple"><?= htmlspecialchars($a['nama_kegiatan'] ?? '—') ?></strong>
                                                    <?php if (!empty($a['waktu_mulai'])): ?>
                                                        <span class="agenda-time-simple">• <?= date('H:i', strtotime($a['waktu_mulai'])) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Setujui & Tolak (sama seperti sebelumnya) -->
    <div class="modal fade" id="modalKonfirmasiSetujui" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Konfirmasi Persetujuan Izin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">Apakah Anda yakin ingin menyetujui izin ini?</div>
                    <div class="alert alert-info small"><strong id="namaSiswaSetujui"></strong></div>
                    <p class="small text-muted mb-0">Setelah disetujui, izin tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiSetujui">Ya, Setujui Izin</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAlasanTolak" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Alasan Penolakan Izin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3"><strong id="namaSiswaTolak"></strong></div>
                    <form id="formAlasanTolak">
                        <input type="hidden" id="id_izin_tolak">
                        <div class="mb-3">
                            <label for="alasanTolak" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alasanTolak" rows="4" required placeholder="Contoh: Dokumen tidak lengkap..."></textarea>
                            <small class="text-muted d-block mt-2">Alasan ini akan dikirim ke orang tua</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiTolak">Tolak Izin</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_PERIZINAN = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";
        const USER_ID = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>;
        let currentIdIzin = null;
        const modalSetujui = new bootstrap.Modal('#modalKonfirmasiSetujui');
        const modalTolak = new bootstrap.Modal('#modalAlasanTolak');

        function showToast(msg, success = true) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = success ? 'success' : 'error';
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-setujui')) {
                currentIdIzin = e.target.dataset.id;
                document.getElementById('namaSiswaSetujui').textContent = 'Menyetujui izin dari: ' + e.target.dataset.nama;
                modalSetujui.show();
            }
            if (e.target.classList.contains('btn-tolak')) {
                currentIdIzin = e.target.dataset.id;
                document.getElementById('namaSiswaTolak').textContent = 'Menolak izin dari: ' + e.target.dataset.nama;
                document.getElementById('alasanTolak').value = '';
                modalTolak.show();
            }
        });

        document.getElementById('btnKonfirmasiSetujui').onclick = () => {
            modalSetujui.hide();
            updateStatusIzin(currentIdIzin, "Disetujui");
        };

        document.getElementById('btnKonfirmasiTolak').onclick = () => {
            const alasan = document.getElementById('alasanTolak').value.trim();
            if (!alasan) return showToast("Alasan harus diisi!", false);
            modalTolak.hide();
            updateStatusIzin(currentIdIzin, "Ditolak", alasan);
        };

        function updateStatusIzin(id, status, alasan = null) {
            const item = document.querySelector(`.izin-item[data-id="${id}"]`);
            const buttons = item.querySelectorAll('button');
            buttons.forEach(b => b.disabled = true);

            fetch(API_PERIZINAN, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    id_izin: parseInt(id),
                    status: status,
                    id_guru_verifikasi: USER_ID,
                    alasan_penolakan: alasan
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    showToast(status === 'Disetujui' ? 'Izin disetujui!' : 'Izin ditolak!');
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        item.remove();
                        if (!document.querySelector('.izin-item')) {
                            document.getElementById('izinContainer').innerHTML = '<p class="text-muted small mb-0">Tidak ada izin menunggu</p>';
                        }
                        document.querySelector('.badge.bg-primary').textContent = document.querySelectorAll('.izin-item').length;
                    }, 300);
                } else {
                    showToast("Gagal: " + (data.message || "Unknown error"), false);
                    buttons.forEach(b => b.disabled = false);
                }
            })
            .catch(() => {
                showToast("Gagal koneksi ke server", false);
                buttons.forEach(b => b.disabled = false);
            });
        }

        // Chart
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('attendanceChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Masuk', 'Tidak Masuk'],
                        datasets: [{
                            data: [<?= $siswa_masuk_hari_ini ?>, <?= $siswa_tidak_masuk ?>],
                            backgroundColor: ['#0d6efd', '#e9ecef'],
                            borderWidth: 2,
                            cutout: '70%'
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } },
                    plugins: [{
                        id: 'centerText',
                        beforeDatasetsDraw(chart) {
                            const { ctx, width, height } = chart;
                            ctx.save();
                            ctx.font = 'bold 1.8em sans-serif';
                            ctx.fillStyle = '#0d6efd';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText('<?= $siswa_masuk_hari_ini ?>/<?= $siswa ?>', width/2, height/2);
                        }
                    }]
                });
            }
        });
    </script>
</body>
</html>