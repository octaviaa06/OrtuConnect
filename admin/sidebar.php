<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace(basename($script_name), '', $script_name);
$base_url = $protocol . $_SERVER['HTTP_HOST'] . rtrim($base_path, '/\\') . '/';

function asset_url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}
?>

<!-- Hamburger Button (Mobile/Tablet) -->
<button class="hamburger-btn" id="hamburgerBtn">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div id="sidebar" class="sidebar expanded">
    <!-- Tombol Collapse (Desktop) -->
    <div class="sidebar-header text-center">
        <img src="<?= asset_url('../assets/slide.png') ?>" 
             alt="Toggle" 
             class="slide-btn" 
             id="toggleSidebarBtn"
             style="cursor:pointer;">
    </div>

    <!-- Menu -->
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a href="<?= asset_url('../dashboard_admin/home_admin.php') ?>" class="nav-link">
                <img src="<?= asset_url('../assets/Dashboard.png') ?>" class="icon" alt="Dashboard">
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="<?= asset_url('../admin data guru/DataGuru.php') ?>" 
               class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'DataGuru.php') ? 'active' : '' ?>">
                <img src="<?= asset_url('../assets/Data_Guru.png') ?>" class="icon" alt="Guru">
                <span class="menu-text">Data Guru</span>
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="<?= asset_url('../admin data siswa/DataSiswa.php') ?>" 
               class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'DataSiswa.php') ? 'active' : '' ?>">
                <img src="<?= asset_url('../assets/Data_Siswa.png') ?>" class="icon" alt="Siswa">
                <span class="menu-text">Data Murid</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= asset_url('../admin absensi/Absensi.php') ?>" class="nav-link">
                <img src="<?= asset_url('../assets/absensi.png') ?>" class="icon" alt="Absensi">
                <span class="menu-text">Absensi</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= asset_url('../admin perizinan/Perizinan.php') ?>" class="nav-link">
                <img src="<?= asset_url('../assets/Perizinan.png') ?>" class="icon" alt="Perizinan">
                <span class="menu-text">Perizinan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= asset_url('../admin kalender/Kalender.php') ?>" class="nav-link">
                <img src="<?= asset_url('../assets/Kalender.png') ?>" class="icon" alt="Kalender">
                <span class="menu-text">Kalender</span>
            </a>
        </li>
    </ul>
</div>


<link rel="stylesheet" href="<?= asset_url('../admin/sidebar.css?v=1.5') ?>">

<script>

document.addEventListener('DOMContentLoaded', function() {
    const sidebar       = document.getElementById('sidebar');
    const overlay       = document.getElementById('sidebarOverlay');
    const hamburger     = document.getElementById('hamburgerBtn');
    const toggleBtn     = document.getElementById('toggleSidebarBtn');

    // Toggle utama
    function toggleSidebar() {
        if (window.innerWidth < 992) {
            // Mobile / Tablet
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            hamburger.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        } else {
            // Desktop
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('expanded');
        }
    }

    // Event listener
    hamburger.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);

    // Auto close saat klik menu (mobile only)
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Highlight menu aktif
    const currentPage = location.pathname.split('/').pop();
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
    });

    // Resize handler (paling penting biar ga rusak saat ganti ukuran layar)
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
                sidebar.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed', 'expanded');
            }
        }, 200);
    });

    // ESC key to close
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    });

    // Swipe to close (mobile)
    let startX = 0;
    sidebar.addEventListener('touchstart', e => startX = e.touches[0].clientX, {passive: true});
    sidebar.addEventListener('touchend', e => {
        const endX = e.changedTouches[0].clientX;
        if (startX - endX > 70 && sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    }, {passive: true});
});
</script>