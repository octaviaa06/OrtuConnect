<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'dashboard';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login/index.php?error=Harap login sebagai admin!");
  exit;
}

// Ambil data dashboard
$api_url = "http://ortuconnect.atwebpages.com/api/admin/dashboard_admin.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$ch = null;


$data = json_decode($response, true);
$guru = $data['guru'] ?? 0;
$siswa = $data['siswa'] ?? 0;
$izin = $data['izin_menunggu'] ?? [];
$agenda = $data['agenda_terdekat'] ?? [];

// 👇 AMBIL DATA ABSENSI HARI INI DARI API
$today = date('Y-m-d');
// API endpoint untuk ambil semua kelas dulu
$api_kelas_url = "http://ortuconnect.atwebpages.com/api/admin/absensi.php?mode=kelas";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_kelas_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$ch = null;


$kelas_data = json_decode($response, true);
$kelas_list = $kelas_data['data'] ?? [];

// Loop semua kelas dan hitung siswa yang masuk
$siswa_masuk_hari_ini = 0;
$total_absensi_recorded = 0;

foreach ($kelas_list as $kelas) {
    // Ambil data absensi per kelas untuk hari ini
    $api_absensi_url = "http://ortuconnect.atwebpages.com/api/admin/absensi.php?kelas=" . urlencode($kelas) . "&tanggal=" . urlencode($today);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_absensi_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
   $ch = null;

    
    $absensi_data = json_decode($response, true);
    $absensi_list = $absensi_data['data'] ?? [];
    
    // Hitung siswa yang masuk dari API
    foreach ($absensi_list as $abs) {
        if (!empty($abs['status_absensi'])) {
            $total_absensi_recorded++;
            // Hitung yang masuk (Hadir, Izin, Sakit = dianggap masuk)
            if (in_array($abs['status_absensi'], ['Hadir'])) {
                $siswa_masuk_hari_ini++;
            }
        }
    }
}

$siswa_tidak_masuk = $siswa - $siswa_masuk_hari_ini;
// 👆 AMBIL DATA ABSENSI HARI INI DARI API
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>


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
            <div class="card stat-card shadow-sm">
              <div class="card-body stat-card-body p-4">
                <div class="icon-stat"></div>
                <p class="stat-label">Jumlah Guru</p>
                <p class="stat-value"><?= $guru ?></p>
                <div class="stat-change">
                  <span>↑</span>
                  <span>Aktif</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="card stat-card shadow-sm">
              <div class="card-body stat-card-body p-4">
                <div class="icon-stat"></div>
                <p class="stat-label">Jumlah Siswa</p>
                <p class="stat-value"><?= $siswa ?></p>
                <div class="stat-change">
                  <span>↑</span>
                  <span>Total</span>
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

        <!-- AKSES CEPAT -->
        <h5 class="fw-bold text-primary mb-3">Akses Cepat</h5>
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-4">
            <a href="../admin data guru/DataGuru.php?action=generate" class="text-decoration-none">
              <div class="card access-card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                  <img src="../assets/Data Guru biru.png" class="access-icon" alt="Guru">
                  <p class="access-text">Buat Akun Guru</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-4">
            <a href="../admin data siswa/DataSiswa.php?action=generate" class="text-decoration-none">
              <div class="card access-card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                  <img src="../assets/Data Siswa biru.png" class="access-icon" alt="Siswa">
                  <p class="access-text">Buat Akun Orang Tua</p>
                </div>
              </div>
            </a>
          </div>
          <div class="col-12 col-md-4">
            <a href="../admin kalender/Kalender.php" class="text-decoration-none">
              <div class="card access-card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                  <img src="../assets/Kalender biru.png" class="access-icon" alt="Kalender">
                  <p class="access-text">Buat Agenda</p>
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
  <script>
    const masuk = <?= $siswa_masuk_hari_ini ?>;      // Data siswa masuk dari API
    const tidakMasuk = <?= $siswa_tidak_masuk ?>;    // Data siswa tidak masuk (kalkulasi)
    const total = <?= $siswa ?>;                     // Total siswa dari API

    document.addEventListener('DOMContentLoaded', function() {
      const ctx = document.getElementById('attendanceChart');
      
      if (ctx) {
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
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return context.label + ': ' + context.parsed;
                  }
                }
              }
            }
          },
          plugins: [{
            id: 'textCenter',
            beforeDatasetsDraw(chart) {
              const {width, height, ctx} = chart;
              ctx.restore();
              const fontSize = (height / 200).toFixed(2);
              ctx.font = `bold ${fontSize}em sans-serif`;
              ctx.textBaseline = "middle";
              ctx.fillStyle = '#0d6efd';
              
              const text = masuk + "/" + total; 
              const textX = Math.round((width - ctx.measureText(text).width) / 2);
              const textY = height / 2;
              ctx.fillText(text, textX, textY);
              ctx.save();
            }
          }]
        });
      }
    });
  </script>
</body>
</html>