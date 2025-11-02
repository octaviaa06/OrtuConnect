<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../index.php?error=Harap login sebagai admin!");
  exit;
}

$api_url = "http://ortuconnect.atwebpages.com/api/admin/dashboard_admin.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$guru = $data['guru'] ?? 0;
$siswa = $data['siswa'] ?? 0;
$izin = $data['izin_menunggu'] ?? [];
$agenda = $data['agenda_terdekat'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin | OrtuConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="dashboard_admin.css">
</head>
<body>
  <div class="d-flex">

    <!-- SIDEBAR -->
    <div id="sidebar" class="sidebar bg-primary text-white p-3 expanded">
      <div class="text-center mb-4">
        <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
      </div>
      <ul class="nav flex-column">
        <li class="nav-item"><a href="home_admin.php" class="nav-link active"><img src="../assets/Dashboard.png" class="icon"><span>Dashboard</span></a></li>
        <li class="nav-item"><a href="../admin data guru/DataGuru.php" class="nav-link"><img src="../assets/Data Guru.png" class="icon"><span>Data Guru</span></a></li>
        <li class="nav-item"><a href="../admin data siswa/DataSiswa.php" class="nav-link"><img src="../assets/Data Siswa.png" class="icon"><span>Data Murid</span></a></li>
        <li class="nav-item"><a href="../admin absensi/Absensi.php" class="nav-link"><img src="../assets/absensi.png" class="icon"><span>Absensi</span></a></li>
        <li class="nav-item"><a href="../admin perizinan/Perizinan.php" class="nav-link"><img src="../assets/Perizinan.png" class="icon"><span>Perizinan</span></a></li>
        <li class="nav-item"><a href="../admin kalender/Kalender.php" class="nav-link"><img src="../assets/Kalender.png" class="icon"><span>Kalender</span></a></li>
      </ul>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="flex-grow-1 main-content" style="background-image: url('../assets/background/Dashboard Admin.png'); background-size: cover;">
      <div class="container-fluid py-3">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
          <h4 class="fw-bold text-primary m-0">Dashboard</h4>
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

        <!-- CARD JUMLAH -->
        <div class="row g-3 mb-4 mt-3">
          <div class="col-md-4">
            <div class="card text-center shadow-sm border-primary dashboard-card">
              <div class="card-body">
                <h6 class="text-primary">Jumlah Guru</h6>
                <h3><?= $guru ?></h3>
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
                <h6 class="text-primary">Kosong</h6>
                <h3>-</h3>
              </div>
            </div>
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
                  <?php if (empty($izin)): ?>
                    <p class="text-muted">Tidak ada izin menunggu</p>
                  <?php else: ?>
                    <?php foreach ($izin as $i): ?>
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
                      <li class="list-group-item"><?= htmlspecialchars($a['judul_kegiatan']) ?> - <?= htmlspecialchars($a['tanggal']) ?></li>
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
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const profileBtn = document.getElementById('profileToggle');
    const profileCard = document.getElementById('profileCard');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });

    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileCard.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!profileBtn.contains(e.target)) {
        profileCard.classList.remove('show');
      }
    });
  </script>
</body>
</html>
