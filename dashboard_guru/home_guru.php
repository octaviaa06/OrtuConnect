<?php
// 1. Pengaturan Awal dan Proteksi Sesi
ob_start();
session_name('SESS_GURU');
session_start();

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Silakan+login+sebagai+Guru");
    exit;
}

// 2. Fungsi Helper untuk Fetch API
function fetchApiData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return [];
}

// 3. Pengambilan Data Dashboard dari API
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/dashboard_admin.php?t=" . time();
$data = fetchApiData($api_url);

// Inisialisasi variabel untuk menghindari error
$siswa = $data['siswa'] ?? 0;
$izin_list = $data['izin_menunggu'] ?? [];
$izin_menunggu_count = count($izin_list);

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
    );
    
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
$agenda_data = fetchApiData($agenda_url);
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
                                <p class="stat-value"><?= $izin_menunggu_count ?></p>
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
                                    <img src="../assets/Pesan.png" width="22" alt="Pesan"> Izin Menunggu
                                </h6>
                                <div class="border-top pt-2" id="izinContainer">
                                <?php if (empty($izin_list)): ?>
                                    <p class="text-muted small mb-0">Tidak ada izin menunggu</p>
                                <?php else: ?>
                                    <?php foreach ($izin_list as $i): ?>
                                        <?php
                                        // Deteksi ID izin dari berbagai kemungkinan field
                                        $id_izin = $i['id_izin'] ?? $i['id'] ?? $i['id_perizinan'] ?? 0;
                                        ?>
                                        <div class="mb-3 pb-2 border-bottom izin-item" data-id="<?= (int)$id_izin ?>">
                                            <p class="mb-1 small">
                                                <strong><?= htmlspecialchars($i['nama_siswa'] ?? '—') ?></strong>
                                                <?php if (!empty($i['kelas'])): ?>
                                                    <span class="badge bg-secondary ms-1"><?= htmlspecialchars($i['kelas']) ?></span>
                                                <?php endif; ?>
                                                <br>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($i['jenis_izin'] ?? 'Izin') ?>
                                                    <?php if (!empty($i['tanggal_mulai'])): ?>
                                                        • <?= date('d/m/Y', strtotime($i['tanggal_mulai'])) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </p>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-success btn-sm flex-fill btn-setujui"
                                                        data-id="<?= (int)$id_izin ?>">
                                                    ✔ Setujui
                                                </button>
                                                <button class="btn btn-danger btn-sm flex-fill btn-tolak"
                                                        data-id="<?= (int)$id_izin ?>">
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
                                    <img src="../assets/Kalender_Biru.png" width="22" alt="Kalender"> Agenda Terdekat
                                </h6>
                                <ul class="list-group list-group-flush">
                                <?php if (empty($agenda)): ?>
                                    <li class="list-group-item text-muted small py-2">Tidak ada agenda dalam 7 hari ke depan</li>
                                <?php else: ?>
                                    <?php foreach ($agenda as $a): ?>
                                        <li class="list-group-item small py-2">
                                            <strong><?= htmlspecialchars($a['nama_kegiatan'] ?? '—') ?></strong><br>
                                            <span class="text-muted">
                                                <?php 
                                                if (!empty($a['tanggal'])) {
                                                    try {
                                                        $date = new DateTime($a['tanggal']);
                                                        echo $date->format('d M Y');
                                                    } catch (Exception $e) {
                                                        echo '—';
                                                    }
                                                } else {
                                                    echo '—';
                                                }
                                                ?>
                                                <?php if (!empty($a['waktu_mulai'])): ?>
                                                    • <?= htmlspecialchars($a['waktu_mulai']) ?> WIB
                                                <?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
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
                    <form id="formAlasanTolak">
                        <div class="mb-3">
                            <label for="alasanTolak" class="form-label">Masukkan Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alasanTolak" name="alasan" rows="4" placeholder="Contoh: Dokumen tidak lengkap, format tidak sesuai, dll..." required></textarea>
                            <small class="text-muted d-block mt-2">Alasan ini akan dikirimkan ke orang tua siswa</small>
                        </div>
                        <input type="hidden" id="id_izin_tolak" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiTolak">Tolak Izin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi Sukses -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content notification-modal">
                <div class="modal-body text-center p-4">
                    <div class="notification-icon-wrapper mb-3">
                        <svg class="notification-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="notification-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="notification-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                    <h5 class="notification-title mb-2" id="notificationTitle">Berhasil!</h5>
                    <p class="notification-message text-muted mb-4" id="notificationMessage">Izin berhasil diproses.</p>
                    <button type="button" class="btn btn-primary btn-notification-ok" id="btnNotificationOk">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_PERIZINAN = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";
        const modalAlasanTolak = new bootstrap.Modal(document.getElementById('modalAlasanTolak'));
        const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'), {
            backdrop: 'static',
            keyboard: false
        });
        const btnKonfirmasiTolak = document.getElementById('btnKonfirmasiTolak');

        // Show Notification Modal Function
        function showNotification(title, message) {
            document.getElementById('notificationTitle').textContent = title;
            document.getElementById('notificationMessage').textContent = message;
            notificationModal.show();
        }

        // Close Notification and Reload
        document.getElementById('btnNotificationOk').addEventListener('click', () => {
            notificationModal.hide();
            setTimeout(() => location.reload(), 300);
        });

        // Toast Notification Function (for errors)
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast');
            if (!toast) return;
            
            toast.textContent = message;
            toast.className = isSuccess ? 'success' : 'error';
            toast.classList.add('show');
            
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Attach Button Listeners
        attachButtonListeners();

        function attachButtonListeners() {
            // SETUJUI
            document.querySelectorAll(".btn-setujui").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id_izin = this.getAttribute("data-id");
                    
                    if (!id_izin) {
                        showToast("❌ Error: ID izin tidak ditemukan", false);
                        return;
                    }
                    
                    if (confirm("Apakah Anda yakin ingin MENYETUJUI izin ini?")) {
                        updateStatusIzin(id_izin, "Disetujui", null);
                    }
                });
            });

            // TOLAK - Buka modal
            document.querySelectorAll(".btn-tolak").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id_izin = this.getAttribute("data-id");
                    
                    if (!id_izin) {
                        showToast("❌ Error: ID izin tidak ditemukan", false);
                        return;
                    }
                    
                    document.getElementById('id_izin_tolak').value = id_izin;
                    document.getElementById('alasanTolak').value = '';
                    modalAlasanTolak.show();
                });
            });
        }

        // Konfirmasi penolakan
        btnKonfirmasiTolak.addEventListener("click", function() {
            const id_izin = document.getElementById('id_izin_tolak').value;
            const alasan = document.getElementById('alasanTolak').value.trim();
            
            if (!alasan) {
                showToast("⚠️ Alasan penolakan harus diisi!", false);
                return;
            }
            
            modalAlasanTolak.hide();
            updateStatusIzin(id_izin, "Ditolak", alasan);
        });

        // Update Status Izin Function
        function updateStatusIzin(id_izin, status, alasan) {
            const payload = {
                id_izin: parseInt(id_izin),
                status: status,
                id_guru_verifikasi: <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>
            };

            if (alasan) {
                payload.alasan_penolakan = alasan;
            }

            document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => {
                btn.disabled = true;
                if (btn.classList.contains('btn-setujui')) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
                } else if (btn.classList.contains('btn-tolak')) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';
                }
            });

            fetch(API_PERIZINAN, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`.izin-item[data-id="${id_izin}"]`);
                    if (item) {
                        item.style.transition = 'opacity 0.3s';
                        item.style.opacity = '0';
                        
                        setTimeout(() => {
                            item.remove();
                            
                            const container = document.getElementById('izinContainer');
                            const remainingItems = container.querySelectorAll('.izin-item');
                            
                            if (remainingItems.length === 0) {
                                container.innerHTML = '<p class="text-muted small mb-0">Tidak ada izin menunggu</p>';
                            }
                            
                            // Update counter
                            const countEl = document.querySelector('.col-6:nth-child(2) .stat-value, .col-md-4:nth-child(2) .stat-value');
                            if (countEl) {
                                let current = parseInt(countEl.textContent) || 0;
                                countEl.textContent = Math.max(0, current - 1);
                            }
                            
                            if (status === 'Disetujui') {
                                showNotification('Berhasil menyetujui izin!', 'Izin siswa telah disetujui.');
                            } else {
                                showNotification('Berhasil menolak izin!', 'Izin siswa telah ditolak.');
                            }
                        }, 300);
                    }
                } else {
                    showToast("❌ " + (data.message || "Gagal memperbarui status"), false);
                    document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => {
                        btn.disabled = false;
                        if (btn.classList.contains('btn-setujui')) {
                            btn.innerHTML = '✔ Setujui';
                        } else if (btn.classList.contains('btn-tolak')) {
                            btn.innerHTML = '✘ Tolak';
                        }
                    });
                }
            })
            .catch(error => {
                showToast("❌ Error: " + error.message, false);
                document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => {
                    btn.disabled = false;
                    if (btn.classList.contains('btn-setujui')) {
                        btn.innerHTML = '✔ Setujui';
                    } else if (btn.classList.contains('btn-tolak')) {
                        btn.innerHTML = '✘ Tolak';
                    }
                });
            });
        }

        document.getElementById('modalAlasanTolak').addEventListener('hidden.bs.modal', function() {
            attachButtonListeners();
        });

        // Chart.js - Attendance Doughnut
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;

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
        });
    </script>
</body>
</html>