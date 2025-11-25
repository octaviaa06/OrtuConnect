<?php
session_name('SESS_ADMIN');
session_start();



if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login/index.php?error=Harap login sebagai admin!");
  exit;
}

$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_siswa.php";
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
$ch=null;

$data = json_decode($response, true);
$siswaList = $data['data'] ?? [];
$from_param = 'data';
$_GET['from'] = $from_param;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Siswa | OrtuConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="datasiswa.css">
  <link rel="stylesheet" href="../profil/profil.css">
  <link rel="stylesheet" href="../admin/sidebar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
       <?php include '../profil/profil.php'; ?>
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
            <div class="col-md-4 mb-3 siswa-item">
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

<!-- TAMBAH/EDIT -->
<div class="modal fade" id="modalSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 custom-modal">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold text-primary" id="judulModalSiswa">Tambah Murid Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formSiswa">
        <input type="hidden" name="id_siswa" id="id_siswa">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama_siswa" id="nama_siswa" class="form-control custom-input" required>
            <div class="invalid-feedback">Nama lengkap harus diisi</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
            <select name="kelas" id="kelas" class="form-select custom-input" required>
              <option value="">-- Pilih Kelas --</option>
              <option value="Kelas A">Kelas A</option>
              <option value="Kelas B">Kelas B</option>
            </select>
            <div class="invalid-feedback">Kelas harus dipilih</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control custom-input" required>
            <div class="invalid-feedback">Tanggal lahir harus diisi</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="gender" id="gender" class="form-select custom-input" required>
              <option value="">-- Jenis Kelamin --</option>
              <option value="Laki-Laki">Laki-Laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
            <div class="invalid-feedback">Jenis kelamin harus dipilih</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Orang Tua <span class="text-danger">*</span></label>
            <input type="text" name="nama_ortu" id="nama_ortu" class="form-control custom-input" required>
            <div class="invalid-feedback">Nama orang tua harus diisi</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">No. Telp Orang Tua <span class="text-danger">*</span></label>
            <input type="text" name="no_telp_ortu" id="no_telp_ortu" class="form-control custom-input" required>
            <div class="invalid-feedback">Nomor telepon harus diisi</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control custom-input" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary custom-btn" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary custom-btn" id="btnSimpanSiswa">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- AKUN -->
