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

// 2. Fungsi Helper untuk Fetch API
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

// Fungsi fetchApiData yang konsisten
function fetchApiData($url) {
    return callAPI($url);
}

// 3. Pengambilan Data Dashboard dari API
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/dashboard_admin.php?t=" . time();
$data = fetchApiData($api_url) ?: [];

// Inisialisasi variabel untuk menghindari error
$siswa = $data['siswa'] ?? 0;

// Ambil data izin LANGSUNG dari API perizinan dengan filter status Menunggu
$izin_response = fetchApiData("https://ortuconnect.pbltifnganjuk.com/api/perizinan.php?t=" . time());

// Debug: Log response untuk cek struktur data
error_log("Izin Response: " . print_r($izin_response, true));

// Ambil izin dengan status Menunggu saja
$izin = [];
if (isset($izin_response['data']) && is_array($izin_response['data'])) {
    foreach ($izin_response['data'] as $item) {
        $status = strtolower($item['status'] ?? '');
        if ($status === 'menunggu' || $status === 'pending') {
            $izin[] = $item;
        }
    }
} elseif (isset($izin_response['izin_menunggu']) && is_array($izin_response['izin_menunggu'])) {
    // Jika API mengembalikan key 'izin_menunggu' langsung
    $izin = $izin_response['izin_menunggu'];
} elseif (isset($data['izin_menunggu']) && is_array($data['izin_menunggu'])) {
    // Fallback ke dashboard API
    $izin = $data['izin_menunggu'];
}

// Ambil data kelas
$kelas_data = fetchApiData("https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?mode=kelas");
$kelas_list = $kelas_data['data'] ?? [];

// Hitung siswa masuk hari ini
$siswa_masuk_hari_ini = 0;
$today = date('Y-m-d');

foreach ($kelas_list as $kelas) {
    $absensi_data = fetchApiData(
        "https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?kelas=" . 
        urlencode($kelas) . "&tanggal=" . urlencode($today)
    ) ?: [];
    
    foreach ($absensi_data['data'] ?? [] as $abs) {
        if (!empty($abs['status_absensi']) && $abs['status_absensi'] === 'Hadir') {
            $siswa_masuk_hari_ini++;
        }
    }
}

$siswa_tidak_masuk = $siswa - $siswa_masuk_hari_ini;

// 4. Ambil data AGENDA dari endpoint khusus
$current_month = date('m');
$current_year = date('Y');
$agenda_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";
$agenda_data = fetchApiData($agenda_url) ?: [];
$all_agenda = $agenda_data['data'] ?? [];

// 5. Filter agenda: hanya yang tanggalnya >= hari ini, maks 7 hari ke depan
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

// Urutkan berdasarkan tanggal ascending
usort($upcoming_agenda, function($a, $b) {
    return strcmp($a['tanggal'] ?? '', $b['tanggal'] ?? '');
});

