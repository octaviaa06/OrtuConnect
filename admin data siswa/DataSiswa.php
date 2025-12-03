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
 <div class="d-flex justify-content-between align-items-center mb-4">
  <div class="d-flex align-items-center gap-3">
    
    <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
        <h4 class="fw-bold text-primary m-0">Data Murid</h4>
    </div>
   
  </div>
       <?php include '../profil/profil.php'; ?>
      </div>

      <!-- HEADER TAMBAH, PENCARIAN & FILTER KELAS -->
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <!-- Pencarian -->
          <div class="search-container position-relative">
            <img src="../assets/cari.png" alt="Cari" class="search-icon">
            <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari murid berdasarkan nama...">
          </div>

          <!-- Filter Kelas - Dipindahkan ke samping pencarian -->
          <div class="filter-kelas-container">
            <select id="filterKelas" class="form-select filter-select" onchange="filterByKelas()">
              <option value="">Semua Kelas</option>
              <option value="Kelas A" <?= $selected_kelas === 'Kelas A' ? 'selected' : '' ?>>Kelas A</option>
              <option value="Kelas B" <?= $selected_kelas === 'Kelas B' ? 'selected' : '' ?>>Kelas B</option>
            </select>
          </div>
        </div>

        <button class="btn btn-primary rounded-3 px-4" id="btnTambahSiswa">
          <span style="font-weight:600;">+ Tambah Murid</span>
        </button>
      </div>

      <!-- CARD LIST SISWA -->
      <div class="row g-3" id="siswaContainer">
        <?php if (empty($siswaList)): ?>
          <div class="col-12">
            <div class="text-center text-muted p-5">
              <img src="../assets/empty-data.png" alt="Data kosong" width="100" class="mb-3 opacity-50">
              <p class="mb-0"><?= $selected_kelas ? "Tidak ada data siswa di $selected_kelas" : "Tidak ada data murid." ?></p>
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
              <div class="card shadow-sm border-0 p-3 d-flex flex-column justify-content-between" style="border-radius:16px;">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar-inisial bg-primary text-white me-3" 
                       style="width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                    <?= $inisial ?>
                  </div>
                  <div>
                    <h5 class="card-title mb-0"><?= $nama ?></h5>
                    <small class="badge bg-secondary"><?= htmlspecialchars($siswa['kelas']); ?></small>
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
            <input type="text" name="no_telp_ortu" id="no_telp_ortu" class="form-control custom-input" required maxlength="15">
            <small class="text-muted d-block">Contoh: 081234567890</small>
            <div class="invalid-feedback">Nomor telepon harus 10-15 digit angka saja</div>
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

  // ====== VALIDASI NOMOR TELEPON ======
  // Validasi Nomor Telepon – hanya angka
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
    document.getElementById('judulModalSiswa').textContent = "Tambah Murid Baru";
    formSiswa.reset();
    idSiswa.value = "";
    inputs.forEach(input => input.classList.remove('is-invalid'));
    modalSiswa.show();
  });

  // Simpan data siswa (POST/PUT)
  formSiswa.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    let isValid = true;
    inputs.forEach(input => {
      if (!validateField(input)) {
        isValid = false;
      }
    });

    // Validasi tambahan untuk nomor telepon
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
      document.getElementById('judulModalSiswa').textContent = "Edit Data Murid";
      
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
      const kelas = item.querySelector('.badge').textContent.toLowerCase();

      if (nama.includes(keyword) || kelas.includes(keyword)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    }
  });
}
// ========== ANIMASI INTERAKTIF TAMBAHAN ==========

