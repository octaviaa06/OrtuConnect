<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'DataGuru';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login/index.php?error=Harap login sebagai admin!");
  exit;
}

// Fungsi Helper untuk Fetch API
function fetchApiData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return ["data" => []];
    }
    
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return ["data" => []];
}

$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_guru.php";
$data = fetchApiData($api_url);
$guruList = $data['data'] ?? [];
$from_param = 'DataGuru';
$_GET['from'] = $from_param;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Guru | OrtuConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="notification.css">
  <link rel="stylesheet" href="../profil/profil.css">
  <link rel="stylesheet" href="../admin/sidebar.css">
 
</head>
<body>
<div class="d-flex">
  <?php include '../admin/sidebar.php'; ?>

  <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
    <div class="container-fluid py-3">
      <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
  <div class="d-flex align-items-center gap-3">
    <!-- Icon Data Guru -->
    <div class="header-icon-wrapper data-guru animated">
      <img src="../assets/Data_Guru.png" alt="Data Guru" class="header-icon">
    </div>
    <!-- Judul -->
    <h4 class="fw-bold text-primary m-0">Data Guru</h4>
  </div>
        <?php include '../profil/profil.php'; ?>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="search-container flex-grow-1 position-relative" style="max-width: 500px;">
          <img src="../assets/cari.png" alt="Cari" class="search-icon">
          <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari guru berdasarkan nama, NIP, atau email...">
        </div>
        <button class="btn btn-primary rounded-3 px-4" id="btnTambahGuru">
          <span style="font-weight:600;">+ Tambah Guru</span>
        </button>
      </div>

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
            <div class="col-md-4 mb-3 guru-item" data-id="<?= $guru['id_guru'] ?>">
              <div class="card guru-card shadow-sm border-0 p-3 d-flex flex-column justify-content-between" style="border-radius:16px; transition: all 0.2s;">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar-inisial bg-primary text-white me-3" style="width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                    <?= $inisial ?>
                  </div>
                  <div>
                    <h5 class="card-title mb-0"><?= $nama ?></h5>
                    <small><strong>NIP </strong><?= htmlspecialchars($guru['nip']); ?></small>
                  </div>
                </div>

                <div class="card-body pt-0 pb-2 px-0">
                  <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($guru['email']); ?></p>
                  <p class="mb-1"><strong>No. Telp:</strong> <?= htmlspecialchars($guru['no_telp']); ?></p>
                  <p class="mb-0"><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($guru['alamat'])); ?></p>
                </div>

                <div class="d-flex justify-content-between mt-3">
                  <button class="btn btn-primary rounded-3 px-4" onclick="generateAkun('<?= $guru['id_guru'] ?>')">
                    <span style="font-weight:600;">Buat Akun</span>
                  </button>
                  <div>
                    <button class="btn btn-light border-0 p-1" onclick="editGuru('<?= $guru['id_guru'] ?>')">
                      <img src="../assets/edit.png" alt="Edit" width="22">
                    </button>
                    <button class="btn btn-light border-0 p-1" onclick="hapusGuru('<?= $guru['id_guru'] ?>')">
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

