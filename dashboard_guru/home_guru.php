<?php
// 1. Pengaturan Awal dan Proteksi Sesi
ob_start(); // Memulai output buffering
session_name('SESS_GURU');
session_start();

// Cek login - jika session role tidak ada atau bukan guru, redirect
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Silakan login sebagai Guru");
    exit;
}

// 2. Pengambilan Data API
$api_url = "http://ortuconnect.atwebpages.com/api/admin/dashboard_admin.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$ch = null;
// 3. Pengolahan Data
$data = ($http_code === 200 && $response) ? json_decode($response, true) : [];

// Penyesuaian variabel berdasarkan respons API Admin (diasumsikan sama)
// Pastikan semua variabel diinisialisasi untuk menghindari error jika API gagal
$siswa = $data['siswa'] ?? 0;
// Perhatikan: Menggunakan 'Total hadir Siswa ' sesuai kode asli, namun sebaiknya cek dan perbaiki API key jika ada spasi
$total_Kehadiran_Siswa = $data['Total hadir Siswa '] ?? 0; 
$izin_list = $data['izin_menunggu'] ?? [];
$izin_menunggu_count = count($izin_list);
$agenda = $data['agenda_terdekat'] ?? [];

// Mengakhiri output buffering dan mengirim output
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
</head>
<body>
    <div class="d-flex">
        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content" style="background-image: url('../assets/background/Dashboard Admin.png');">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Dashboard Guru</h4>
                 
                    <div class="profile-area">
    <?php include "../profil/profil.php"; ?>
</div>

                </div>

                <div class="row g-3 mb-4 mt-3">
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm border-primary dashboard-card">
                            <div class="card-body">
                                <h6 class="text-primary">Total Kehadiran Siswa</h6>
                                <h3><?= $total_Kehadiran_Siswa ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm border-primary dashboard-card">
                            <div class="card-body">
                                <h6 class="text-primary">Jumlah Siswa</h6>
                                <h3><?= $siswa ?></h3>
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
                </div>

                <h5 class="fw-bold text-primary mb-3 mt-4">Akses Cepat</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <a href="../guru_absensi/absensi_siswa.php" class="card text-center shadow-sm access-card link-underline-opacity-0">
                            <div class="card-body">
                                <img src="../assets/absensi.png" class="access-icon mb-2" alt="Absensi">
                                <p class="mb-0 text-dark fw-semibold">Kelola Absensi</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../guru_perizinan/perizinan.php" class="card text-center shadow-sm access-card link-underline-opacity-0">
                            <div class="card-body">
                                <img src="../assets/Perizinan.png" class="access-icon mb-2" alt="Perizinan">
                                <p class="mb-0 text-dark fw-semibold">Proses Perizinan</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../guru kalender/kalender.php" class="card text-center shadow-sm access-card link-underline-opacity-0">
                            <div class="card-body">
                                <img src="../assets/Kalender Biru.png" class="access-icon mb-2" alt="Kalender">
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
                                    <img src="../assets/Pesan.png" width="22"> Izin Menunggu
                                </h6>
                                <div class="border-top pt-2 mt-2">
                                    <?php if (empty($izin_list)): ?>
                                        <p class="text-muted">Tidak ada izin menunggu</p>
                                    <?php else: ?>
                                        <?php foreach ($izin_list as $i): ?>
                                            <p><strong><?= htmlspecialchars($i['nama_siswa']) ?></strong> - <?= htmlspecialchars($i['jenis_izin']) ?></p>
                                            <div class="d-flex gap-2 mb-2">
                                                <button class="btn btn-success btn-sm">Setujui</button>
                                                <button class="btn btn-danger btn-sm">Tolak</button>
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
                                    <img src="../assets/Kalender Biru.png" width="22"> Agenda Terdekat
                                </h6>
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($agenda)): ?>
                                        <li class="list-group-item text-muted">Tidak ada agenda</li>
                                    <?php else: ?>
                                        <?php foreach ($agenda as $a): ?>
                                            <li class="list-group-item">
                                                <?= htmlspecialchars($a['nama_kegiatan']) ?> - <?= htmlspecialchars($a['tanggal']) ?>
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



</body>
</html> 