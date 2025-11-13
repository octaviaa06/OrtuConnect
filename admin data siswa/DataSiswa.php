<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'datasiswa';
//include '../admin/sidebar.php';
// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login/index.php?error=Harap login sebagai admin!");
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
  <link rel="stylesheet" href="datasiswa.css">
</head>
<body>
<div class="d-flex">
  <!-- SIDEBAR -->
  <?php include '../admin/sidebar.php'; ?>

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

      <!-- HEADER TAMBAH & PENCARIAN -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="search-container flex-grow-1 position-relative" style="max-width: 500px;">
          <img src="../assets/cari.png" alt="Cari" class="search-icon">
          <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari murid berdasarkan nama atau kelas...">
        </div>

        <button class="btn btn-primary rounded-3 px-4" id="btnTambahSiswa">
          <span style="font-weight:600;">+ Tambah Murid</span>
        </button>
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
            <div class="col-md-4 mb-3 siswa-item">//
              <div class="card shadow-sm border-0 p-3 d-flex flex-column justify-content-between" style="border-radius:16px;">
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

                <div class="d-flex justify-content-between mt-3">
                  <button class="btn btn-primary rounded-3 px-4" onclick="generateAkun('<?= $siswa['id_siswa'] ?>')">
                    <span style="font-weight:600;">Buat Akun</span>
                  </button>
                  <div>
                    <button class="btn btn-light border-0 p-1" onclick="editSiswa('<?= $siswa['id_siswa'] ?>')">
                      <img src="../assets/edit.png" alt="Edit" width="22">
                    </button>
                    <button class="btn btn-light border-0 p-1" onclick="hapusSiswa('<?= $siswa['id_siswa'] ?>')">
                      <img src="../assets/Hapus.png" alt="Hapus" width="22">
                    </button>
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
<div class="modal fade" id="modalSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="judulModalSiswa">Tambah Murid Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formSiswa">
        <input type="hidden" name="id_siswa" id="id_siswa">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama_siswa" id="nama_siswa" class="form-control" required></div>
       <div class="mb-3">
  <label class="form-label">Kelas</label>
  <select name="kelas" id="kelas" class="form-select" required>
    <option value="">-- Pilih Kelas --</option>
    <option value="Kelas A">Kelas A</option>
    <option value="Kelas B">Kelas B</option>
  </select>
          </div>

          <div class="mb-3"><label class="form-label">Tanggal Lahir</label><input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Jenis Kelamin</label><input type="text" name="gender" id="gender" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nama Orang Tua</label><input type="text" name="nama_ortu" id="nama_ortu" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">No. Telp Orang Tua</label><input type="text" name="no_telp_ortu" id="no_telp_ortu" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="alamat" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSimpanSiswa">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- MODAL AKUN -->
<div class="modal fade" id="modalAkun" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Akun OrangTua</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-borderless">
          <tr><th>Nama</th><td id="akunNama"></td></tr>
          <tr><th>Username</th><td id="akunUsername"></td></tr>
          <tr><th>Password</th><td id="akunPassword"></td></tr>
          <tr><th>Role</th><td id="akunRole"></td></tr>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const modalSiswa = new bootstrap.Modal(document.getElementById('modalSiswa'));
  const formSiswa = document.getElementById('formSiswa');
  const idSiswa = document.getElementById('id_siswa');
  const apiURL = "http://ortuconnect.atwebpages.com/api/admin/data_siswa.php";

  // Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleSidebar');
if (toggleBtn) {
  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('expanded');
  });
}

// Profile dropdown toggle
const profileToggle = document.getElementById('profileToggle');
const profileCard = document.getElementById('profileCard');
if (profileToggle) {
  profileToggle.addEventListener('click', () => {
    profileCard.classList.toggle('show');
  });
}

  // Tambah siswa
  document.getElementById('btnTambahSiswa').addEventListener('click', () => {
    document.getElementById('judulModalSiswa').textContent = "Tambah Murid Baru";
    formSiswa.reset();
    idSiswa.value = "";
    modalSiswa.show();
  });

  // Simpan data siswa (POST/PUT)
  formSiswa.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = idSiswa.value.trim();
    const dataForm = Object.fromEntries(new FormData(formSiswa).entries());
    const method = id ? "PUT" : "POST";
    if (id) dataForm.id_siswa = id;

    try {
      const res = await fetch(apiURL, {
        method,
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(dataForm)
      });
      const data = await res.json();
      alert(data.message || "Berhasil disimpan!");
      modalSiswa.hide();
      location.reload();
    } catch (err) {
      alert("Gagal menyimpan: " + err.message);
    }
  });

 // Pastikan fungsi ini bisa diakses global
window.editSiswa = async function(id) {
  try {
    const res = await fetch(apiURL + `?id_siswa=${id}`);
    const data = await res.json();
    if (!data || data.message) return alert("Data tidak ditemukan!");

    const s = data;
    idSiswa.value = s.id_siswa;
    document.getElementById('nama_siswa').value = s.nama_siswa;
    document.getElementById('kelas').value = s.kelas;
    document.getElementById('tanggal_lahir').value = s.tanggal_lahir;
    document.getElementById('gender').value = s.gender;
    document.getElementById('nama_ortu').value = s.nama_ortu;
    document.getElementById('no_telp_ortu').value = s.no_telp_ortu;
    document.getElementById('alamat').value = s.alamat;
    document.getElementById('judulModalSiswa').textContent = "Edit Data Murid";
    modalSiswa.show();
  } catch (err) {
    alert("Gagal memuat data: " + err.message);
  }
};

window.hapusSiswa = async function(id) {
  if (!confirm("Yakin ingin menghapus data murid ini?")) return;
  try {
    const res = await fetch(apiURL, {
      method: "DELETE",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify({id_siswa: id})
    });
    const data = await res.json();
    alert(data.message || "Data berhasil dihapus!");
    location.reload();
  } catch (err) {
    alert("Gagal menghapus: " + err.message);
  }
};

window.generateAkun = async function(id) {
  try {
    const res = await fetch(`http://ortuconnect.atwebpages.com/api/admin/generate_akun.php?tipe=siswa&id=${id}`, { cache: "no-store" });
    const data = await res.json();
    if (data.status === "success") {
      const d = data.data;
      document.getElementById("akunNama").textContent = d.nama;
      document.getElementById("akunUsername").textContent = d.username;
      document.getElementById("akunPassword").textContent = d.password;
      document.getElementById("akunRole").textContent = d.role;
      new bootstrap.Modal(document.getElementById('modalAkun')).show();
    } else {
      alert(data.message);
    }
  } catch (err) {
    alert("Gagal menampilkan akun: " + err.message);
  }
}
});

// 🔍 Fitur Pencarian Data Siswa
const searchInput = document.getElementById('searchInput');
const siswaContainer = document.getElementById('siswaContainer');

if (searchInput && siswaContainer) {
  searchInput.addEventListener('input', function() {
    const keyword = this.value.toLowerCase().trim();
    const siswaItems = siswaContainer.getElementsByClassName('siswa-item');

    for (let item of siswaItems) {
      const nama = item.querySelector('.card-title').textContent.toLowerCase();
      const kelas = item.querySelector('small').textContent.toLowerCase();

      // tampilkan jika nama atau kelas cocok dengan pencarian
      if (nama.includes(keyword) || kelas.includes(keyword)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    }
  });
}


</script>
</body>
</html>