<!-- Modal Tambah/Edit Guru -->
<div class="modal fade" id="modalGuru" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-3">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="judulModalGuru">Tambah Guru Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formGuru" novalidate>
        <input type="hidden" name="id_guru" id="id_guru">
        <div class="modal-body">
          <!-- ===== FORM TAMBAH GURU ===== -->
          <div class="form-group">
            <label for="nama_lengkap" class="form-label">Nama Lengkap <span style="color:red">*</span></label>
            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
            <div class="invalid-feedback">Nama lengkap wajib diisi</div>
          </div>

          <div class="form-group">
            <label for="nip" class="form-label">NIP <span style="color:red">*</span></label>
            <input type="text" class="form-control" id="nip" name="nip" required maxlength="18">
            <small id="nipWarning" style="color:red; display:none; font-size:13px;">NIP tidak boleh lebih dari 18 angka!</small>
            <div class="invalid-feedback">NIP harus berisi angka saja (minimal 8 digit)</div>
          </div>

          <div class="form-group">
            <label for="alamat" class="form-label">Alamat <span style="color:red">*</span></label>
            <input type="text" class="form-control" id="alamat" name="alamat" required>
            <div class="invalid-feedback">Alamat wajib diisi</div>
          </div>

          <div class="form-group">
            <label for="telepon" class="form-label">Nomor Telepon <span style="color:red">*</span></label>
            <input type="text" class="form-control" id="telepon" name="telepon" required>
            <div class="invalid-feedback">Nomor telepon harus 10-15 digit angka saja</div>
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Email <span style="color:red">*</span></label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">Email tidak valid</div>
          </div>

          <div class="form-group">
            <label for="kelas" class="form-label">Kelas <span style="color:red">*</span></label>
            <select class="form-control" id="kelas" name="kelas" required>
              <option value="" disabled selected>Pilih Kelas</option>
              <option value="A">Kelas A</option>
              <option value="B">Kelas B</option>
            </select>
            <div class="invalid-feedback">Pilih kelas terlebih dahulu</div>
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

