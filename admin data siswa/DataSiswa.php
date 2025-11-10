<?php
session_start();

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Ambil data murid dari API
$api_url = "https://ortuconnect.atwebpages.com/api/admin/data_siswa.php";
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
<title>Data Murid | OrtuConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="datasiswa.css">
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
            <li class="nav-item"><a href="../admin data guru/DataGuru.php" class="nav-link"><img src="../assets/Data Guru.png" class="icon"><span>Data Guru</span></a></li>
            <li class="nav-item"><a href="DataSiswa.php" class="nav-link active"><img src="../assets/Data Siswa.png" class="icon"><span>Data Murid</span></a></li>
            <li class="nav-item"><a href="../admin absensi/Absensi.php" class="nav-link"><img src="../assets/absensi.png" class="icon"><span>Absensi</span></a></li>
            <li class="nav-item"><a href="../admin perizinan/Perizinan.php" class="nav-link"><img src="../assets/Perizinan.png" class="icon"><span>Perizinan</span></a></li>
            <li class="nav-item"><a href="../admin kalender/Kalender.php" class="nav-link"><img src="../assets/Kalender.png" class="icon"><span>Kalender</span></a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Siswa.png'); background-size:cover; background-position:center;">
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

                <button class="btn btn-primary rounded-3 px-4" id="openPopup">
                    <span style="font-weight:600;">+ Tambah Murid Baru</span>
                </button>
            </div>

            <!-- CARD LIST MURID -->
            <div class="row g-3" id="siswaContainer">
                <?php if (empty($siswaList)): ?>
                    <p class="text-muted">Tidak ada data murid.</p>
                <?php else: ?>
                    <?php foreach ($siswaList as $siswa): ?>
                        <div class="col-md-4 mb-3 siswa-item">
                            <div class="card guru-card shadow-sm border-0 p-3 d-flex flex-column justify-content-between" style="border-radius:16px;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-inisial bg-primary text-white me-3" style="width:50px;height:50px;border-radius:50%;font-weight:bold;font-size:18px;display:flex;align-items:center;justify-content:center;">
                                        <?= strtoupper(substr($siswa['nama_siswa'],0,2)) ?>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0"><?= htmlspecialchars($siswa['nama_siswa']); ?></h5>
                                        <small class="mb-1"><strong>Kelas </strong><?= htmlspecialchars($siswa['kelas']); ?></small>
                                    </div>
                                </div>
                                <div class="card-body pt-0 pb-2 px-0">
                                    <p class="mb-1"><strong>Tanggal Lahir:</strong> <?= htmlspecialchars($siswa['tgl_lahir']); ?></p>
                                    <p class="mb-1"><strong>Orang Tua:</strong> <?= htmlspecialchars($siswa['nama_wali']); ?></p>
                                    <p class="mb-1"><strong>No. Telepon:</strong> <?= htmlspecialchars($siswa['no_wali']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- === POPUP TAMBAH MURID === -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup-box">
        <h2>Tambah Murid Baru</h2>
        <p>Masukan Data Murid Baru untuk Ditambahkan ke Sistem</p>

        <form id="formTambahMurid">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_siswa" required>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="kelas" required>
                        <option value="Kelas A">Kelas A</option>
                        <option value="Kelas B">Kelas B</option>
                        <option value="Kelas C">Kelas C</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="alamat" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="Laki-laki">Laki - Laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" required> 
                </div>
                <div class="form-group">
                    <label>Nama Orang tua/Wali</label>
                    <input type="text" name="nama_wali" required>
                </div>
                <div class="form-group full">
                    <label>No Telepon Orang tua/Wali</label>
                    <input type="text" name="no_wali" required>
                </div>
            </div>
            <div class="popup-buttons">
                <button type="button" class="btn-batal" id="closePopup">Batal</button>
                <button type="submit" class="btn-konfirmasi">Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    document.getElementById('toggleSidebar').addEventListener('click', () => sidebar.classList.toggle('collapsed'));

    // Profile Card Toggle
    const profileBtn = document.getElementById('profileToggle');
    const profileCard = document.getElementById('profileCard');
    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileCard.classList.toggle('show');
    });
    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target)) profileCard.classList.remove('show');
    });

    // Search Function
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', () => {
        const keyword = searchInput.value.toLowerCase();
        document.querySelectorAll('.siswa-item').forEach(item => {
            const nama = item.querySelector('.card-title').textContent.toLowerCase();
            const kelas = item.querySelector('small').textContent.toLowerCase();
            item.style.display = nama.includes(keyword) || kelas.includes(keyword) ? 'block' : 'none';
        });
    });

    // Popup Tambah Murid
    const popupOverlay = document.getElementById('popupOverlay');
    document.getElementById('openPopup').addEventListener('click', () => popupOverlay.style.display = 'flex');
    document.getElementById('closePopup').addEventListener('click', () => popupOverlay.style.display = 'none');

    // Submit Tambah Murid (dummy)
    document.getElementById('formTambahMurid').addEventListener('submit', e => {
        e.preventDefault();
        alert("Fungsi tambah murid akan diimplementasikan pada API backend.");
        popupOverlay.style.display = 'none';
    });
});
</script>
<style>
</style>
</body>
</html>
