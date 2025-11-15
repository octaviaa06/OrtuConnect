<?php

session_name('SESS_GURU');
session_start();
$_SESSION['last_page'] = 'data_siswa.php';
// Verifikasi role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
  header("Location: ../login/index.php?error=Harap login sebagai guru!");
  exit;
}

// Ambil data siswa dari API
$api_url = "http://ortuconnect.atwebpages.com/api/admin/data_siswa.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    $response = json_encode(["data" => []]);
}
curl_close($ch);

$data = json_decode($response, true);
$siswaList = $data['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Siswa | OrtuConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../guru/sidebar.css">
  <link rel="stylesheet" href="data_siswa.css">
</head>
<body>
<div class="d-flex">
  <!-- SIDEBAR -->
  <?php include('../guru/sidebar.php'); ?>

  <!-- MAIN CONTENT -->
  <div class="flex-grow-1 main-content"
       style="background-image:url('../background/Data Siswa(1).png'); background-size:cover; background-position:center;">
    <div class="container-fluid py-3">

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
        <h4 class="fw-bold text-primary m-0">Data Murid</h4>
        <div class="profile-btn" id="profileToggle">
          <div class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
          <span class="fw-semibold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>
          <div class="profile-card" id="profileCard">
            <h6><?= ucfirst($_SESSION['role']) ?></h6>
            <p><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
            <hr>
            <a href="../logout/logout.php?from=datasiswa" class="logout-btn">
              <img src="../assets/keluar.png" alt="Logout"> Logout
            </a>
          </div>
        </div>
      </div>

      <!-- PENCARIAN -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="search-container flex-grow-1 position-relative" style="max-width: 500px;">
          <img src="../assets/cari.png" alt="Cari" class="search-icon">
          <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari murid berdasarkan nama atau kelas...">
        </div>
      </div>

      <!-- CARD LIST SISWA -->
      <div class="row g-3" id="siswaContainer">
        <?php if (empty($siswaList)): ?>
          <p class="text-muted">Tidak ada data murid.</p>
        <?php else: ?>
          <?php foreach ($siswaList as $siswa): 
            $nama = htmlspecialchars($siswa['nama_siswa']);
            $kata = explode(' ', $nama);
            $inisial = (count($kata) >= 2)
              ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1))
              : strtoupper(substr($kata[0], 0, 2));
          ?>
            <div class="col-md-4 mb-3 siswa-item">
              <div class="card guru-card shadow-sm border-0 p-3 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar-inisial bg-primary text-white me-3"
                       style="width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                    <?= $inisial ?>
                  </div>
                  <div>
                    <h5 class="card-title mb-0"><?= $nama ?></h5>
                    <small><strong></strong> <?= htmlspecialchars($siswa['kelas']); ?></small>
                  </div>
                </div>
                <div class="card-body pt-0 pb-2 px-0">
                  <p class="mb-1"><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($siswa['gender']); ?></p>
                  <p class="mb-1"><strong>Orang Tua:</strong> <?= htmlspecialchars($siswa['nama_ortu']); ?></p>
                  <p class="mb-1"><strong>No. Telp:</strong> <?= htmlspecialchars($siswa['no_telp_ortu']); ?></p>
                  <p class="mb-0"><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($siswa['alamat'])); ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>


  // Profile dropdown
  const profileToggle = document.getElementById('profileToggle');
  const profileCard = document.getElementById('profileCard');
  if (profileToggle) {
    profileToggle.addEventListener('click', () => {
      profileCard.classList.toggle('show');
    });
  }

  // Fitur pencarian
  const searchInput = document.getElementById('searchInput');
  const siswaContainer = document.getElementById('siswaContainer');
  searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase();
    const siswaItems = siswaContainer.querySelectorAll('.siswa-item');
    siswaItems.forEach(item => {
      const text = item.textContent.toLowerCase();
      item.style.display = text.includes(keyword) ? '' : 'none';
    });
  });
</script>
</body>
</html>