<!-- Modal Akun Guru -->
<div class="modal fade" id="modalAkun" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Akun Guru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <img src="../assets/Hapus.png" alt="Hapus" width="24" class="me-2">
          Konfirmasi Hapus
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Apakah Anda yakin ingin <strong>menghapus</strong> data guru ini?</p>
        <p class="text-muted mt-2 mb-0"><small>Tindakan ini <strong>tidak dapat dibatalkan</strong>.</small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapus">Ya, Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content notification-modal">
      <div class="modal-body text-center p-4">
        <div class="notification-icon-wrapper mb-3">
          <svg class="notification-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="notification-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="notification-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
          </svg>
        </div>
        <h5 class="notification-title mb-2" id="notificationTitle">Berhasil!</h5>
        <p class="notification-message text-muted mb-4" id="notificationMessage">Data berhasil disimpan.</p>
        <button type="button" class="btn btn-primary btn-notification-ok" id="btnNotificationOk">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'), { backdrop: 'static', keyboard: false });
  function showNotification(title, message) {
    document.getElementById('notificationTitle').textContent = title;
    document.getElementById('notificationMessage').textContent = message;
    notificationModal.show();
  }
  document.getElementById('btnNotificationOk').addEventListener('click', () => {
    notificationModal.hide();
    setTimeout(() => location.reload(), 300);
  });

  // ====== JAVASCRIPT VALIDASI ======
  // Validasi NIP – hanya angka & max 18 digit
  document.getElementById("nip").addEventListener("input", function () {
      let nip = this.value;

      // Hanya angka (hapus semua selain angka)
      this.value = nip.replace(/\D/g, "");

      // Jika lebih dari 18 digit → beri warning
      if (this.value.length > 18) {
          document.getElementById("nipWarning").style.display = "block";
      } else {
          document.getElementById("nipWarning").style.display = "none";
      }
  });

  // Validasi Nomor Telepon – hanya angka (tanpa contoh tampilan)
  document.getElementById("telepon").addEventListener("input", function () {
      this.value = this.value.replace(/\D/g, "");
  });

  // Search
  document.getElementById('searchInput').addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('.guru-item').forEach(item => {
      const text = item.textContent.toLowerCase();
      item.style.display = text.includes(keyword) ? '' : 'none';
    });
  });

  const modalGuruBootstrap = new bootstrap.Modal(document.getElementById('modalGuru'));
  const modalHapusBootstrap = new bootstrap.Modal(document.getElementById('modalHapus'));
  const formGuru = document.getElementById('formGuru');
  const idGuruInput = document.getElementById('id_guru');
  const judulModal = document.getElementById('judulModalGuru');
  const tombolSimpan = document.getElementById('btnSimpanGuru');
  const apiURL = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_guru.php";
  let idGuruHapus = null;

  document.getElementById('btnTambahGuru').addEventListener('click', () => {
    judulModal.textContent = "Tambah Guru Baru";
    formGuru.reset();
    formGuru.classList.remove('was-validated');
    idGuruInput.value = "";
    modalGuruBootstrap.show();
  });

  async function fetchAllGuru() {
    try {
      const res = await fetch(apiURL + '?list=all', { cache: 'no-store' });
      const d = await res.json();
      return d.data || [];
    } catch { return []; }
  }

  async function nipExists(nip, excludeId = null) {
    const list = await fetchAllGuru();
    return list.some(g => {
      if (excludeId && String(g.id_guru) === String(excludeId)) return false;
      return String(g.nip).trim() === String(nip).trim();
    });
  }

  formGuru.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!formGuru.checkValidity()) {
      e.stopPropagation();
      formGuru.classList.add('was-validated');
      return;
    }

    const nama = document.getElementById('nama_lengkap').value.trim();
    const nip = document.getElementById('nip').value.trim();
    const alamat = document.getElementById('alamat').value.trim();
    const no_telp = document.getElementById('telepon').value.trim();
    const email = document.getElementById('email').value.trim();
    const kelas = document.getElementById('kelas').value;
    const id = idGuruInput.value.trim();

    if (!/^\d{8,20}$/.test(nip)) {
      alert('NIP harus berisi angka saja (8-20 digit)');
      return;
    }
    if (!/^\d{10,15}$/.test(no_telp)) {
      alert('Nomor telepon harus berisi 10-15 digit angka saja');
      return;
    }

    tombolSimpan.disabled = true;
    tombolSimpan.textContent = "Menyimpan...";

    try {
      if (!id && await nipExists(nip)) {
        alert('NIP sudah terdaftar!');
        tombolSimpan.disabled = false;
        tombolSimpan.textContent = "Simpan";
        return;
      }
      if (id && await nipExists(nip, id)) {
        alert('NIP sudah digunakan guru lain!');
        tombolSimpan.disabled = false;
        tombolSimpan.textContent = "Simpan";
        return;
      }

      const method = id ? "PUT" : "POST";
      const body = { nama_guru: nama, nip, alamat, no_telp, email, kelas };
      if (id) body.id_guru = id;

      const res = await fetch(apiURL, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
      });

      const data = await res.json();

      if (data.status === "success") {
        modalGuruBootstrap.hide();
        showNotification(
          id ? 'Berhasil mengedit data!' : 'Berhasil menambah data!',
          id ? 'Data guru berhasil diperbarui.' : 'Data guru berhasil ditambahkan.'
        );
      } else {
        alert(data.message || "Gagal menyimpan data.");
      }
    } catch (err) {
      alert("Terjadi kesalahan: " + err.message);
    } finally {
      tombolSimpan.disabled = false;
      tombolSimpan.textContent = "Simpan";
    }
  });

  window.editGuru = async (id) => {
    try {
      const res = await fetch(apiURL + `?id_guru=${id}`);
      const data = await res.json();
      if (!data.data) throw new Error('Data tidak ditemukan');

      const g = data.data;
      idGuruInput.value = g.id_guru;
      document.getElementById('nama_lengkap').value = g.nama_guru || '';
      document.getElementById('nip').value = g.nip || '';
      document.getElementById('alamat').value = g.alamat || '';
      document.getElementById('telepon').value = g.no_telp || '';
      document.getElementById('email').value = g.email || '';
      document.getElementById('kelas').value = g.kelas || '';
      judulModal.textContent = "Edit Data Guru";
      formGuru.classList.remove('was-validated');
      modalGuruBootstrap.show();
    } catch (err) {
      alert("Gagal memuat data: " + err.message);
    }
  };

  window.hapusGuru = (id) => {
    idGuruHapus = id;
    modalHapusBootstrap.show();
  };

  document.getElementById('btnKonfirmasiHapus').addEventListener('click', async () => {
    if (!idGuruHapus) return;
    try {
      const res = await fetch(apiURL, {
        method: "DELETE",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ id_guru: idGuruHapus })
      });
      const data = await res.json();
      modalHapusBootstrap.hide();
      if (data.status === "success") {
        showNotification('Berhasil menghapus data!', 'Data guru telah dihapus.');
      } else {
        alert(data.message || "Gagal menghapus.");
      }
    } catch (err) {
      alert("Error: " + err.message);
    } finally {
      idGuruHapus = null;
    }
  });

  window.generateAkun = async (id) => {
    try {
      const res = await fetch(`https://ortuconnect.pbltifnganjuk.com/api/admin/generate_akun.php?tipe=guru&id=${id}`, { cache: "no-store" });
      const data = await res.json();
      if (data.status === "success") {
        const d = data.data;
        document.getElementById("akunNama").textContent = d.nama;
        document.getElementById("akunUsername").textContent = d.username;
        document.getElementById("akunPassword").textContent = d.password;
        document.getElementById("akunRole").textContent = d.role;
        new bootstrap.Modal(document.getElementById('modalAkun')).show();
      } else {
        alert(data.message || "Gagal membuat akun.");
      }
    } catch (err) {
      alert("Error: " + err.message);
    }
  };
 // ===== ANIMASI ICON HEADER DATA GURU - WARNA BIRU =====

