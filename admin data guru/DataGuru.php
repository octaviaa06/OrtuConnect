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
        <h4 class="fw-bold text-primary m-0">Data Guru</h4>
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
</script>

</body>
</html>