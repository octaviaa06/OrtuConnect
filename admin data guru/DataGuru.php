<?php
session_start();

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login/index.php?error=Harap login sebagai admin!");
  exit;
}

// Ambil data guru dari API
$api_url = "https://ortuconnect.atwebpages.com/api/admin/data_guru.php";
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
$guruList = $data['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Guru | OrtuConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="d-flex">
  <!-- SIDEBAR -->
  <div id="sidebar" class="sidebar bg-primary text-white p-3 expanded">
    <div class="text-center mb-4">
      <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
    </div>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="../dashboard_admin/home_admin.php" class="nav-link"><img src="../assets/Dashboard.png" class="icon"><span>Dashboard</span></a></li>
      <li class="nav-item"><a href="../admin data guru/DataGuru.php" class="nav-link active"><img src="../assets/Data Guru.png" class="icon"><span>Data Guru</span></a></li>
      <li class="nav-item"><a href="../admin data siswa/DataSiswa.php" class="nav-link"><img src="../assets/Data Siswa.png" class="icon"><span>Data Murid</span></a></li>
      <li class="nav-item"><a href="../admin absensi/Absensi.php" class="nav-link"><img src="../assets/absensi.png" class="icon"><span>Absensi</span></a></li>
      <li class="nav-item"><a href="../admin perizinan/Perizinan.php" class="nav-link"><img src="../assets/Perizinan.png" class="icon"><span>Perizinan</span></a></li>
      <li class="nav-item"><a href="../admin kalender/Kalender.php" class="nav-link"><img src="../assets/Kalender.png" class="icon"><span>Kalender</span></a></li>
    </ul>
  </div>

  <!-- MAIN CONTENT -->
  <div class="flex-grow-1 main-content" 
       style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
    <div class="container-fluid py-3">

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
        <h4 class="fw-bold text-primary m-0">Data Guru</h4>
        <div class="profile-btn" id="profileToggle">
          <div class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
          <span class="fw-semibold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>
          <div class="profile-card" id="profileCard">
            <h6><?= ucfirst($_SESSION['role']) ?></h6>
            <p><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
            <hr>
<a href="../logout/logout.php?from=dataguru" class="logout-btn">
  <img src="../assets/keluar.png" alt="Logout"> Logout
</a>


          </div>
        </div>
      </div>

      <!-- HEADER TAMBAH & PENCARIAN -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="search-container flex-grow-1 position-relative" style="max-width: 500px;">
          <img src="../assets/cari.png" alt="Cari" class="search-icon">
          <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari guru berdasarkan nama, NIP, atau email...">
        </div>

        <button class="btn btn-primary rounded-3 px-4" id="btnTambahGuru">
          <span style="font-weight:600;">+ Tambah Guru</span>
        </button>
      </div>

      <!-- CARD LIST GURU -->
<div class="row g-3" id="guruContainer">
  <?php if (empty($guruList)): ?>
    <p class="text-muted">Tidak ada data guru.</p>
  <?php else: ?>
    <?php foreach ($guruList as $guru): 
      $nama = htmlspecialchars($guru['nama_guru']);
      $kata = explode(' ', $nama);
      $inisial = (count($kata) >= 2)
        ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1))
        : strtoupper(substr($kata[0], 0, 2));
    ?>
      <div class="col-md-4 mb-3 guru-item">
        <div class="card guru-card shadow-sm border-0 p-3 d-flex flex-column justify-content-between" 
             style="border-radius: 16px; transition: all 0.25s ease;">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-inisial bg-primary text-white me-3" 
                 style="width: 50px; height: 50px; border-radius: 50%; font-weight: bold; font-size: 18px;
                        display: flex; align-items: center; justify-content: center;">
              <?= $inisial ?>
            </div>
            <div>
              <h5 class="card-title mb-0"><?= $nama ?></h5>
              <small class="mb-1"><strong>NIP </strong><?= htmlspecialchars($guru['nip']); ?></small>
            </div>
          </div>

          <div class="card-body pt-0 pb-2 px-0">
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($guru['email']); ?></p>
            <p class="mb-1"><strong>No. Telp:</strong> <?= htmlspecialchars($guru['no_telp']); ?></p>
            <p class="mb-0"><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($guru['alamat'])); ?></p>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-light border-0 p-1" onclick="editGuru('<?= $guru['id_guru'] ?>')">
              <img src="../assets/edit.png" alt="Edit" width="22">
            </button>
            <button class="btn btn-light border-0 p-1" onclick="hapusGuru('<?= $guru['id_guru'] ?>')">
              <img src="../assets/Hapus.png" alt="Hapus" width="22">
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>



<!-- MODAL TAMBAH/EDIT -->
<div class="modal fade" id="modalGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="judulModalGuru">Tambah Guru Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formGuru">
        <input type="hidden" name="id_guru" id="id_guru">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama_guru" id="nama_guru" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">NIP</label>
            <input type="text" name="nip" id="nip" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" id="alamat" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Nomor Telepon</label>
            <input type="text" name="no_telp" id="no_telp" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control">
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpanGuru">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById('sidebar');
  document.getElementById('toggleSidebar').addEventListener('click', () => sidebar.classList.toggle('collapsed'));

  const profileBtn = document.getElementById('profileToggle');
  const profileCard = document.getElementById('profileCard');
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profileCard.classList.toggle('show');
  });
  document.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target)) profileCard.classList.remove('show');
  });

  // === FITUR CARI ===
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('keyup', () => {
  const keyword = searchInput.value.toLowerCase();
  document.querySelectorAll('.guru-item').forEach(item => {
    const nama = item.querySelector('.card-title').textContent.toLowerCase();
    const nip = item.querySelector('small').textContent.toLowerCase();
    const email = item.querySelector('.card-body p:first-child').textContent.toLowerCase();
    item.style.display = (nama.includes(keyword) || nip.includes(keyword) || email.includes(keyword)) ? '' : 'none';
  });
});

  const modalGuru = new bootstrap.Modal(document.getElementById('modalGuru'));
  const formGuru = document.getElementById('formGuru');
  const idGuruInput = document.getElementById('id_guru');
  const judulModal = document.getElementById('judulModalGuru');
  const tombolSimpan = document.getElementById('btnSimpanGuru');
  const apiURL = "https://ortuconnect.atwebpages.com/api/admin/data_guru.php";

  // === TOMBOL TAMBAH GURU ===
  document.getElementById('btnTambahGuru').addEventListener('click', () => {
    judulModal.textContent = "Tambah Guru Baru";
    formGuru.reset();
    idGuruInput.value = "";
    modalGuru.show();
  });

  // === SIMPAN DATA GURU (langsung ke API) ===
  formGuru.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = idGuruInput.value.trim();
    const formData = Object.fromEntries(new FormData(formGuru).entries());
    const method = id ? "PUT" : "POST";
    if (id) formData.id_guru = id;

    tombolSimpan.disabled = true;
    tombolSimpan.textContent = "Menyimpan...";

    try {
      const res = await fetch(apiURL, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
      });
      const data = await res.json();
      alert(data.message || "Berhasil disimpan!");
      modalGuru.hide();
      location.reload();
    } catch (err) {
      alert("Terjadi kesalahan: " + err.message);
    } finally {
      tombolSimpan.disabled = false;
      tombolSimpan.textContent = "Simpan";
    }
  });
});
</script>
</body>
</html>
