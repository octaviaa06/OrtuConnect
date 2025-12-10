<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'DataGuru';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Fungsi Helper untuk Fetch API
function fetchApiData($url)
{
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

// Fungsi untuk mengatasi perbedaan path localhost vs hosting
function getAssetPath($path) {
    // Cek jika sedang di localhost atau hosting
    $isLocalhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) || 
                   (strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
    
    // Untuk localhost: path relatif
    // Untuk hosting: path absolute dari root
    if ($isLocalhost) {
        return $path;
    } else {
        // Hapus ../ jika ada di awal
        $cleanPath = ltrim($path, './');
        $cleanPath = ltrim($cleanPath, '../');
        return '/' . $cleanPath;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Data Guru | OrtuConnect</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Load CSS dengan path yang benar -->
    <link rel="stylesheet" href="<?php echo getAssetPath('style.css'); ?>">
    <link rel="stylesheet" href="<?php echo getAssetPath('notification.css'); ?>">
    <link rel="stylesheet" href="<?php echo getAssetPath('../profil/profil.css'); ?>">
    <link rel="stylesheet" href="<?php echo getAssetPath('../admin/sidebar.css'); ?>">
    
    <!-- CSS Khusus untuk Data Guru -->
    <link rel="stylesheet" href="<?php echo getAssetPath('data_guru_style.css'); ?>">
    
</head>
<body>
<div class="d-flex">
    <?php include getAssetPath('../admin/sidebar.php'); ?>

    <div class="flex-grow-1 main-content"
         style="background-image:url('<?php echo getAssetPath('../background/Data Guru(1).png'); ?>'); background-size:cover; background-position:center; min-height:100vh;">
        <div class="container-fluid py-3">
            <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                <div class="d-flex align-items-center gap-3">
                    <!-- Icon Data Guru -->
                    <div class="header-icon-wrapper data-guru animated">
                        <img src="<?php echo getAssetPath('../assets/Data_Guru.png'); ?>" alt="Data Guru" class="header-icon">
                    </div>
                    <!-- Judul -->
                    <h4 class="fw-bold text-primary m-0">Data Guru</h4>
                </div>
                <?php include getAssetPath('../profil/profil.php'); ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="search-container position-relative">
                    <img src="<?php echo getAssetPath('../assets/cari.png'); ?>" alt="Cari" class="search-icon">
                    <input type="text" id="searchInput" class="form-control search-input"
                           placeholder="Cari guru berdasarkan nama, NIP, atau email...">
                </div>
                <button class="btn btn-primary rounded-3 px-4 btn-add-student" id="btnTambahGuru">
                    <span style="font-weight:600;">+ Tambah Guru</span>
                </button>
            </div>

            <div class="row g-3" id="guruContainer">
                <?php if (empty($guruList)): ?>
                    <div class="col-12">
                        <div class="empty-state text-center">
                            <div class="empty-icon-wrapper mb-4">
                                <img src="<?php echo getAssetPath('../assets/Data_Guru.png'); ?>" alt="No Data" width="120" style="opacity: 0.5;">
                            </div>
                            <h5 class="text-muted mb-2">Tidak ada data guru</h5>
                            <p class="text-muted small">Silakan tambahkan data guru baru</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($guruList as $guru):
                        $nama = htmlspecialchars($guru['nama_guru']);
                        $kata = explode(' ', $nama);
                        $inisial = (count($kata) >= 2)
                            ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1))
                            : strtoupper(substr($kata[0], 0, 2));
                        ?>
                        <div class="col-md-4 mb-3 guru-item" data-id="<?= $guru['id_guru'] ?>">
                            <div class="card card-guru shadow-sm border-0 p-3 d-flex flex-column justify-content-between h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-inisial bg-primary text-white me-3">
                                        <?= $inisial ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1 fw-bold" style="color: #1e3a8a;"><?= $nama ?></h5>
                                        <span class="badge badge-class">NIP: <?= htmlspecialchars($guru['nip']); ?></span>
                                    </div>
                                </div>

                                <div class="card-body pt-0 pb-2 px-0">
                                    <div class="info-item mb-2">
                                        <span class="info-label">Email:</span>
                                        <span class="info-value"><?= htmlspecialchars($guru['email']); ?></span>
                                    </div>
                                    <div class="info-item mb-2">
                                        <span class="info-label">No. Telp:</span>
                                        <span class="info-value"><?= htmlspecialchars($guru['no_telp']); ?></span>
                                    </div>
                                    <div class="info-item mb-2">
                                        <span class="info-label">Kelas:</span>
                                        <span class="info-value"><?= htmlspecialchars($guru['kelas']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Alamat:</span>
                                        <span class="info-value"><?= nl2br(htmlspecialchars($guru['alamat'])); ?></span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2"
                                     style="border-top: 1px solid rgba(0,0,0,0.05);">
                                    <button class="btn custom-btn btn-success btn-generate px-3 py-2"
                                            onclick="generateAkun('<?= $guru['id_guru'] ?>')"
                                            style="border-radius: 12px; font-size: 14px;">
                                        <span style="font-weight:600;">Lihat Akun</span>
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-action btn-edit" onclick="editGuru('<?= $guru['id_guru'] ?>')"
                                                title="Edit">
                                            <img src="<?php echo getAssetPath('../assets/edit.png'); ?>" alt="Edit" width="20">
                                        </button>
                                        <button class="btn btn-action btn-delete"
                                                onclick="hapusGuru('<?= $guru['id_guru'] ?>')" title="Hapus">
                                            <img src="<?php echo getAssetPath('../assets/Hapus.png'); ?>" alt="Hapus" width="20">
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
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="judulModalGuru">Tambah Guru Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formGuru" novalidate>
                <input type="hidden" name="id_guru" id="id_guru">
                <div class="modal-body">
                    <!-- Nama Lengkap -->
                    <div class="form-group mb-4">
                        <label for="nama_lengkap" class="form-label fw-semibold d-flex align-items-center">
                            <span class="me-2">Nama Lengkap</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control custom-input" id="nama_lengkap" name="nama_lengkap" 
                               placeholder="Masukkan nama lengkap guru" required>
                        <div class="error-message" id="namaError">Nama lengkap wajib diisi</div>
                    </div>

                    <!-- NIP -->
                    <div class="form-group mb-4">
                        <label for="nip" class="form-label fw-semibold d-flex align-items-center">
                            <span class="me-2">NIP</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control custom-input" id="nip" name="nip" 
                               placeholder="Masukkan NIP guru" required maxlength="18">
                        <div class="error-message" id="nipError">NIP harus 8-18 digit angka</div>
                        <div class="hint-text">NIP tidak boleh lebih dari 18 angka</div>
                    </div>

                    <!-- Alamat -->
                    <div class="form-group mb-4">
                        <label for="alamat" class="form-label fw-semibold d-flex align-items-center">
                            <span class="me-2">Alamat</span>
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control custom-input" id="alamat" name="alamat" 
                                  rows="2" placeholder="Masukkan alamat lengkap" required></textarea>
                        <div class="error-message" id="alamatError">Alamat wajib diisi</div>
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="form-group mb-4">
                        <label for="telepon" class="form-label fw-semibold d-flex align-items-center">
                            <span class="me-2">Nomor Telepon</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control custom-input" id="telepon" name="telepon" 
                               placeholder="Contoh: 081234567890" required maxlength="15">
                        <div class="error-message" id="teleponError">Nomor telepon harus 10-15 digit angka</div>
                        <div class="hint-text">Contoh: 081234567890</div>
                    </div>

                    <!-- Email -->
                    <div class="form-group mb-4">
                        <label for="email" class="form-label fw-semibold d-flex align-items-center">
                            <span class="me-2">Email</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control custom-input" id="email" name="email" 
                               placeholder="Masukkan email guru" required>
                        <div class="error-message" id="emailError">Email tidak valid</div>
                    </div>

                    <!-- Kelas -->
                    <div class="form-group mb-4">
                        <label for="kelas" class="form-label fw-semibold d-flex align-items-center">
                            <span class="me-2">Kelas</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-control custom-input filter-select" id="kelas" name="kelas" required>
                            <option value="" disabled selected>-- Pilih Kelas --</option>
                            <option value="A">Kelas A</option>
                            <option value="B">Kelas B</option>
                            <option value="C">Kelas C</option>
                            <option value="D">Kelas D</option>
                        </select>
                        <div class="error-message" id="kelasError">Pilih kelas terlebih dahulu</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn custom-btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn custom-btn btn-primary" id="btnSimpanGuru">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Akun Guru -->
<div class="modal fade" id="modalAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title">Akun Guru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="account-info">
                    <div class="info-row">
                        <span class="info-label">Nama</span>
                        <span class="info-value" id="akunNama"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Username</span>
                        <span class="info-value" id="akunUsername"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Password</span>
                        <span class="info-value" id="akunPassword"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Role</span>
                        <span class="info-value" id="akunRole"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn custom-btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title">
                    <img src="<?php echo getAssetPath('../assets/Hapus.png'); ?>" alt="Hapus" width="24" class="me-2"
                         style="filter: brightness(0) invert(1);">
                    Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Apakah Anda yakin ingin <strong>menghapus</strong> data guru ini?</p>
                <p class="text-muted mt-2 mb-0"><small>Tindakan ini <strong>tidak dapat dibatalkan</strong>.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn custom-btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn custom-btn btn-success" id="btnKonfirmasiHapus">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
     data-bs-keyboard="false">
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
                <button type="button" class="btn custom-btn btn-primary btn-notification-ok" id="btnNotificationOk">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript code tetap sama seperti sebelumnya...
    // ====== INISIALISASI MODAL ======
    const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'), {
        backdrop: 'static',
        keyboard: false
    });

    const modalGuruBootstrap = new bootstrap.Modal(document.getElementById('modalGuru'));
    const modalHapusBootstrap = new bootstrap.Modal(document.getElementById('modalHapus'));
    const formGuru = document.getElementById('formGuru');
    const idGuruInput = document.getElementById('id_guru');
    const judulModal = document.getElementById('judulModalGuru');
    const tombolSimpan = document.getElementById('btnSimpanGuru');
    const apiURL = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_guru.php";
    let idGuruHapus = null;

    // ====== FUNGSI NOTIFIKASI ======
    function showNotification(title, message, type = 'success') {
        document.getElementById('notificationTitle').textContent = title;
        document.getElementById('notificationMessage').textContent = message;
        
        // Atur warna icon berdasarkan type
        const iconCircle = document.querySelector('.notification-checkmark-circle');
        if (type === 'success') {
            iconCircle.style.stroke = '#28a745';
        } else if (type === 'error') {
            iconCircle.style.stroke = '#dc3545';
        } else if (type === 'warning') {
            iconCircle.style.stroke = '#ffc107';
        }
        
        notificationModal.show();
    }

    document.getElementById('btnNotificationOk').addEventListener('click', () => {
        notificationModal.hide();
        setTimeout(() => location.reload(), 300);
    });

    // ====== FUNGSI VALIDASI ======
    function showError(inputId, message) {
        const errorElement = document.getElementById(inputId + 'Error');
        const inputElement = document.getElementById(inputId);
        
        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            inputElement.classList.add('is-invalid');
            inputElement.classList.remove('is-valid');
        }
    }
    
    function hideError(inputId) {
        const errorElement = document.getElementById(inputId + 'Error');
        const inputElement = document.getElementById(inputId);
        
        if (errorElement && inputElement) {
            errorElement.style.display = 'none';
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        }
    }
    
    function validateNama() {
        const nama = document.getElementById('nama_lengkap').value.trim();
        if (nama === '') {
            showError('nama', 'Nama lengkap wajib diisi');
            return false;
        } else if (nama.length < 3) {
            showError('nama', 'Nama minimal 3 karakter');
            return false;
        } else {
            hideError('nama');
            return true;
        }
    }
    
    function validateNIP() {
        const nip = document.getElementById('nip').value.trim();
        if (nip === '') {
            showError('nip', 'NIP wajib diisi');
            return false;
        } else if (!/^\d+$/.test(nip)) {
            showError('nip', 'NIP hanya boleh berisi angka');
            return false;
        } else if (nip.length < 8 || nip.length > 18) {
            showError('nip', 'NIP harus 8-18 digit angka');
            return false;
        } else {
            hideError('nip');
            return true;
        }
    }
    
    function validateAlamat() {
        const alamat = document.getElementById('alamat').value.trim();
        if (alamat === '') {
            showError('alamat', 'Alamat wajib diisi');
            return false;
        } else if (alamat.length < 10) {
            showError('alamat', 'Alamat terlalu pendek');
            return false;
        } else {
            hideError('alamat');
            return true;
        }
    }
    
    function validateTelepon() {
        const telepon = document.getElementById('telepon').value.trim();
        if (telepon === '') {
            showError('telepon', 'Nomor telepon wajib diisi');
            return false;
        } else if (!/^\d+$/.test(telepon)) {
            showError('telepon', 'Hanya boleh berisi angka');
            return false;
        } else if (telepon.length < 10 || telepon.length > 15) {
            showError('telepon', 'Nomor telepon harus 10-15 digit');
            return false;
        } else {
            hideError('telepon');
            return true;
        }
    }
    
    function validateEmail() {
        const email = document.getElementById('email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email === '') {
            showError('email', 'Email wajib diisi');
            return false;
        } else if (!emailRegex.test(email)) {
            showError('email', 'Format email tidak valid');
            return false;
        } else {
            hideError('email');
            return true;
        }
    }
    
    function validateKelas() {
        const kelas = document.getElementById('kelas').value;
        if (kelas === '' || kelas === null) {
            showError('kelas', 'Kelas wajib dipilih');
            return false;
        } else {
            hideError('kelas');
            return true;
        }
    }

    // ====== EVENT LISTENERS UNTUK VALIDASI REAL-TIME ======
    document.addEventListener('DOMContentLoaded', function() {
        // Nama
        document.getElementById('nama_lengkap').addEventListener('blur', validateNama);
        document.getElementById('nama_lengkap').addEventListener('input', function() {
            if (this.value.trim() !== '') hideError('nama');
        });
        
        // NIP
        document.getElementById('nip').addEventListener('blur', validateNIP);
        document.getElementById('nip').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 18) {
                this.value = this.value.substring(0, 18);
            }
            if (this.value.trim() !== '' && /^\d+$/.test(this.value)) hideError('nip');
        });
        
        // Alamat
        document.getElementById('alamat').addEventListener('blur', validateAlamat);
        document.getElementById('alamat').addEventListener('input', function() {
            if (this.value.trim() !== '') hideError('alamat');
        });
        
        // Telepon
        document.getElementById('telepon').addEventListener('blur', validateTelepon);
        document.getElementById('telepon').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 15) {
                this.value = this.value.substring(0, 15);
            }
            if (this.value.trim() !== '' && /^\d+$/.test(this.value)) hideError('telepon');
        });
        
        // Email
        document.getElementById('email').addEventListener('blur', validateEmail);
        document.getElementById('email').addEventListener('input', function() {
            if (this.value.trim() !== '') hideError('email');
        });
        
        // Kelas
        document.getElementById('kelas').addEventListener('change', validateKelas);
        
        // Reset form saat modal ditutup
        document.getElementById('modalGuru').addEventListener('hidden.bs.modal', function() {
            formGuru.reset();
            // Hapus semua error message
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            // Hapus semua class validasi
            document.querySelectorAll('.custom-input').forEach(el => {
                el.classList.remove('is-invalid', 'is-valid');
            });
            idGuruInput.value = "";
        });
    });

    // ====== FUNGSI TOAST ======
    function showToast(message, type = 'success') {
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
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }

    // ====== FUNGSI UTAMA ======
    // Tambah Guru
    document.getElementById('btnTambahGuru').addEventListener('click', () => {
        judulModal.textContent = "Tambah Guru Baru";
        modalGuruBootstrap.show();
    });

    // Search
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll('.guru-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(keyword) ? '' : 'none';
        });
    });

    // Fetch semua guru untuk validasi NIP
    async function fetchAllGuru() {
        try {
            const res = await fetch(apiURL + '?list=all', {cache: 'no-store'});
            const d = await res.json();
            return d.data || [];
        } catch {
            return [];
        }
    }

    async function nipExists(nip, excludeId = null) {
        const list = await fetchAllGuru();
        return list.some(g => {
            if (excludeId && String(g.id_guru) === String(excludeId)) return false;
            return String(g.nip).trim() === String(nip).trim();
        });
    }

    // Submit Form
    formGuru.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validasi semua field
        const isNamaValid = validateNama();
        const isNIPValid = validateNIP();
        const isAlamatValid = validateAlamat();
        const isTeleponValid = validateTelepon();
        const isEmailValid = validateEmail();
        const isKelasValid = validateKelas();
        
        if (!(isNamaValid && isNIPValid && isAlamatValid && isTeleponValid && isEmailValid && isKelasValid)) {
            // Scroll ke field pertama yang error
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            showToast('Harap perbaiki data yang masih salah', 'error');
            return;
        }
        
        // Ambil data dari form
        const nama = document.getElementById('nama_lengkap').value.trim();
        const nip = document.getElementById('nip').value.trim();
        const alamat = document.getElementById('alamat').value.trim();
        const telepon = document.getElementById('telepon').value.trim();
        const email = document.getElementById('email').value.trim();
        const kelas = document.getElementById('kelas').value;
        const id = idGuruInput.value.trim();
        
        tombolSimpan.disabled = true;
        tombolSimpan.textContent = "Menyimpan...";
        
        try {
            // Validasi NIP unik
            if (!id && await nipExists(nip)) {
                showError('nip', 'NIP sudah terdaftar!');
                showToast('NIP sudah terdaftar!', 'error');
                tombolSimpan.disabled = false;
                tombolSimpan.textContent = "Simpan";
                return;
            }
            
            if (id && await nipExists(nip, id)) {
                showError('nip', 'NIP sudah digunakan guru lain!');
                showToast('NIP sudah digunakan guru lain!', 'error');
                tombolSimpan.disabled = false;
                tombolSimpan.textContent = "Simpan";
                return;
            }
            
            // Kirim data ke API
            const method = id ? "PUT" : "POST";
            const body = {
                nama_guru: nama,
                nip,
                alamat,
                no_telp: telepon,
                email,
                kelas
            };
            
            if (id) body.id_guru = id;
            
            const res = await fetch(apiURL, {
                method,
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify(body)
            });
            
            const data = await res.json();
            if (data.status === "success") {
                modalGuruBootstrap.hide();
                showNotification(
                    id ? 'Berhasil mengedit data!' : 'Berhasil menambah data!',
                    id ? 'Data guru berhasil diperbarui.' : 'Data guru berhasil ditambahkan.',
                    'success'
                );
            } else {
                showToast(data.message || "Gagal menyimpan data.", 'error');
            }
        } catch (err) {
            showToast("Terjadi kesalahan: " + err.message, 'error');
        } finally {
            tombolSimpan.disabled = false;
            tombolSimpan.textContent = "Simpan";
        }
    });

    // Edit Guru
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
            
            // Reset validasi
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.custom-input').forEach(el => {
                el.classList.remove('is-invalid', 'is-valid');
            });
            
            modalGuruBootstrap.show();
        } catch (err) {
            showToast("Gagal memuat data: " + err.message, 'error');
        }
    };

    // Hapus Guru
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
                body: JSON.stringify({id_guru: idGuruHapus})
            });
            
            const data = await res.json();
            modalHapusBootstrap.hide();
            
            if (data.status === "success") {
                showNotification('Berhasil menghapus data!', 'Data guru telah dihapus.', 'success');
            } else {
                showToast(data.message || "Gagal menghapus.", 'error');
            }
        } catch (err) {
            showToast("Error: " + err.message, 'error');
        } finally {
            idGuruHapus = null;
        }
    });

    // Generate Akun
    window.generateAkun = async (id) => {
        try {
            const res = await fetch(`https://ortuconnect.pbltifnganjuk.com/api/admin/generate_akun.php?tipe=guru&id=${id}`, {
                cache: "no-store"
            });
            const data = await res.json();
            
            if (data.status === "success") {
                const d = data.data;
                document.getElementById("akunNama").textContent = d.nama;
                document.getElementById("akunUsername").textContent = d.username;
                document.getElementById("akunPassword").textContent = d.password;
                document.getElementById("akunRole").textContent = d.role;
                
                new bootstrap.Modal(document.getElementById('modalAkun')).show();
            } else {
                showToast(data.message || "Gagal melihat akun.", 'error');
            }
        } catch (err) {
            showToast("Error: " + err.message, 'error');
        }
    };

    // ====== ANIMASI & EFFECT ======
    document.addEventListener('DOMContentLoaded', function () {
        const guruIcon = document.querySelector('.header-icon-wrapper.data-guru');
        if (guruIcon) {
            guruIcon.addEventListener('click', function (e) {
                createRippleEffectBlue(this, e);
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
            
            guruIcon.addEventListener('mouseenter', function () {
                createParticlesEffectBlue(this);
            });
            
            setTimeout(() => {
                guruIcon.classList.add('animated');
            }, 500);
        }
        
        // Intersection Observer untuk animasi card
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
        
        document.querySelectorAll('.guru-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'all 0.5s ease';
            observer.observe(item);
        });
    });

    function createRippleEffectBlue(element, event) {
        const rect = element.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        const ripple = document.createElement('span');
        
        ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(33, 150, 243, 0.3);
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

    function createParticlesEffectBlue(element) {
        const oldParticles = element.querySelectorAll('.particle');
        oldParticles.forEach(p => p.remove());
        
        for (let i = 0; i < 4; i++) {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const posX = Math.random() * 80 + 10;
                const posY = Math.random() * 80 + 10;
                const moveX = (Math.random() - 0.5) * 60;
                const moveY = (Math.random() - 0.5) * 60;
                const colors = [
                    'rgba(33, 150, 243, 0.6)',
                    'rgba(66, 165, 245, 0.6)',
                    'rgba(100, 181, 246, 0.6)',
                    'rgba(144, 202, 249, 0.6)'
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

    // Tambahkan style untuk animasi
    const style = document.createElement('style');
    style.textContent = `
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
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        .guru-item {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }
    `;
    document.head.appendChild(style);
</script>

</body>
</html>