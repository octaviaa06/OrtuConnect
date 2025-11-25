<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'dashboard';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Function untuk call API
function callAPI($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Ambil data dashboard
$data = callAPI("https://ortuconnect.pbltifnganjuk.com/api/admin/dashboard_admin.php");
$guru = $data['guru'] ?? 0;
$siswa = $data['siswa'] ?? 0;
$izin = $data['izin_menunggu'] ?? [];
$agenda = $data['agenda_terdekat'] ?? [];

// Ambil data kelas
$kelas_data = callAPI("https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?mode=kelas");
$kelas_list = $kelas_data['data'] ?? [];

// Hitung siswa masuk hari ini
$siswa_masuk_hari_ini = 0;
$today = date('Y-m-d');

foreach ($kelas_list as $kelas) {
    $absensi_data = callAPI(
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
  <!-- Toast Notification -->
  <div id="toast" role="alert" aria-live="polite"></div>

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
          <?php include '../profil/profil.php'; ?>
        </div>

        <!-- CARD STATISTIK -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-4">
            <div class="card stat-card shadow-sm">
              <div class="card-body stat-card-body p-4">
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
          <?php
          $quick_links = [
              ['url' => '../admin data guru/DataGuru.php?action=generate', 'icon' => '../assets/Data_Guru_Biru.png', 'text' => 'Buat Akun Guru'],
              ['url' => '../admin data siswa/DataSiswa.php?action=generate', 'icon' => '../assets/Data_Siswa_Biru.png', 'text' => 'Buat Akun Orang Tua'],
              ['url' => '../admin kalender/Kalender.php', 'icon' => '../assets/Kalender_biru.png', 'text' => 'Buat Agenda']
          ];
          
          foreach ($quick_links as $link):
          ?>
          <div class="col-6 col-md-4">
            <a href="<?= $link['url'] ?>" class="text-decoration-none">
              <div class="card access-card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center w-100">
                  <img src="<?= $link['icon'] ?>" class="access-icon" alt="<?= $link['text'] ?>">
                  <p class="access-text"><?= $link['text'] ?></p>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- IZIN & AGENDA -->
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-primary shadow-sm h-100">
              <div class="card-body">
                <h6 class="text-primary d-flex align-items-center gap-2 mb-3">
                  <img src="../assets/Pesan.png" width="22" alt="Izin"> Izin Menunggu
                </h6>
                <div class="border-top pt-2" id="izinContainer">
                  <?php if (empty($izin)): ?>
                    <p class="text-muted small mb-0">Tidak ada izin menunggu</p>
                  <?php else: ?>
                    <?php foreach ($izin as $i): ?>
                      <?php
                      // Deteksi ID izin dari berbagai kemungkinan field
                      $id_izin = $i['id'] ?? $i['id_izin'] ?? $i['id_perizinan'] ?? 0;
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
                          <button class="btn btn-success btn-sm flex-fill" 
                                  onclick="updateIzin(<?= (int)$id_izin ?>, 'disetujui', this)">
                            ✔ Setujui
                          </button>
                          <button class="btn btn-danger btn-sm flex-fill" 
                                  onclick="updateIzin(<?= (int)$id_izin ?>, 'ditolak', this)">
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
                <ul class="list-group list-group-flush">
                  <?php if (empty($agenda)): ?>
                    <li class="list-group-item text-muted small py-2">Tidak ada agenda</li>
                  <?php else: ?>
                    <?php foreach ($agenda as $a): ?>
                      <li class="list-group-item small py-2">
                        <strong><?= htmlspecialchars($a['nama_kegiatan'] ?? '—') ?></strong><br>
                        <span class="text-muted">
                          <?php 
                          if (!empty($a['tanggal'])) {
                              $date = date('d M Y', strtotime($a['tanggal']));
                              echo htmlspecialchars($date);
                          } else {
                              echo '—';
                          }
                          ?>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const API_PERIZINAN = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";

    // Toast Notification Function
    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        
        toast.textContent = message;
        toast.className = isSuccess ? 'success' : 'error';
        toast.classList.add('show');
        
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Update Izin Function (Setujui/Tolak)
    async function updateIzin(id, status, button) {
        if (!confirm(`Yakin ingin ${status === 'disetujui' ? 'MENYETUJUI' : 'MENOLAK'} izin ini?`)) return;

        const item = button.closest('.izin-item');
        if (!item) return;

        // Disable buttons & show loading
        const buttons = item.querySelectorAll('button');
        buttons.forEach(btn => btn.disabled = true);
        button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${status === 'disetujui' ? 'Menyetujui...' : 'Menolak...'}`;

        try {
            const res = await fetch(API_PERIZINAN, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    id: id, 
                    status: status 
                })
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
                    
                    // Cek apakah masih ada izin
                    const container = document.getElementById('izinContainer');
                    const remainingItems = container.querySelectorAll('.izin-item');
                    
                    if (remainingItems.length === 0) {
                        container.innerHTML = '<p class="text-muted small mb-0">Tidak ada izin menunggu</p>';
                    }
                }, 300);
            } else {
                showToast(data.message || 'Gagal memproses izin.', false);
                resetButtons(buttons);
            }
        } catch (err) {
            console.error('Error memproses izin:', err);
            showToast('Gagal menghubungi server. Periksa koneksi.', false);
            resetButtons(buttons);
        }
    }

    function resetButtons(buttons) {
        buttons[0].innerHTML = '✔ Setujui';
        buttons[1].innerHTML = '✘ Tolak';
        buttons.forEach(btn => btn.disabled = false);
    }

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