<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin | OrtuConnect</title>
<link rel="stylesheet" href="../assets/dashboard_admin.css">
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="../assets/logo.png" alt="Logo OrtuConnect">
        <h2>OrtuConnect</h2>
    </div>
    <ul class="menu">
        <li><a href="#"><img src="../assets/dashboard.png" alt="">Dashboard</a></li>
        <li><a href="#"><img src="../assets/Data Guru.png" alt="">Data Guru</a></li>
        <li><a href="#"><img src="../assets/Absensi.png" alt="">Absensi</a></li>
        <li><a href="#"><img src="../assets/Perizinan.png" alt="">Perizinan</a></li>
        <li><a href="#"><img src="../assets/Kalender.png" alt="">Kalender</a></li>
    </ul>
</div>

<div class="main">
    <header>
        <h1>Dashboard</h1>
        <div class="profile">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
            <span class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </header>

    <section class="cards">
        <div class="card"></div>
        <div class="card"></div>
        <div class="card"></div>
    </section>
</div>
</body>
</html>