// Animasi saat halaman selesai loading
document.addEventListener('DOMContentLoaded', function() {
    console.log('Data Siswa page loaded with animations');
    
    // Delay untuk animasi entrance
    setTimeout(() => {
        document.body.classList.add('page-loaded');
    }, 100);
    
    // Tambah efek ripple ke tombol utama
    const primaryButtons = document.querySelectorAll('.btn-primary');
    primaryButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.disabled || this.classList.contains('disabled')) return;
            
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.7);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: 100px;
                height: 100px;
                top: ${y - 50}px;
                left: ${x - 50}px;
                pointer-events: none;
                z-index: 1;
            `;
            
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Animasi real-time search dengan highlight
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                highlightSearchResults(this.value.toLowerCase());
            }, 300);
        });
    }
    
    // Hover effect untuk card siswa
    const siswaCards = document.querySelectorAll('.card');
    siswaCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = '0 12px 35px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Animasi tombol edit/hapus
    const actionButtons = document.querySelectorAll('.btn-light.border-0');
    actionButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const img = this.querySelector('img');
            if (img) {
                img.style.transform = 'scale(1.15) rotate(5deg)';
                img.style.transition = 'transform 0.2s ease';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            const img = this.querySelector('img');
            if (img) {
                img.style.transform = 'scale(1) rotate(0)';
            }
        });
    });
    
    // Scroll reveal animation
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe semua card siswa untuk lazy loading
    document.querySelectorAll('.siswa-item').forEach(item => {
        observer.observe(item);
    });
    
    // Tambah scroll indicator
    createScrollIndicator();
    
    // Animasi filter select
    const filterSelect = document.getElementById('filterKelas');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }
    
    // Validasi form dengan animasi
    setupFormValidationAnimations();
});

// Fungsi highlight hasil pencarian
function highlightSearchResults(searchTerm) {
    if (!searchTerm) {
        // Hapus highlight jika tidak ada pencarian
        document.querySelectorAll('.search-highlight').forEach(el => {
            const parent = el.parentNode;
            parent.replaceChild(document.createTextNode(el.textContent), el);
            parent.normalize();
        });
        return;
    }
    
    const siswaItems = document.querySelectorAll('.siswa-item');
    siswaItems.forEach(item => {
        const textElements = item.querySelectorAll('.card-title, .card-body p, .badge');
        textElements.forEach(element => {
            const originalHTML = element.innerHTML;
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            
            if (regex.test(element.textContent)) {
                const highlighted = element.textContent.replace(
                    regex, 
                    '<span class="search-highlight">$1</span>'
                );
                element.innerHTML = highlighted;
                
                // Animasi untuk highlighted elements
                const highlights = element.querySelectorAll('.search-highlight');
                highlights.forEach(highlight => {
                    highlight.style.animation = 'highlightFlash 0.5s ease';
                });
            }
        });
    });
}

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'success') {
    // Hapus toast sebelumnya jika ada
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove setelah 3 detik
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'fadeOutUp 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }
    }, 3000);
}

// Fungsi untuk menampilkan loading overlay
function showLoading(message = 'Memproses...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="text-center">
            <div class="loading-spinner mb-3"></div>
            <p class="text-primary fw-semibold">${message}</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
    return overlay;
}

function hideLoading(overlay) {
    if (overlay && overlay.parentNode) {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';
        setTimeout(() => overlay.remove(), 300);
    }
}

// Fungsi untuk membuat scroll indicator
function createScrollIndicator() {
    const indicator = document.createElement('div');
    indicator.className = 'scroll-indicator';
    indicator.innerHTML = '<i class="bi bi-chevron-up"></i>';
    indicator.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        z-index: 1000;
    `;
    
    document.body.appendChild(indicator);
    
    // Tampilkan indicator saat scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            indicator.classList.add('visible');
        } else {
            indicator.classList.remove('visible');
        }
    });
    
    // Scroll to top saat diklik
    indicator.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const iconWrapper = document.querySelector('.header-icon-wrapper');
    if (iconWrapper) {
        for (let i = 0; i < 3; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            iconWrapper.appendChild(particle);
        }
    }
});


// Setup form validation animations
function setupFormValidationAnimations() {
    const form = document.getElementById('formSiswa');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        // Validasi real-time
        input.addEventListener('blur', function() {
            validateWithAnimation(this);
        });
        
        // Animasi saat fokus
        input.addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Animasi untuk select
        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                    
                    // Animasi perubahan
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                }
            });
        }
    });
}

function validateWithAnimation(field) {
    if (!field.value.trim() && field.hasAttribute('required')) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        // Shake animation
        field.style.animation = 'shake 0.5s ease';
        setTimeout(() => {
            field.style.animation = '';
        }, 500);
        
        return false;
    } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        return true;
    }
}

// Enhanced filter function dengan animasi
function filterByKelas() {
    const selectedKelas = document.getElementById('filterKelas').value;
    const siswaItems = document.querySelectorAll('.siswa-item');
    
    // Animasi filter
    const filterSelect = document.getElementById('filterKelas');
    filterSelect.style.transform = 'scale(0.95)';
    setTimeout(() => {
        filterSelect.style.transform = 'scale(1)';
    }, 150);
    
    // Jika filter kosong, reload page
    if (!selectedKelas) {
        window.location.href = '?';
        return;
    }
    
    // Tampilkan loading
    const loading = showLoading('Memfilter data...');
    
    // Filter dengan animasi
    let visibleCount = 0;
    siswaItems.forEach((item, index) => {
        const kelas = item.getAttribute('data-kelas');
        
        if (kelas === selectedKelas) {
            item.style.display = '';
            item.style.animation = `cardSlideIn 0.5s ease ${index * 0.1}s forwards`;
            visibleCount++;
        } else {
            item.style.animation = 'cardSlideOut 0.5s ease forwards';
            setTimeout(() => {
                item.style.display = 'none';
            }, 500);
        }
    });
    
    // Hide loading
    setTimeout(() => {
        hideLoading(loading);
        
        // Tampilkan toast jika tidak ada hasil
        if (visibleCount === 0) {
            showToast(`Tidak ditemukan siswa di kelas ${selectedKelas}`, 'warning');
        } else {
            showToast(`Menampilkan ${visibleCount} siswa di kelas ${selectedKelas}`, 'success');
        }
        
        // Update URL tanpa reload page
        const url = new URL(window.location);
        url.searchParams.set('kelas_filter', selectedKelas);
        window.history.pushState({}, '', url);
    }, 500);
}

