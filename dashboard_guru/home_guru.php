<?php
session_name('SESS_GURU');
session_start();

// Cek login - jika session role tidak ada atau bukan guru, redirect
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Silakan login sebagai Guru");
    exit;
}

ob_start();

$api_url = "http://ortuconnect.atwebpages.com/api/admin/dashboard_admin.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = ($http_code === 200 && $response) ? json_decode($response, true) : [];

$siswa = $data['siswa'] ?? 0;
$total_Kehadiran_Siswa = $data['Total hadir Siswa '] ?? 0;
$izin_list = $data['izin_menunggu'] ?? [];
$izin_menunggu_count = count($izin_list);
$agenda = $data['agenda_terdekat'] ?? [];

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
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content" style="background-image: url('../assets/background/Dashboard Admin.png');">
            <div class="container-fluid py-3">
                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Dashboard Guru</h4>
                    <div class="profile-btn" id="profileToggle">
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center profile-avatar">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                        <span class="fw-semibold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <div class="profile-card" id="profileCard">
                            <h6><?= ucfirst($_SESSION['role']) ?></h6>
                            <p><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
                            <hr>
                            <a href="../logout/logout.php" class="text-danger d-flex align-items-center gap-2 text-decoration-none">
                                <img src="../assets/keluar.png" width="20" alt="Logout"> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU STATISTIK -->
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

                <!-- AKSES CEPAT -->
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
                        <a href="../guru_kalender/kalender.php" class="card text-center shadow-sm access-card link-underline-opacity-0">
                            <div class="card-body">
                                <img src="../assets/Kalender.png" class="access-icon mb-2" alt="Kalender">
                                <p class="mb-0 text-dark fw-semibold">Lihat Kalender</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- IZIN & AGENDA -->
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
                                    <img src="../assets/Kalender.png" width="22"> Agenda Terdekat
                                </h6>
                                <ul class="list-group list-group-flush">
                                    <?php if (empty($agenda)): ?>
                                        <li class="list-group-item text-muted">Tidak ada agenda</li>
                                    <?php else: ?>
                                        <?php foreach ($agenda as $a): ?>
                                            <li class="list-group-item">
                                                <?= htmlspecialchars($a['judul_kegiatan']) ?> - <?= htmlspecialchars($a['tanggal']) ?>
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
        const profileBtn = document.getElementById('profileToggle');
        const profileCard = document.getElementById('profileCard');
        if (profileBtn) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileCard.classList.toggle('show');
            });
            document.addEventListener('click', (e) => {
                if (!profileBtn.contains(e.target)) {
                    profileCard.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>