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

// Filter berdasarkan kelas jika dipilih
$selected_kelas = $_GET['kelas_filter'] ?? '';
if ($selected_kelas) {
    $siswaList = array_filter($siswaList, function($siswa) use ($selected_kelas) {
        return $siswa['kelas'] === $selected_kelas;
    });
}

$from_param = 'data';
$_GET['from'] = $from_param;

// Get today's date for max attribute
$today = date('Y-m-d');
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
          <div class="header-icon-wrapper">
            <img src="../assets/data_siswa_biru.png" alt="Students Icon" class="header-icon">
          </div>
          <h4 class="fw-bold text-primary m-0">Data Murid</h4>
        </div>
        <?php include '../profil/profil.php'; ?>
      </div>

      <!-- HEADER TAMBAH, PENCARIAN & FILTER KELAS -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <!-- Pencarian -->
          <div class="search-container position-relative">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari murid berdasarkan nama...">
          </div>

          <!-- Filter Kelas -->
          <div class="filter-kelas-container">
            <select id="filterKelas" class="form-select filter-select" onchange="filterByKelas()">
              <option value="">Semua Kelas</option>
              <option value="Kelas A" <?= $selected_kelas === 'Kelas A' ? 'selected' : '' ?>>Kelas A</option>
              <option value="Kelas B" <?= $selected_kelas === 'Kelas B' ? 'selected' : '' ?>>Kelas B</option>
            </select>
          </div>
        </div>

        <button class="btn btn-primary btn-add-student rounded-3 px-4" id="btnTambahSiswa">
          <i class="bi bi-plus-circle me-2"></i>
          <span style="font-weight:600;">Tambah Murid</span>
        </button>
      </div>

      <!-- CARD LIST SISWA -->
      <div class="row g-3" id="siswaContainer">
        <?php if (empty($siswaList)): ?>
          <div class="col-12">
            <div class="empty-state text-center text-muted p-5">
              <div class="empty-icon-wrapper mb-3">
                <i class="bi bi-inbox display-1 opacity-50"></i>
              </div>
              <p class="mb-0 fs-5"><?= $selected_kelas ? "Tidak ada data siswa di $selected_kelas" : "Tidak ada data murid." ?></p>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($siswaList as $siswa): 
            $nama = htmlspecialchars($siswa['nama_siswa']);
            $kata = explode(' ', $nama);
            $inisial = (count($kata) >= 2)
              ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1))
              : strtoupper(substr($kata[0], 0, 2));
          ?>
            <div class="col-md-4 mb-3 siswa-item" data-kelas="<?= htmlspecialchars($siswa['kelas']) ?>">
              <div class="card card-student shadow-sm border-0 p-3 d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar-inisial bg-gradient-primary text-white me-3">
                    <?= $inisial ?>
                  </div>
                  <div>
                    <h5 class="card-title mb-1"><?= $nama ?></h5>
                    <span class="badge badge-class"><?= htmlspecialchars($siswa['kelas']); ?></span>
                  </div>
                </div>
                <div class="card-body pt-0 pb-2 px-0">
                  <p class="mb-2 info-item"><i class="bi bi-gender-ambiguous me-2 text-primary"></i><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($siswa['gender']); ?></p>
                  <p class="mb-2 info-item"><i class="bi bi-person-heart me-2 text-primary"></i><strong>Orang Tua:</strong> <?= htmlspecialchars($siswa['nama_ortu']); ?></p>
                  <p class="mb-2 info-item"><i class="bi bi-telephone me-2 text-primary"></i><strong>No. Telp:</strong> <?= htmlspecialchars($siswa['no_telp_ortu']); ?></p>
                  <p class="mb-0 info-item"><i class="bi bi-geo-alt me-2 text-primary"></i><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($siswa['alamat'])); ?></p>
                </div>

                <div class="d-flex justify-content-between mt-3 card-actions">
                  <button class="btn btn-primary btn-generate rounded-pill px-4" onclick="generateAkun('<?= $siswa['id_siswa'] ?>')">
                    <i class="bi bi-person-plus-fill me-2"></i>
                    <span style="font-weight:600;">
                      Lihat Akun</span>
                  </button>
                  <div class="action-buttons">
                    <button class="btn btn-action btn-edit" onclick="editSiswa('<?= $siswa['id_siswa'] ?>')" title="Edit">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-action btn-delete" onclick="hapusSiswa('<?= $siswa['id_siswa'] ?>')" title="Hapus">
                      <i class="bi bi-trash3"></i>
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

