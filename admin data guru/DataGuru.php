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
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$guru_list = $data['data'] ?? []; // pastikan struktur JSON-nya
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
    <li class="nav-item">
      <a href="../dashboard_admin/home_admin.php" class="nav-link">
        <img src="../assets/Dashboard.png" class="icon">
        <span>Dashboard</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="../admin data guru/DataGuru.php" class="nav-link active">
        <img src="../assets/Data Guru.png" class="icon">
        <span>Data Guru</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="../admin data siswa/DataSiswa.php" class="nav-link">
        <img src="../assets/Data Siswa.png" class="icon">
        <span>Data Murid</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="../admin absensi/Absensi.php" class="nav-link">
        <img src="../assets/absensi.png" class="icon">
        <span>Absensi</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="../admin perizinan/Perizinan.php" class="nav-link">
        <img src="../assets/Perizinan.png" class="icon">
        <span>Perizinan</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="../admin kalender/Kalender.php" class="nav-link">
        <img src="../assets/Kalender.png" class="icon">
        <span>Kalender</span>
      </a>
    </li>
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
          <a href="../logout/logout.php" class="logout-btn">
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
      <div class="row g-3">
        <?php if(empty($guru_list)): ?>
          <p class="text-muted">Tidak ada data guru.</p>
        <?php else: ?>
          <?php foreach ($guru_list as $guru): ?>
            <div class="col-md-4">
              <div class="card shadow-sm border-0 guru-card">
                <div class="card-body text-center">
                  <img src="../assets/user.png" width="70" class="mb-3 rounded-circle" alt="Guru">
                  <h5 class="fw-bold mb-1"><?= htmlspecialchars($guru['nama_guru']) ?></h5>
                  <p class="text-muted small mb-2"><?= htmlspecialchars($guru['nip']) ?></p>
                  <p class="mb-0"><?= htmlspecialchars($guru['email']) ?></p>
                  <p class="small text-muted mb-3"><?= htmlspecialchars($guru['telepon']) ?></p>
                  <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-primary btn-edit"
                      data-id="<?= $guru['id_guru'] ?>"
                      data-nama="<?= htmlspecialchars($guru['nama_guru']) ?>"
                      data-nip="<?= htmlspecialchars($guru['nip']) ?>"
                      data-alamat="<?= htmlspecialchars($guru['alamat']) ?>"
                      data-telepon="<?= htmlspecialchars($guru['telepon']) ?>"
                      data-email="<?= htmlspecialchars($guru['email']) ?>">Edit</button>
                    <button class="btn btn-sm btn-outline-danger btn-hapus" data-id="<?= $guru['id_guru'] ?>">Hapus</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
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
            <input type="text" name="telepon" id="telepon" class="form-control">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // === TOGGLE SIDEBAR ===
  const sidebar = document.getElementById('sidebar');
  document.getElementById('toggleSidebar').addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
  });

  // === PROFILE DROPDOWN ===
  const profileBtn = document.getElementById('profileToggle');
  const profileCard = document.getElementById('profileCard');
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profileCard.classList.toggle('show');
  });
  document.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target)) profileCard.classList.remove('show');
  });

   // === FITUR CARI GURU ===
  const searchInput = document.getElementById('searchInput');
  searchInput.addEventListener('keyup', () => {
    const keyword = searchInput.value.toLowerCase();
    document.querySelectorAll('.guru-card').forEach(card => {
      const text = card.innerText.toLowerCase();
      card.parentElement.style.display = text.includes(keyword) ? '' : 'none';
    });
  });



  // === MODAL TAMBAH/EDIT GURU ===
  const modalGuru = new bootstrap.Modal(document.getElementById('modalGuru'));
  const formGuru = document.getElementById('formGuru');
  const idGuruInput = document.getElementById('id_guru');
  const judulModal = document.getElementById('judulModalGuru');
  const tombolSimpan = document.getElementById('btnSimpanGuru');

  // TAMBAH DATA
  document.getElementById('btnTambahGuru').addEventListener('click', () => {
    judulModal.textContent = "Tambah Guru Baru";
    formGuru.reset();
    idGuruInput.value = "";
    modalGuru.show();
  });

  // EDIT DATA
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      judulModal.textContent = "Edit Data Guru";
      idGuruInput.value = btn.dataset.id;
      document.getElementById('nama_guru').value = btn.dataset.nama;
      document.getElementById('nip').value = btn.dataset.nip;
      document.getElementById('alamat').value = btn.dataset.alamat;
      document.getElementById('telepon').value = btn.dataset.telepon;
      document.getElementById('email').value = btn.dataset.email;
      modalGuru.show();
    });
  });

  // SIMPAN TAMBAH/EDIT
  formGuru.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(formGuru);
    const id = idGuruInput.value.trim();
    const url = id ? 'edit_guru.php' : 'tambah_guru.php';

    tombolSimpan.disabled = true;
    tombolSimpan.textContent = "Menyimpan...";

    try {
      const res = await fetch(url, { method: 'POST', body: formData });
      const text = await res.text();
      alert(text);
      modalGuru.hide();
      location.reload();
    } catch (err) {
      alert("Terjadi kesalahan: " + err.message);
    } finally {
      tombolSimpan.disabled = false;
      tombolSimpan.textContent = "Simpan";
    }
  });

  // HAPUS DATA
  document.querySelectorAll('.btn-hapus').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm("Yakin ingin menghapus data guru ini?")) return;
      const id = btn.dataset.id;
      const formData = new FormData();
      formData.append('id_guru', id);

      try {
        const res = await fetch('hapus_guru.php', { method: 'POST', body: formData });
        const text = await res.text();
        alert(text);
        location.reload();
      } catch (err) {
        alert("Gagal menghapus data: " + err.message);
      }
    });
  });
});
</script>
</body>
</html>