// Ambil maksimal 5 agenda terdekat
$agenda = array_slice($upcoming_agenda, 0, 5);

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="guru.css">
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../guru/sidebar.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast" role="alert" aria-live="polite"></div>

    <div class="d-flex">
        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content" style="background-image: url('../assets/background/Dashboard Admin.png'); background-size: cover; background-position: center;">
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
                                <div class="stat-change">
                                    <span>↑</span>
                                    <span>Total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card stat-card shadow-sm">
                            <div class="card-body stat-card-body p-4">
                                <p class="stat-label">Izin Menunggu</p>
                                <p class="stat-value"><?= count($izin) ?></p>
                                <div class="stat-change">
                                    <span>↑</span>
                                    <span>Proses</span>
                                </div>
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
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                                    <img src="../assets/Absensi Biru.png" class="access-icon mb-2" alt="Absensi">
                                    <p class="access-text">Kelola Absensi</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-4">
                        <a href="../guru perizinan/perizinan.php" class="text-decoration-none">
                            <div class="card access-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                                    <img src="../assets/Perizinan Biru.png" class="access-icon mb-2" alt="Perizinan">
                                    <p class="access-text">Proses Perizinan</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-md-4">
                        <a href="../guru kalender/kalender.php" class="text-decoration-none">
                            <div class="card access-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                                    <img src="../assets/Kalender_Biru.png" class="access-icon mb-2" alt="Kalender">
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
                                    <img src="../assets/Pesan.png" width="22" alt="Izin"> 
                                    Izin Menunggu 
                                    <span class="badge bg-primary"><?= count($izin) ?></span>
                                </h6>
                                <div class="border-top pt-2" id="izinContainer" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (empty($izin)): ?>
                                        <p class="text-muted small mb-0">Tidak ada izin menunggu</p>
                                    <?php else: ?>
                                        <?php foreach ($izin as $i): ?>
                                            <?php
                                            // Deteksi ID izin dari berbagai kemungkinan field
                                            $id_izin = $i['id_izin'] ?? $i['id'] ?? $i['id_perizinan'] ?? 0;
                                            
                                            // Deteksi field nama siswa
                                            $nama_siswa = $i['nama_siswa'] ?? $i['nama'] ?? $i['nama_lengkap'] ?? 'N/A';
                                            
                                            // Deteksi field tanggal
                                            $tanggal_mulai = $i['tanggal_mulai'] ?? $i['tanggal'] ?? $i['tanggal_izin'] ?? '';
                                            $tanggal_selesai = $i['tanggal_selesai'] ?? $i['tanggal_akhir'] ?? '';
                                            
                                            // Format tanggal range
                                            $tanggal_display = '';
                                            if (!empty($tanggal_mulai)) {
                                                $tanggal_display = date('d/m/Y', strtotime($tanggal_mulai));
                                                if (!empty($tanggal_selesai) && $tanggal_selesai !== $tanggal_mulai) {
                                                    $tanggal_display .= ' - ' . date('d/m/Y', strtotime($tanggal_selesai));
                                                }
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
                                                        <?php if ($tanggal_display): ?>
                                                            • <?= $tanggal_display ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </p>
                                                <?php if (!empty($i['keterangan'])): ?>
                                                    <p class="mb-2 small text-muted fst-italic">"<?= htmlspecialchars(substr($i['keterangan'], 0, 50)) ?><?= strlen($i['keterangan']) > 50 ? '...' : '' ?>"</p>
                                                <?php endif; ?>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-success btn-sm flex-fill btn-setujui" 
                                                            data-id="<?= (int)$id_izin ?>"
                                                            data-nama="<?= htmlspecialchars($nama_siswa) ?>">
                                                        ✔ Setujui
                                                    </button>
                                                    <button class="btn btn-danger btn-sm flex-fill btn-tolak" 
                                                            data-id="<?= (int)$id_izin ?>"
                                                            data-nama="<?= htmlspecialchars($nama_siswa) ?>">
                                                        ✘ Tolak
                                                    </button>
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
                                    <img src="../assets/Kalender_Biru.png" width="22" alt="Agenda"> Agenda Terdekat
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
                                                    <?php 
                                                    if (!empty($a['tanggal'])) {
                                                        $date = date('d M', strtotime($a['tanggal']));
                                                        echo $date;
                                                    } else {
                                                        echo '-- ---';
                                                    }
                                                    ?>
                                                </div>
                                                <div class="agenda-content-simple">
                                                    <strong class="agenda-title-simple"><?= htmlspecialchars($a['nama_kegiatan'] ?? '—') ?></strong>
                                                    <?php if (!empty($a['waktu_mulai'])): ?>
                                                        <span class="agenda-time-simple">
                                                            • <?= date('H:i', strtotime($a['waktu_mulai'])) ?>
                                                        </span>
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

    <!-- Modal Konfirmasi Persetujuan -->
    <div class="modal fade" id="modalKonfirmasiSetujui" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Konfirmasi Persetujuan Izin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Apakah Anda yakin ingin menyetujui izin ini?</strong>
                    </div>
                    <div class="alert alert-info small">
                        <strong id="namaSiswaSetujui"></strong>
                    </div>
                    <p class="small text-muted mb-0">
                        Setelah disetujui, izin tidak dapat dibatalkan. Pastikan data sudah benar.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiSetujui">Ya, Setujui Izin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alasan Penolakan -->
    <div class="modal fade" id="modalAlasanTolak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Alasan Penolakan Izin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <strong id="namaSiswaTolak"></strong>
                    </div>
                    <form id="formAlasanTolak">
                        <input type="hidden" id="id_izin_tolak" value="">
                        <div class="mb-3">
                            <label for="alasanTolak" class="form-label">Masukkan Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alasanTolak" name="alasan" rows="4" placeholder="Contoh: Dokumen tidak lengkap, format tidak sesuai, dll..." required></textarea>
                            <small class="text-muted d-block mt-2">Alasan ini akan dikirimkan ke orang tua siswa</small>
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
        const modalKonfirmasiSetujui = new bootstrap.Modal(document.getElementById('modalKonfirmasiSetujui'));
        const modalAlasanTolak = new bootstrap.Modal(document.getElementById('modalAlasanTolak'));

        // ========== TOAST NOTIFICATION ==========
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast');
            if (!toast) return;
            
            toast.textContent = message;
            toast.className = isSuccess ? 'success' : 'error';
            toast.classList.add('show');
            
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // ========== BUTTON SETUJUI - Buka Modal Konfirmasi ==========
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-setujui")) {
                const id_izin = e.target.getAttribute("data-id");
                const nama = e.target.getAttribute("data-nama");
                
                if (!id_izin) {
                    showToast("Error: ID izin tidak ditemukan", false);
                    return;
                }
                
                currentIdIzin = id_izin;
                document.getElementById('namaSiswaSetujui').textContent = `Menyetujui izin dari: ${nama}`;
                modalKonfirmasiSetujui.show();
            }
        });

        // ========== KONFIRMASI SETUJUI ==========
        document.getElementById('btnKonfirmasiSetujui').addEventListener("click", function() {
            if (!currentIdIzin) {
                showToast("Error: ID izin tidak ditemukan", false);
                return;
            }
            
            modalKonfirmasiSetujui.hide();
            updateStatusIzin(currentIdIzin, "Disetujui", null, null);
            currentIdIzin = null;
        });

        // ========== BUTTON TOLAK - Buka Modal ==========
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-tolak")) {
                const id_izin = e.target.getAttribute("data-id");
                const nama = e.target.getAttribute("data-nama");
                
                if (!id_izin) {
                    showToast("Error: ID izin tidak ditemukan", false);
                    return;
                }
                
                currentIdIzin = id_izin;
                document.getElementById('alasanTolak').value = '';
                document.getElementById('namaSiswaTolak').textContent = `Menolak izin dari: ${nama}`;
                modalAlasanTolak.show();
            }
        });

        // ========== KONFIRMASI TOLAK ==========
        document.getElementById('btnKonfirmasiTolak').addEventListener("click", function() {
            const alasan = document.getElementById('alasanTolak').value.trim();
            
            if (!alasan) {
                showToast("⚠ Alasan penolakan harus diisi!", false);
                return;
            }
            
            if (!currentIdIzin) {
                showToast("Error: ID izin tidak ditemukan", false);
                return;
            }
            
            modalAlasanTolak.hide();
            updateStatusIzin(currentIdIzin, "Ditolak", alasan, null);
            currentIdIzin = null;
        });

        // ========== UPDATE STATUS IZIN ==========
        function updateStatusIzin(id_izin, status, alasan, buttonClicked) {
            const item = document.querySelector(`.izin-item[data-id="${id_izin}"]`);
            if (!item) {
                showToast("Error: Item izin tidak ditemukan", false);
                return;
            }

            // Disable buttons & show loading
            const buttons = item.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.cursor = 'not-allowed';
            });

            if (status === 'Disetujui') {
                buttons[0].innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Memproses...`;
            } else {
                buttons[1].innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Memproses...`;
            }

            const payload = {
                id_izin: parseInt(id_izin),
                status: status,
                id_guru_verifikasi: USER_ID
            };

            if (alasan) {
                payload.alasan_penolakan = alasan;
            }

            console.log('Mengirim payload:', payload);

            fetch(API_PERIZINAN, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response dari API:', data);

                if (data.success || data.status === 'success') {
                    const pesan = status === 'Disetujui' ? '✓ Izin berhasil disetujui!' : '✗ Izin berhasil ditolak!';
                    showToast(pesan, true);
                    
                    // Hapus item dengan animasi
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        item.remove();
                        
                        // Cek apakah masih ada izin menunggu
                        const container = document.getElementById('izinContainer');
                        const remainingItems = container.querySelectorAll('.izin-item');
                        
                        if (remainingItems.length === 0) {
                            container.innerHTML = '<p class="text-muted small mb-0">Tidak ada izin menunggu</p>';
                        }
                        
                        // Update badge counter
                        updateBadgeCount();
                    }, 300);
                } else {
                    showToast("❌ " + (data.message || "Gagal memperbarui status"), false);
                    resetButtons(buttons);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showToast("❌ Gagal menghubungi server: " + error.message, false);
                resetButtons(buttons);
            });
        }

        // ========== UPDATE BADGE COUNT ==========
        function updateBadgeCount() {
            const container = document.getElementById('izinContainer');
            const count = container.querySelectorAll('.izin-item').length;
            const badge = document.querySelector('.badge.bg-primary');
            if (badge) {
                badge.textContent = count;
            }
        }

        // ========== RESET BUTTON STATE ==========
        function resetButtons(buttons) {
            buttons[0].innerHTML = '✔ Setujui';
            buttons[0].disabled = false;
            buttons[0].style.opacity = '1';
            buttons[0].style.cursor = 'pointer';
            
            buttons[1].innerHTML = '✘ Tolak';
            buttons[1].disabled = false;
            buttons[1].style.opacity = '1';
            buttons[1].style.cursor = 'pointer';
        }

        // ========== AUTO REFRESH IZIN ==========
        function autoRefreshIzin() {
            setInterval(async () => {
                try {
                    const response = await fetch(API_PERIZINAN + '?t=' + Date.now());
                    const data = await response.json();
                    
                    let izinBaru = [];
                    if (data.data && Array.isArray(data.data)) {
                        izinBaru = data.data.filter(item => {
                            const status = (item.status || '').toLowerCase();
                            return status === 'menunggu' || status === 'pending';
                        });
                    }
                    
                    const currentCount = document.querySelectorAll('.izin-item').length;
                    if (izinBaru.length > currentCount) {
                        console.log('Ada izin baru!', izinBaru.length);
                        showToast('📬 Ada izin baru yang perlu diproses!', true);
                        // Optional: bisa auto reload atau update UI
                    }
                } catch (error) {
                    console.error('Error auto refresh:', error);
                }
            }, 30000); // 30 detik
        }

        // ========== CHART.JS - ATTENDANCE DOUGHNUT ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Chart
            const ctx = document.getElementById('attendanceChart');
            if (ctx) {
                const masuk = <?= $siswa_masuk_hari_ini ?>;
                const tidakMasuk = <?= $siswa_tidak_masuk ?>;
                const total = <?= $siswa ?>;

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Masuk', 'Tidak Masuk'],
                        datasets: [{
                            data: [masuk, tidakMasuk],
                            backgroundColor: ['#0d6efd', '#e9ecef'],
                            borderColor: ['#0d6efd', '#e9ecef'],
                            borderWidth: 2,
                            cutout: '70%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    },
                    plugins: [{
                        id: 'textCenter',
                        beforeDatasetsDraw(chart) {
                            const {width, height, ctx} = chart;
                            ctx.restore();
                            ctx.font = `bold ${(height / 200).toFixed(2)}em sans-serif`;
                            ctx.textBaseline = "middle";
                            ctx.fillStyle = '#0d6efd';
                            
                            const text = `${masuk}/${total}`;
                            const textX = Math.round((width - ctx.measureText(text).width) / 2);
                            const textY = height / 2;
                            ctx.fillText(text, textX, textY);
                            ctx.save();
                        }
                    }]
                });
            }

            // Jalankan auto refresh
            autoRefreshIzin();
            
            // Debug: Tampilkan jumlah izin yang berhasil dimuat
            console.log('Total izin menunggu:', document.querySelectorAll('.izin-item').length);
        });
    </script>
</body>
</html>