<div class="modal fade" id="modalAkun" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content custom-modal">
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
        <button class="btn btn-secondary custom-btn" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const modalSiswa = new bootstrap.Modal(document.getElementById('modalSiswa'));
  const formSiswa = document.getElementById('formSiswa');
  const idSiswa = document.getElementById('id_siswa');
  const apiURL = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_siswa.php";

  // Sidebar toggle
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleSidebar');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('expanded');
    });
  }

  // Validasi form real-time
  const inputs = formSiswa.querySelectorAll('.custom-input[required]');
  inputs.forEach(input => {
    input.addEventListener('blur', function() {
      validateField(this);
    });
  });

  function validateField(field) {
    if (!field.value.trim()) {
      field.classList.add('is-invalid');
      return false;
    } else {
      field.classList.remove('is-invalid');
      return true;
    }
  }

  // Tambah siswa
  document.getElementById('btnTambahSiswa').addEventListener('click', () => {
    document.getElementById('judulModalSiswa').textContent = "Tambah Murid Baru";
    formSiswa.reset();
    idSiswa.value = "";
    // Hapus validasi sebelumnya
    inputs.forEach(input => input.classList.remove('is-invalid'));
    modalSiswa.show();
  });

  // Simpan data siswa (POST/PUT)
  formSiswa.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Validasi semua field
    let isValid = true;
    inputs.forEach(input => {
      if (!validateField(input)) {
        isValid = false;
      }
    });

    if (!isValid) {
      Swal.fire({
        icon: 'warning',
        title: 'Data Belum Lengkap',
        text: 'Harap isi semua field yang wajib diisi!',
        confirmButtonColor: '#3085d6'
      });
      return;
    }

    const id = idSiswa.value.trim();
    const dataForm = Object.fromEntries(new FormData(formSiswa).entries());
    const method = id ? "PUT" : "POST";
    const action = id ? "mengedit" : "menambah";

    if (id) dataForm.id_siswa = id;

    try {
      const res = await fetch(apiURL, {
        method,
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(dataForm)
      });
      const data = await res.json();
      
      if (res.ok) {
        Swal.fire({
          icon: 'success',
          title: `Berhasil ${action} data!`,
          text: data.message || `Data murid berhasil ${action}.`,
          confirmButtonColor: '#3085d6'
        }).then(() => {
          modalSiswa.hide();
          location.reload();
        });
      } else {
        throw new Error(data.message || 'Gagal menyimpan data');
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal Menyimpan',
        text: err.message,
        confirmButtonColor: '#3085d6'
      });
    }
  });

  window.editSiswa = async function(id) {
    try {
      const res = await fetch(apiURL + `?id_siswa=${id}`);
      const data = await res.json();
      if (!data || data.message) {
        Swal.fire({
          icon: 'error',
          title: 'Data Tidak Ditemukan',
          text: 'Data murid tidak ditemukan!',
          confirmButtonColor: '#3085d6'
        });
        return;
      }

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
      
      // Hapus validasi sebelumnya
      inputs.forEach(input => input.classList.remove('is-invalid'));
      modalSiswa.show();
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal Memuat Data',
        text: err.message,
        confirmButtonColor: '#3085d6'
      });
    }
  };

  window.hapusSiswa = async function(id) {
    const result = await Swal.fire({
      icon: 'warning',
      title: 'Hapus Data Murid?',
      text: 'Data yang dihapus tidak dapat dikembalikan!',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
      try {
        const res = await fetch(apiURL, {
          method: "DELETE",
          headers: {"Content-Type": "application/json"},
          body: JSON.stringify({id_siswa: id})
        });
        const data = await res.json();
        
        if (res.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil Dihapus!',
            text: data.message || 'Data murid berhasil dihapus.',
            confirmButtonColor: '#3085d6'
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(data.message || 'Gagal menghapus data');
        }
      } catch (err) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal Menghapus',
          text: err.message,
          confirmButtonColor: '#3085d6'
        });
      }
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Dibatalkan',
        text: 'Data murid aman.',
        confirmButtonColor: '#3085d6',
        timer: 1500
      });
    }
  };

  window.generateAkun = async function(id) {
    try {
      const res = await fetch(`https://ortuconnect.pbltifnganjuk.com/api/admin/generate_akun.php?tipe=siswa&id=${id}`, { cache: "no-store" });
      const data = await res.json();
      if (data.status === "success") {
        const d = data.data;
        document.getElementById("akunNama").textContent = d.nama;
        document.getElementById("akunUsername").textContent = d.username;
        document.getElementById("akunPassword").textContent = d.password;
        document.getElementById("akunRole").textContent = d.role;
        new bootstrap.Modal(document.getElementById('modalAkun')).show();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal Membuat Akun',
          text: data.message,
          confirmButtonColor: '#3085d6'
        });
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "Gagal menampilkan akun: " + err.message,
        confirmButtonColor: '#3085d6'
      });
    }
  }
});

// Pencarian
const searchInput = document.getElementById('searchInput');
const siswaContainer = document.getElementById('siswaContainer');

if (searchInput && siswaContainer) {
  searchInput.addEventListener('input', function() {
    const keyword = this.value.toLowerCase().trim();
    const siswaItems = siswaContainer.getElementsByClassName('siswa-item');

    for (let item of siswaItems) {
      const nama = item.querySelector('.card-title').textContent.toLowerCase();
      const kelas = item.querySelector('small').textContent.toLowerCase();

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