<!-- TAMBAH/EDIT MODAL -->
<div class="modal fade" id="modalSiswa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content custom-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModalSiswa">
          <i class="bi bi-person-plus-fill me-2"></i>Tambah Murid Baru
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formSiswa">
        <input type="hidden" name="id_siswa" id="id_siswa">
        <div class="modal-body">
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-person me-2"></i>Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama_siswa" id="nama_siswa" class="form-control custom-input" required>
            <div class="invalid-feedback">Nama lengkap harus diisi</div>
          </div>
          
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-door-open me-2"></i>Kelas <span class="text-danger">*</span></label>
            <select name="kelas" id="kelas" class="form-select custom-input" required>
              <option value="">-- Pilih Kelas --</option>
              <option value="Kelas A">Kelas A</option>
              <option value="Kelas B">Kelas B</option>
            </select>
            <div class="invalid-feedback">Kelas harus dipilih</div>
          </div>
          
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control custom-input" max="<?= $today ?>" required>
            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Tanggal tidak boleh melebihi hari ini</small>
            <div class="invalid-feedback">Tanggal lahir harus diisi</div>
          </div>
          
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-gender-ambiguous me-2"></i>Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="gender" id="gender" class="form-select custom-input" required>
              <option value="">-- Jenis Kelamin --</option>
              <option value="Laki-Laki">Laki-Laki</option>
              <option value="Perempuan">Perempuan</option>
            </select>
            <div class="invalid-feedback">Jenis kelamin harus dipilih</div>
          </div>
          
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-person-heart me-2"></i>Nama Orang Tua <span class="text-danger">*</span></label>
            <input type="text" name="nama_ortu" id="nama_ortu" class="form-control custom-input" required>
            <div class="invalid-feedback">Nama orang tua harus diisi</div>
          </div>
          
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-telephone me-2"></i>No. Telp Orang Tua <span class="text-danger">*</span></label>
            <input type="text" name="no_telp_ortu" id="no_telp_ortu" class="form-control custom-input" required maxlength="15">
            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Contoh: 081234567890</small>
            <div class="invalid-feedback">Nomor telepon harus 10-15 digit angka saja</div>
          </div>
          
          <div class="mb-3 form-group">
            <label class="form-label"><i class="bi bi-geo-alt me-2"></i>Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control custom-input" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary custom-btn" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Batal
          </button>
          <button type="submit" class="btn btn-primary custom-btn" id="btnSimpanSiswa">
            <i class="bi bi-check-circle me-2"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL AKUN -->
<div class="modal fade" id="modalAkun" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content custom-modal">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title"><i class="bi bi-key-fill me-2"></i>Akun OrangTua</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="account-info">
          <div class="info-row">
            <div class="info-label"><i class="bi bi-person me-2"></i>Nama</div>
            <div class="info-value" id="akunNama"></div>
          </div>
          <div class="info-row">
            <div class="info-label"><i class="bi bi-person-badge me-2"></i>Username</div>
            <div class="info-value" id="akunUsername"></div>
          </div>
          <div class="info-row">
            <div class="info-label"><i class="bi bi-shield-lock me-2"></i>Password</div>
            <div class="info-value" id="akunPassword"></div>
          </div>
          <div class="info-row">
            <div class="info-label"><i class="bi bi-award me-2"></i>Role</div>
            <div class="info-value" id="akunRole"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary custom-btn" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-2"></i>Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Set max date untuk tanggal lahir
document.addEventListener('DOMContentLoaded', function() {
  const tanggalLahirInput = document.getElementById('tanggal_lahir');
  const today = new Date().toISOString().split('T')[0];
  tanggalLahirInput.setAttribute('max', today);
  
  // Validasi tambahan saat input berubah
  tanggalLahirInput.addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const todayDate = new Date(today);
    
    if (selectedDate > todayDate) {
      Swal.fire({
        icon: 'warning',
        title: 'Tanggal Tidak Valid',
        text: 'Tanggal lahir tidak boleh melebihi hari ini!',
        confirmButtonColor: '#3085d6'
      });
      this.value = '';
      this.classList.add('is-invalid');
    }
  });
});

// Filter berdasarkan kelas
function filterByKelas() {
    const selectedKelas = document.getElementById('filterKelas').value;
    if (selectedKelas) {
        window.location.href = `?kelas_filter=${encodeURIComponent(selectedKelas)}`;
    } else {
        window.location.href = '?';
    }
}

document.addEventListener("DOMContentLoaded", () => {
  const modalSiswa = new bootstrap.Modal(document.getElementById('modalSiswa'));
  const formSiswa = document.getElementById('formSiswa');
  const idSiswa = document.getElementById('id_siswa');
  const apiURL = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_siswa.php";

  // Validasi Nomor Telepon
  document.getElementById("no_telp_ortu").addEventListener("input", function () {
      this.value = this.value.replace(/\D/g, "");
  });

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
    document.getElementById('judulModalSiswa').innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Tambah Murid Baru';
    formSiswa.reset();
    idSiswa.value = "";
    inputs.forEach(input => input.classList.remove('is-invalid'));
    modalSiswa.show();
  });

  // Simpan data siswa
  formSiswa.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    let isValid = true;
    inputs.forEach(input => {
      if (!validateField(input)) {
        isValid = false;
      }
    });

    // Validasi nomor telepon
    const noTelp = document.getElementById('no_telp_ortu').value.trim();
    if (!/^\d{10,15}$/.test(noTelp)) {
      Swal.fire({
        icon: 'warning',
        title: 'Format Nomor Telepon Salah',
        text: 'Nomor telepon harus berisi 10-15 digit angka saja!',
        confirmButtonColor: '#3085d6'
      });
      return;
    }

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
      document.getElementById('judulModalSiswa').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Data Murid';
      
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
          title: 'Gagal Melihat Akun',
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
      const kelas = item.querySelector('.badge').textContent.toLowerCase();

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