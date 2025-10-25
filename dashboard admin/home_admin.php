<?php

session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php"); 
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$initial = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | OrtuConnect</title>
    <link rel="stylesheet" href="../../assets/dashboard_admin.css">
</head>
<body>




    <ul class="menu">
        <li><a href="#" class="active"><img src="../../assets/Dashboard Admin.png" alt="Dashboard"></a></li>
        
        <li><a href="#"><img src="../../assets/Data Guru.png" alt="Data Guru"></a></li>
        <li><a href="#"><img src="../../assets/Absensi.png" alt="Absensi"></a></li>
        <li><a href="#"><img src="../../assets/checked-person.png" alt="Profil"></a></li> 
        <li><a href="#"><img src="../../assets/Perizinan.png" alt="Perizinan"></a></li>
        <li><a href="#"><img src="../../assets/Kalender.png" alt="Kalender"></a></li>
        
        <li><a href="../../logout/logout.php"><img src="../../assets/keluar.png" alt="Keluar"></a></li>
    </ul>
</div>

<div class="main">
    <header>
        <h1>Dashboard</h1>
        <div class="profile">
            <span class="header-title">Dashboard Admin</span>

            <div class="user-info">
                <div class="avatar"><?php echo $initial; ?></div> 
                <span class="name"><?php echo $username; ?></span> 
            </div>
        </div>
    </header>


</html>