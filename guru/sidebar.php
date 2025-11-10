<?php 

$active_page = $active_page ?? 'dashboard'; 
?>

<link rel="stylesheet" href="../guru/sidebar.css"> <div id="sidebar" class="sidebar expanded bg-primary text-white p-3">
    <div class="text-center mb-4">
        <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
    </div>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="../dashboard guru/dashboardGuru.php" class="nav-link text-white d-flex align-items-center gap-2 <?= $active_page === 'dashboard' ? 'active' : '' ?>">
                <img src="../assets/Dashboard.png" class="icon" />
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru data siswa/data_siswa.php" class="nav-link text-white d-flex align-items-center gap-2 <?= $active_page === 'data_siswa' ? 'active' : '' ?>">
                <img src="../assets/Data Siswa.png" class="icon" />
                <span class="menu-text">Data Murid</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru absensi/absensi_siswa.php" class="nav-link text-white d-flex align-items-center gap-2 <?= $active_page === 'absensi' ? 'active' : '' ?>">
                <img src="../assets/absensi.png" class="icon" />
                <span class="menu-text">Absensi</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru perizinan/perizinan.php" class="nav-link text-white d-flex align-items-center gap-2 <?= $active_page === 'perizinan' ? 'active' : '' ?>">
                <img src="../assets/Perizinan.png" class="icon" />
                <span class="menu-text">Perizinan</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="../guru kalender/Kalender.php" class="nav-link text-white d-flex align-items-center gap-2 <?= $active_page === 'kalender' ? 'active' : '' ?>">
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
 
    const mainContent = document.getElementById("mainContent") || document.querySelector(".main-content");
    
    
    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
        
      
        if (mainContent) {
            mainContent.classList.toggle('collapsed');
        }
        
        // --- Logika Animasi Swing ---
        if (!sidebar.classList.contains('collapsed')) { // Jika expanded
            const menuTexts = sidebar.querySelectorAll('.menu-text');
            menuTexts.forEach(text => {
                text.classList.remove('animate-swing');
                void text.offsetWidth; 
                text.classList.add('animate-swing');
                setTimeout(() => {
                    text.classList.remove('animate-swing');
                }, 400); 
            });
        }
    });
    

    if (!sidebar.classList.contains('collapsed')) {
        const menuTexts = sidebar.querySelectorAll('.menu-text');
        menuTexts.forEach(text => {
            text.classList.add('animate-swing');
            setTimeout(() => {
                text.classList.remove('animate-swing');
            }, 400); 
        });
    }
});
</script>