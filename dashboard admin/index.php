<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | OrtuConnect</title>
    <link rel="stylesheet" href="../../assets/dashboard_admin.png"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    
    <aside class="sidebar">
        <div class="logo">
            <img src="../../assets/OrtuConnect_Logo.png" alt="OrtuConnect Logo">
        </div>
        <ul class="nav-menu">
            <li><a href="#" class="nav-item"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="#" class="nav-item"><i class="fas fa-users"></i> Data Guru</a></li>
            <li><a href="#" class="nav-item"><i class="fas fa-user-friends"></i> Data Siswa</a></li>
            <li><a href="#" class="nav-item"><i class="fas fa-user-check"></i> Absensi</a></li>
            <li><a href="#" class="nav-item"><i class="fas fa-clipboard-list"></i> Perizinan</a></li>
            <li><a href="#" class="nav-item"><i class="fas fa-calendar-alt"></i> Kalender</a></li>
        </ul>
        <div class="logout-link">
            <a href="../../logout/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1 class="page-title">Dashboard</h1>
            <div class="profile-info">
                <div class="avatar"><?php echo $initial ?? 'A'; ?></div> 
                <span class="user-role"><?php echo $username ?? 'Admin'; ?></span>
            </div>
        </header>

        <section class="content-grid">
            <div class="content-card"></div>
            <div class="content-card"></div>
            <div class="content-card"></div>
        </section>

        </main>
</div>

</body>
</html>