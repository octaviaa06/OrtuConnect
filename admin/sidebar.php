<!-- SIDEBAR -->
<link rel="stylesheet" href="../admin/sidebar.css">
<link rel="icon" type="image/x-icon" href="../favicon.ico">

<div id="sidebar" class="sidebar expanded bg-primary text-white p-3">
    <div class="text-center mb-4">
        <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
    </div>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="../dashboard_admin/home_admin.php"
               class="nav-link text-white d-flex align-items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) == 'home_admin.php' ? 'active' : ''; ?>">
                <img src="../assets/Dashboard.png" class="icon" />
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../admin data guru/DataGuru.php"
               class="nav-link text-white d-flex align-items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) == 'DataGuru.php' ? 'active' : ''; ?>">
                <img src="../assets/Data_Guru.png" class="icon" />
                <span class="menu-text">Data Guru</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../admin data siswa/DataSiswa.php"
               class="nav-link text-white d-flex align-items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) == 'DataSiswa.php' ? 'active' : ''; ?>">
                <img src="../assets/Data_Siswa.png" class="icon" />
                <span class="menu-text">Data Murid</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../admin absensi/Absensi.php"
               class="nav-link text-white d-flex align-items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) == 'Absensi.php' ? 'active' : ''; ?>">
                <img src="../assets/absensi.png" class="icon" />
                <span class="menu-text">Absensi</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../admin perizinan/Perizinan.php"
               class="nav-link text-white d-flex align-items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) == 'Perizinan.php' ? 'active' : ''; ?>">
                <img src="../assets/Perizinan.png" class="icon" />
                <span class="menu-text">Perizinan</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../admin kalender/Kalender.php"
               class="nav-link text-white d-flex align-items-center gap-2 <?php echo basename($_SERVER['PHP_SELF']) == 'Kalender.php' ? 'active' : ''; ?>">
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

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
        sidebar.classList.toggle("expanded");
    });
});
</script>
