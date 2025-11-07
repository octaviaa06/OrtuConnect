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
        body {
            background-color: #f8f9fc;
            font-family: "Poppins", sans-serif;
            margin: 0;
            transition: all 0.3s ease;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background-color: #3B5CCC;
            min-height: 100vh;
            width: 240px;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 25px;
            border-radius: 0 12px 12px 0;
            transition: width 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 80px;
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
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            white-space: nowrap;
        }

        .sidebar ul li a i {
            font-size: 22px;
            margin-right: 14px;
            min-width: 24px;
            text-align: center;
        }

        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .sidebar ul li a.active {
            background-color: #ffffff;
            color: #3B5CCC;
        }

        .sidebar ul li a.active i {
            color: #3B5CCC;
        }

        .sidebar.collapsed a span {
            display: none;
        }

        .sidebar-toggle {
            text-align: center;
            color: white;
            font-size: 26px;
            cursor: pointer;
            margin-bottom: 25px;
        }

        /* ===== MAIN CONTENT ===== */
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

        /* ===== FILTER SECTION ===== */
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
            background-color: #334FBA;
        }

        /* ===== CARD ABSENSI ===== */
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
            <li><a href="dashboard.php" class="<?= $halaman_aktif == 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid-fill"></i><span>Dashboard</span></a></li>
            <li><a href="murid.php" class="<?= $halaman_aktif == 'murid' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i><span>Data Murid</span></a></li>
            <li><a href="absensi.php" class="<?= $halaman_aktif == 'absensi' ? 'active' : '' ?>"><i class="bi bi-person-check-fill"></i><span>Absensi</span></a></li>
            <li><a href="izin.php" class="<?= $halaman_aktif == 'izin' ? 'active' : '' ?>"><i class="bi bi-clipboard2-fill"></i><span>Perizinan</span></a></li>
            <li><a href="kalender.php" class="<?= $halaman_aktif == 'kalender' ? 'active' : '' ?>"><i class="bi bi-calendar3-event-fill"></i><span>Kalender</span></a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="judul"><i class="bi bi-person-check-fill me-2"></i>Absensi</h3>
            <div class="d-flex align-items-center">
                <div class="admin-badge me-2">A</div>
                <span class="fw-bold text-primary">Admin</span>
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
            <button class="btn-simpan"><i class="bi bi-save me-1"></i> Simpan</button>
        </div>

        <!-- CARD ABSENSI -->
        <div class="card p-4">
            <h6 class="text-primary fw-bold mb-3">
                <i class="bi bi-person-check me-2"></i>Daftar Absensi
            </h6>

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