// Animasi untuk SweetAlert2
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

// Override SweetAlert dengan animasi custom
function showSuccessAlert(title, text) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
}

function showErrorAlert(title, text) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        showClass: {
            popup: 'animate__animated animate__headShake'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOut'
        }
    });
}

// Animate card saat ditambah/dihapus
function animateCardRemoval(cardElement) {
    cardElement.style.animation = 'cardSlideOut 0.5s ease forwards';
    setTimeout(() => {
        if (cardElement.parentNode) {
            cardElement.remove();
            showToast('Data siswa berhasil dihapus', 'success');
        }
    }, 500);
}

function animateCardAddition(cardData) {
    // Implementasi untuk menambah card baru dengan animasi
    const container = document.getElementById('siswaContainer');
    const newCard = createCardElement(cardData);
    
    newCard.style.opacity = '0';
    newCard.style.transform = 'translateX(-100px) scale(0.8)';
    container.prepend(newCard);
    
    // Trigger reflow
    newCard.offsetHeight;
    
    newCard.style.transition = 'all 0.5s ease';
    newCard.style.opacity = '1';
    newCard.style.transform = 'translateX(0) scale(1)';
    
    showToast('Data siswa berhasil ditambahkan', 'success');
}

// Helper function untuk membuat card element
function createCardElement(siswa) {
    const nama = siswa.nama_siswa;
    const kata = nama.split(' ');
    const inisial = kata.length >= 2 
        ? (kata[0][0] + kata[1][0]).toUpperCase()
        : nama.substring(0, 2).toUpperCase();
    
    const card = document.createElement('div');
    card.className = 'col-md-4 mb-3 siswa-item';
    card.setAttribute('data-kelas', siswa.kelas);
    card.innerHTML = `
        <div class="card shadow-sm border-0 p-3 d-flex flex-column justify-content-between" style="border-radius:16px;">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-inisial bg-primary text-white me-3" 
                     style="width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                    ${inisial}
                </div>
                <div>
                    <h5 class="card-title mb-0">${nama}</h5>
                    <small class="badge bg-secondary">${siswa.kelas}</small>
                </div>
            </div>
            <div class="card-body pt-0 pb-2 px-0">
                <p class="mb-1"><strong>Jenis Kelamin:</strong> ${siswa.gender}</p>
                <p class="mb-1"><strong>Orang Tua:</strong> ${siswa.nama_ortu}</p>
                <p class="mb-1"><strong>No. Telp:</strong> ${siswa.no_telp_ortu}</p>
                <p class="mb-0"><strong>Alamat:</strong> ${siswa.alamat ? siswa.alamat.replace(/\n/g, '<br>') : ''}</p>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <button class="btn btn-primary rounded-3 px-4" onclick="generateAkun('${siswa.id_siswa}')">
                    <span style="font-weight:600;">Buat Akun</span>
                </button>
                <div>
                    <button class="btn btn-light border-0 p-1" onclick="editSiswa('${siswa.id_siswa}')">
                        <img src="../assets/edit.png" alt="Edit" width="22">
                    </button>
                    <button class="btn btn-light border-0 p-1" onclick="hapusSiswa('${siswa.id_siswa}')">
                        <img src="../assets/Hapus.png" alt="Hapus" width="22">
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

// Event listener untuk data refresh
document.addEventListener('dataRefresh', function() {
    const container = document.getElementById('siswaContainer');
    container.style.opacity = '0.5';
    container.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        container.style.opacity = '1';
    }, 300);
});

// Tambahkan FontAwesome icons untuk toast
const fontAwesomeLink = document.createElement('link');
fontAwesomeLink.rel = 'stylesheet';
fontAwesomeLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css';
document.head.appendChild(fontAwesomeLink);
</script>
</body>
</html>