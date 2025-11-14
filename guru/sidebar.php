<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<link rel="stylesheet" href="sidebar.css">

<div id="sidebar" class="sidebar expanded bg-primary text-white p-3">
    <div class="text-center mb-4">
        <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
    </div>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="../dashboard_guru/home_guru.php" class="nav-link text-white d-flex align-items-center gap-2">
                <img src="../assets/Dashboard.png" class="icon" />
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru data siswa/data_siswa.php" class="nav-link text-white d-flex align-items-center gap-2">
                <img src="../assets/Data Siswa.png" class="icon" />
                <span class="menu-text">Data Murid</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru absensi/absensi_siswa.php" class="nav-link text-white d-flex align-items-center gap-2">
                <img src="../assets/absensi.png" class="icon" />
                <span class="menu-text">Absensi</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru perizinan/perizinan.php" class="nav-link text-white d-flex align-items-center gap-2">
                <img src="../assets/Perizinan.png" class="icon" />
                <span class="menu-text">Perizinan</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru kalender/kalender.php" class="nav-link text-white d-flex align-items-center gap-2">
                <img src="../assets/Kalender.png" class="icon" />
                <span class="menu-text">Kalender</span>
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
const mainContent = document.querySelector(".main-content");

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
        sidebar.classList.toggle("expanded");
    });
});
</script>