<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'dashboard';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login/index.php?error=Harap login sebagai admin!");
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
  <link rel="stylesheet" href="../profil/profil.css">
  <link rel="stylesheet" href="../admin/sidebar.css">
</head>
<body>
  <!-- TOGGLE BUTTON MOBILE/TABLET -->
  <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
    <span></span>
    <span></span>
    <span></span>
  </button>

  <!-- OVERLAY -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="d-flex">

    <!-- SIDEBAR -->
    <?php include '../admin/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 main-content" 
         style="background-image: url('../assets/background/Dashboard Admin.png'); background-size: cover; background-position: center;">
      <div class="container-fluid">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
          <h4 class="fw-bold text-primary m-0 d-none d-md-block">Dashboard Admin</h4>
          <h5 class="fw-bold text-primary m-0 d-md-none">Dashboard Admin</h5>

          <!-- PROFIL INCLUDED -->
          <?php include '../profil/profil.php'; ?>
        </div>

        <!-- CARD STATISTIK -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-4">
            <div class="card text-center shadow-sm border-primary dashboard-card">
              <div class="card-body p-3">
                <h6 class="text-primary mb-1">Jumlah Guru</h6>
                <h3 class="mb-0"><?= $guru ?></h3>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="card text-center shadow-sm border-primary dashboard-card">
              <div class="card-body p-3">
                <h6 class="text-primary mb-1">Jumlah Siswa</h6>
                <h3 class="mb-0"><?= $siswa ?></h3>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4 d-none d-md-block">
            <div class="card text-center shadow-sm border-primary dashboard-card">
              <div class="card-body p-3">
                <h6 class="text-primary mb-1">Kosong</h6>
                <h3 class="mb-0">-</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- AKSES CEPAT -->
        <h5 class="fw-bold text-primary mb-3">Akses Cepat</h5>
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-4">
            <a href="../admin data guru/DataGuru.php?action=generate" class="text-decoration-none">
              <div class="card text-center shadow-sm access-card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                  <img src="../assets/Data Guru biru.png" class="access-icon mb-2" alt="Guru">
                  <p class="mb-0 text-dark fw-semibold small">Generate Akun Guru</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-4">
            <a href="../admin data siswa/DataSiswa.php?action=generate" class="text-decoration-none">
              <div class="card text-center shadow-sm access-card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                  <img src="../assets/Data Siswa biru.png" class="access-icon mb-2" alt="Siswa">
                  <p class="mb-0 text-dark fw-semibold small">Generate Akun Siswa</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-4">
            <a href="../admin kalender/Kalender.php" class="text-decoration-none">
              <div class="card text-center shadow-sm access-card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                  <img src="../assets/Kalender biru.png" class="access-icon mb-2" alt="Kalender">
                  <p class="mb-0 text-dark fw-semibold small">CRUD Kalender</p>
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
                  <img src="../assets/Pesan.png" width="22" alt="Izin"> Izin Menunggu
                </h6>
                <div class="border-top pt-2">
                  <?php if (empty($izin)): ?>
                    <p class="text-muted small mb-0">Tidak ada izin menunggu</p>
                  <?php else: ?>
                    <?php foreach ($izin as $i): ?>
                      <div class="mb-2">
                        <p class="mb-1 small">
                          <strong><?= htmlspecialchars($i['nama_siswa']) ?></strong><br>
                          <span class="text-muted"><?= htmlspecialchars($i['jenis_izin']) ?></span>
                        </p>
                        <div class="d-flex gap-1">
                          <button class="btn btn-success btn-sm flex-fill">Setujui</button>
                          <button class="btn btn-danger btn-sm flex-fill">Tolak</button>
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
                  <img src="../assets/Kalender Biru.png" width="22" alt="Agenda"> Agenda Terdekat
                </h6>
                <ul class="list-group list-group-flush">
                  <?php if (empty($agenda)): ?>
                    <li class="list-group-item text-muted small py-2">Tidak ada agenda</li>
                  <?php else: ?>
                    <?php foreach ($agenda as $a): ?>
                      <li class="list-group-item small py-2">
                        <strong><?= htmlspecialchars($a['nama_kegiatan']) ?></strong><br>
                        <span class="text-muted"><?= htmlspecialchars($a['tanggal']) ?></span>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- JavaScript untuk Sidebar Mobile & Tablet -->
  <script>
    // Element references
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;

    // Fungsi untuk membuka sidebar
    function openSidebar() {
      sidebar.classList.add('show');
      overlay.classList.add('show');
      toggleBtn.classList.add('active');
      body.classList.add('sidebar-open');
    }

    // Fungsi untuk menutup sidebar
    function closeSidebar() {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
      toggleBtn.classList.remove('active');
      body.classList.remove('sidebar-open');
    }

    // Toggle sidebar saat button diklik
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (sidebar.classList.contains('show')) {
          closeSidebar();
        } else {
          openSidebar();
        }
      });
    }

    // Tutup sidebar saat overlay diklik
    if (overlay) {
      overlay.addEventListener('click', closeSidebar);
    }

    // Tutup sidebar saat link menu diklik (hanya di mobile/tablet)
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    sidebarLinks.forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
          closeSidebar();
        }
      });
    });

    // Auto close sidebar saat resize ke desktop
    let resizeTimer;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        if (window.innerWidth > 992) {
          closeSidebar();
        }
      }, 250);
    });

    // Prevent body scroll dengan touch events
    if (sidebar) {
      let touchStartY = 0;
      
      sidebar.addEventListener('touchstart', function(e) {
        touchStartY = e.touches[0].clientY;
      }, { passive: true });

      sidebar.addEventListener('touchmove', function(e) {
        if (!sidebar.classList.contains('show')) return;
        
        const touchY = e.touches[0].clientY;
        const touchDiff = touchY - touchStartY;
        const scrollTop = sidebar.scrollTop;
        const scrollHeight = sidebar.scrollHeight;
        const clientHeight = sidebar.clientHeight;

        // Prevent overscroll bounce
        if ((scrollTop === 0 && touchDiff > 0) || 
            (scrollTop + clientHeight >= scrollHeight && touchDiff < 0)) {
          e.preventDefault();
        }
      }, { passive: false });
    }

    // Keyboard accessibility (ESC untuk menutup sidebar)
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && sidebar.classList.contains('show')) {
        closeSidebar();
      }
    });
  </script>
</body>
</html>