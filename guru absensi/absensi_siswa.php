<?php
// Contoh data siswa
$daftar_siswa = [
    ["id" => 1, "nama" => "Bryan Mbeumo", "status" => "Hadir"],
    ["id" => 2, "nama" => "Benjamin Sesko", "status" => "Hadir"],
    ["id" => 3, "nama" => "Muhammad Suumbul", "status" => "Sakit"],
    ["id" => 4, "nama" => "Riski", "status" => "Tidak Hadir"],
];

$halaman_aktif = 'absensi';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ===============================
           BODY & BACKGROUND UTAMA
           =============================== */
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background-color: #f8f9fc; /* putih lembut */
            color: #222;
        }

        /* ===============================
           SIDEBAR BIRU TRANSPARAN
           =============================== */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, rgba(59, 91, 219, 0.95), rgba(30, 42, 120, 0.9));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 25px;
            border-radius: 0 16px 16px 0;
            transition: width 0.3s ease;
            overflow: hidden;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar-toggle {
            text-align: center;
            color: white;
            font-size: 26px;
            cursor: pointer;
            margin-bottom: 25px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-top: 40px;
            width: 100%;
        }

        .sidebar ul li {
            width: 100%;
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.2s ease-in-out;
            white-space: nowrap;
        }

        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .sidebar ul li a.active {
            background-color: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .sidebar ul li a img.icon {
            width: 22px;
            height: 22px;
            margin-right: 14px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .sidebar.collapsed a span {
            display: none;
        }

        /* ===============================
           MAIN CONTENT
           =============================== */
        .main-content {
            margin-left: 260px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        .main-content.collapsed {
            margin-left: 100px;
        }

        .judul {
            color: #3B5CCC;
            font-weight: 600;
        }

        .admin-badge {
            background-color: #e9ecef;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #3B5CCC;
        }

        /* ===============================
           FILTER SECTION
           =============================== */
        .filter-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .filter-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .filter-section input[type="date"],
        .filter-section select {
            width: 200px;
        }

        .btn-simpan {
            background-color: #3B5CCC;
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-simpan:hover {
            background-color: #324ab2;
        }

        /* ===============================
           CARD ABSENSI
           =============================== */
        .card {
            background-color: white;
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .card-absensi {
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .card-absensi:hover {
            background-color: #f1f3fe;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </div>

        <ul>
            <li class="nav-item">
                <a href="../dashboard_guru/home_guru.php" class="nav-link <?= $halaman_aktif == 'dashboard' ? 'active' : '' ?>">
                    <img src="../assets/Dashboard.png" class="icon"><span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="DataSiswa.php" class="nav-link <?= $halaman_aktif == 'datasiswa' ? 'active' : '' ?>">
                    <img src="../assets/Data Siswa.png" class="icon"><span>Data Murid</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../guru absensi/Absensi.php" class="nav-link <?= $halaman_aktif == 'absensi' ? 'active' : '' ?>">
                    <img src="../assets/absensi.png" class="icon"><span>Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../guru perizinan/Perizinan.php" class="nav-link <?= $halaman_aktif == 'perizinan' ? 'active' : '' ?>">
                    <img src="../assets/Perizinan.png" class="icon"><span>Perizinan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../guru kalender/Kalender.php" class="nav-link <?= $halaman_aktif == 'kalender' ? 'active' : '' ?>">
                    <img src="../assets/Kalender.png" class="icon"><span>Kalender</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="judul">Absensi</h3>
            <div class="d-flex align-items-center">
                <div class="admin-badge me-2">G</div>
                <span class="fw-bold text-primary">Guru</span>
            </div>
        </div>

        <!-- FILTER SECTION -->
        <div class="filter-section">
            <div class="filter-left">
                <input type="date" class="form-control" placeholder="Tanggal">
                <select class="form-select">
                    <option>Kelas A</option>
                    <option>Kelas B</option>
                    <option>Kelas C</option>
                </select>
            </div>
            <button class="btn-simpan">Simpan</button>
        </div>

        <!-- CARD ABSENSI -->
        <div class="card p-4">
            <h6 class="text-primary fw-bold mb-3">Daftar Absensi</h6>

            <?php foreach ($daftar_siswa as $siswa): ?>
                <div class="card-absensi">
                    <div>
                        <strong><?= $siswa['id'] ?>.</strong> <?= htmlspecialchars($siswa['nama']) ?>
                    </div>
                    <select class="form-select w-auto">
                        <option <?= $siswa['status'] == 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                        <option <?= $siswa['status'] == 'Tidak Hadir' ? 'selected' : '' ?>>Tidak Hadir</option>
                        <option <?= $siswa['status'] == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option <?= $siswa['status'] == 'Alpa' ? 'selected' : '' ?>>Alpa</option>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- JS: Toggle Sidebar -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleButton = document.getElementById('sidebarToggle');

        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        });
    </script>

</body>
</html>