document.addEventListener('DOMContentLoaded', function() {
    // Efek khusus untuk icon Data Guru (warna biru)
    const guruIcon = document.querySelector('.header-icon-wrapper.data-guru');
    
    if (guruIcon) {
        // Click effect dengan ripple BIRU
        guruIcon.addEventListener('click', function(e) {
            createRippleEffectBlue(this, e);
            
            // Bounce effect
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
        
        // Hover effect dengan particles BIRU
        guruIcon.addEventListener('mouseenter', function() {
            createParticlesEffectBlue(this);
        });
        
        // Tambah floating animation setelah load
        setTimeout(() => {
            guruIcon.classList.add('animated');
        }, 500);
    }
});

// Fungsi untuk efek ripple dengan warna BIRU
function createRippleEffectBlue(element, event) {
    const rect = element.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    
    const ripple = document.createElement('span');
    ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(33, 150, 243, 0.3); /* BIRU */
        transform: scale(0);
        animation: rippleEffect 0.6s linear;
        width: 100px;
        height: 100px;
        top: ${y - 50}px;
        left: ${x - 50}px;
        pointer-events: none;
        z-index: 1;
    `;
    
    element.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
}

// Fungsi untuk particles effect dengan warna BIRU
function createParticlesEffectBlue(element) {
    // Hapus particles lama
    const oldParticles = element.querySelectorAll('.particle');
    oldParticles.forEach(p => p.remove());
    
    // Buat 4 particles baru dengan warna biru
    for (let i = 0; i < 4; i++) {
        setTimeout(() => {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Random position
            const posX = Math.random() * 80 + 10;
            const posY = Math.random() * 80 + 10;
            
            // Random movement
            const moveX = (Math.random() - 0.5) * 60;
            const moveY = (Math.random() - 0.5) * 60;
            
            // Warna biru dengan variasi
            const colors = [
                'rgba(33, 150, 243, 0.6)',    // Biru utama
                'rgba(66, 165, 245, 0.6)',    // Biru terang
                'rgba(100, 181, 246, 0.6)',   // Biru muda
                'rgba(144, 202, 249, 0.6)'    // Biru sangat muda
            ];
            
            particle.style.cssText = `
                position: absolute;
                width: ${2 + Math.random() * 2}px;
                height: ${2 + Math.random() * 2}px;
                background: ${colors[i % colors.length]};
                border-radius: 50%;
                left: ${posX}%;
                top: ${posY}%;
                animation: particleFloatBlue 1.2s ease-out forwards;
                --moveX: ${moveX}px;
                --moveY: ${moveY}px;
                pointer-events: none;
                z-index: 1;
            `;
            
            element.appendChild(particle);
            setTimeout(() => particle.remove(), 1200);
        }, i * 100);
    }
}

// Tambah style untuk particle animation biru
const particleBlueStyle = document.createElement('style');
particleBlueStyle.textContent = `
    @keyframes particleFloatBlue {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        70% {
            opacity: 0.7;
        }
        100% {
            transform: translate(var(--moveX), var(--moveY)) scale(0);
            opacity: 0;
        }
    }
    
    @keyframes rippleEffect {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(particleBlueStyle);

    // Animasi tombol edit/hapus
    const actionButtons = document.querySelectorAll('.btn-light.border-0');
    actionButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const img = this.querySelector('img');
            if (img) img.style.transform = 'scale(1.15) rotate(5deg)';
        });
        
        btn.addEventListener('mouseleave', function() {
            const img = this.querySelector('img');
            if (img) img.style.transform = 'scale(1) rotate(0)';
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
    
    // Observe semua card guru untuk lazy loading
    document.querySelectorAll('.guru-item').forEach(item => {
        observer.observe(item);
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
    
    const guruItems = document.querySelectorAll('.guru-item');
    guruItems.forEach(item => {
        const textNodes = getTextNodes(item);
        textNodes.forEach(node => {
            const text = node.textContent;
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            
            if (regex.test(text)) {
                const highlighted = text.replace(regex, '<span class="search-highlight">$1</span>');
                const span = document.createElement('span');
                span.innerHTML = highlighted;
                node.parentNode.replaceChild(span, node);
            }
        });
    });
}

// Helper function untuk mendapatkan text nodes
function getTextNodes(element) {
    const textNodes = [];
    const walk = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );
    
    let node;
    while (node = walk.nextNode()) {
        if (node.textContent.trim()) {
            textNodes.push(node);
        }
    }
    
    return textNodes;
}

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'success') {
    // Hapus toast sebelumnya jika ada
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#0d6efd' : type === 'error' ? '#dc3545' : '#ffc107'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove setelah 3 detik
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }
    }, 3000);
}

