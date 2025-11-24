<?php
// 1. Pengaturan Awal dan Proteksi Sesi
ob_start();
session_name('SESS_GURU');
session_start();

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
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ch=null;
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return [];
}

// 3. Pengambilan Data Dashboard dari API
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/dashboard_admin.php";
$data = fetchApiData($api_url);

// Inisialisasi variabel untuk menghindari error
$siswa = $data['siswa'] ?? 0;
$total_Kehadiran_Siswa = $data['Total hadir Siswa'] ?? $data['Total hadir Siswa '] ?? 0;
$izin_list = $data['izin_menunggu'] ?? [];
$izin_menunggu_count = count($izin_list);

// 4. Ambil data AGENDA dari endpoint khusus
$current_month = date('m');
$current_year = date('Y');
$agenda_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";
$agenda_data = fetchApiData($agenda_url);
$all_agenda = $agenda_data['data'] ?? [];

// 5. Filter agenda: hanya yang tanggalnya >= hari ini, maks 7 hari ke depan
$today = new DateTime();
$upcoming_agenda = array_filter($all_agenda, function($item) use ($today) {
    $tgl_str = $item['tanggal'] ?? '';
    if (!$tgl_str) return false;
    
    try {
        $tgl = new DateTime($tgl_str);
        $diff = $today->diff($tgl);
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
    <style>
        /* Toast Notifikasi */
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            color: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.success { background-color: #28a745; }
        #toast.error   { background-color: #dc3545; }
    </style>
</head>
<body>
    <div id="toast" role="alert" aria-live="polite"></div>

    <div class="d-flex">
        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content" style="background-image: url('../assets/background/Dashboard Admin.png'); background-size: cover; background-position: center;">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Dashboard Guru</h4>
                    <div class="profile-area"><?php include "../profil/profil.php"; ?></div>
                </div>

                <div class="row g-3 mb-4 mt-3">
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm border-primary dashboard-card">
                            <div class="card-body">
                                <h6 class="text-primary">Jumlah Siswa</h6>
                                <h3><?= htmlspecialchars((string)$siswa) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm border-primary dashboard-card">
                            <div class="card-body">
                                <h6 class="text-primary">Izin Menunggu</h6>
                                <h3><?= $izin_menunggu_count ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm border-primary dashboard-card">
                            <div class="card-body">
                                <h6 class="text-primary">Agenda Terdekat</h6>
                                <h3><?= count($agenda) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-primary mb-3 mt-4">Akses Cepat</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <a href="../guru_absensi/absensi_siswa.php" class="card text-center shadow-sm access-card text-decoration-none">
                            <div class="card-body">
                                <img src="../assets/Absensi.png" class="access-icon mb-2" alt="Absensi">
                                <p class="mb-0 text-dark fw-semibold">Kelola Absensi</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../guru_perizinan/perizinan.php" class="card text-center shadow-sm access-card text-decoration-none">
                            <div class="card-body">
                                <img src="../assets/Perizinan.png" class="access-icon mb-2" alt="Perizinan">
                                <p class="mb-0 text-dark fw-semibold">Proses Perizinan</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../guru kalender/kalender.php" class="card text-center shadow-sm access-card text-decoration-none">
                            <div class="card-body">
                                <img src="../assets/Kalender_Biru.png" class="access-icon mb-2" alt="Kalender">
                                <p class="mb-0 text-dark fw-semibold">Lihat Kalender</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-primary shadow-sm">
                            <div class="card-body">
                                <h6 class="text-primary d-flex align-items-center gap-2">
                                    <img src="../assets/Pesan.png" width="22" alt="Pesan"> Izin Menunggu
                                </h6>
                                <div class="border-top pt-2 mt-2" id="izinContainer">
                                <?php if (empty($izin_list)): ?>
                                    <p class="text-muted small">Tidak ada izin menunggu</p>
                                <?php else: ?>
                                    <?php foreach ($izin_list as $i): ?>
                                        <?php
                                        // Deteksi nama field ID dengan prioritas yang jelas
                                        $id = $i['id'] ?? $i['id_izin'] ?? $i['id_siswa'] ?? 0;
                                        ?>
                                        <div class="izin-item mb-3 p-2 border rounded" data-id="<?= (int)$id ?>">
                                            <p class="mb-1">
                                                <strong><?= htmlspecialchars($i['nama_siswa'] ?? '—') ?></strong>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($i['kelas'] ?? '—') ?></span><br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($i['jenis_izin'] ?? 'Izin') ?> • 
                                                    <?= !empty($i['tanggal_mulai']) ? date('d M Y', strtotime($i['tanggal_mulai'])) : '—' ?>
                                                </small>
                                            </p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-success btn-sm flex-grow-1"
                                                        onclick="updateIzin(<?= (int)$id ?>, 'disetujui', this)">
                                                    ✔ Setujui
                                                </button>
                                                <button class="btn btn-danger btn-sm flex-grow-1"
                                                        onclick="updateIzin(<?= (int)$id ?>, 'ditolak', this)">
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
                        <div class="card border-primary shadow-sm">
                            <div class="card-body">
                                <h6 class="text-primary d-flex align-items-center gap-2">
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

    <script>
        const API_PERIZINAN = "https://ortuconnect.pbltifnganjuk.com/api/admin/perizinan.php";

        // Toast Notifikasi
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast');
            if (!toast) return;
            toast.textContent = message;
            toast.className = isSuccess ? 'success' : 'error';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Approve / Reject Izin
        async function updateIzin(id, status, button) {
            if (!confirm(`Yakin ingin ${status === 'disetujui' ? 'MENYETUJUI' : 'MENOLAK'} izin ini?`)) return;

            const item = button.closest('.izin-item');
            if (!item) return;

            // Disable & loading state
            const buttons = item.querySelectorAll('button');
            buttons.forEach(btn => btn.disabled = true);
            button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${status === 'disetujui' ? 'Menyetujui' : 'Menolak'}...`;

            try {
                const res = await fetch(API_PERIZINAN, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, status: status })
                });

                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();

                if (data.status === 'success') {
                    showToast(`Izin berhasil ${status}!`, true);
                    
                    // Hapus item dengan animasi
                    item.style.transition = 'opacity 0.3s';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        
                        // Update counter
                        const countEl = document.querySelector('.col-md-4:nth-child(2) h3');
                        if (countEl) {
                            let current = parseInt(countEl.textContent) || 0;
                            current = Math.max(0, current - 1);
                            countEl.textContent = current;
                        }

                        // Cek apakah masih ada izin
                        const container = document.getElementById('izinContainer');
                        const remainingItems = container.querySelectorAll('.izin-item');
                        if (remainingItems.length === 0) {
                            container.innerHTML = '<p class="text-muted small">Tidak ada izin menunggu</p>';
                        }
                    }, 300);
                } else {
                    showToast(data.message || 'Gagal memproses izin.', false);
                    resetButtons(item, buttons);
                }
            } catch (err) {
                console.error('Error memproses izin:', err);
                showToast('Gagal menghubungi server. Periksa koneksi.', false);
                resetButtons(item, buttons);
            }
        }

        function resetButtons(item, buttons) {
            buttons[0].innerHTML = '✔ Setujui';
            buttons[1].innerHTML = '✘ Tolak';
            buttons.forEach(btn => btn.disabled = false);
        }
    </script>
</body>
</html>