// Tambah animasi loading saat fetch data
const originalFetch = window.fetch;
window.fetch = async function(...args) {
    // Tampilkan loading indicator jika perlu
    if (args[0].includes('data_guru.php') || args[0].includes('generate_akun.php')) {
        const loader = document.createElement('div');
        loader.id = 'api-loader';
        loader.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 3px solid rgba(13, 110, 253, 0.3);
            border-top: 3px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 9999;
        `;
        document.body.appendChild(loader);
        
        try {
            const response = await originalFetch.apply(this, args);
            loader.remove();
            return response;
        } catch (error) {
            loader.remove();
            throw error;
        }
    }
    
    return originalFetch.apply(this, args);
};

// CSS untuk loader animation
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Animasi saat menambah/menghapus card
function animateCardRemoval(cardElement) {
    cardElement.style.animation = 'fadeOutUp 0.3s ease forwards';
    setTimeout(() => {
        if (cardElement.parentNode) {
            cardElement.remove();
        }
    }, 300);
}

function animateCardAddition(cardElement) {
    cardElement.style.opacity = '0';
    cardElement.style.transform = 'translateY(20px)';
    document.getElementById('guruContainer').prepend(cardElement);
    
    // Trigger reflow untuk memastikan animasi berjalan
    cardElement.offsetHeight;
    
    cardElement.style.transition = 'all 0.5s ease';
    cardElement.style.opacity = '1';
    cardElement.style.transform = 'translateY(0)';
}

// Event listener untuk refresh data dengan animasi
document.addEventListener('dataRefresh', function() {
    const container = document.getElementById('guruContainer');
    container.style.opacity = '0.5';
    container.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        container.style.opacity = '1';
    }, 300);
});
</script>

</body>
</html>