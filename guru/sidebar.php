<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<!-- Hamburger Button untuk Mobile/Tablet -->
<button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- Overlay untuk Mobile/Tablet -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<div id="sidebar" class="sidebar expanded">
    <!-- Sidebar Header -->
    <div class="sidebar-header text-center">
        <img src="../assets/Slide.png" id="toggleSidebar" alt="Collapse" class="slide-btn" onclick="toggleSidebar()">
       
    </div>

    <!-- Navigation Menu -->
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a href="../dashboard_guru/home_guru.php" class="nav-link">
                <img src="../assets/Dashboard.png" class="icon" alt="Dashboard">
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../guru data siswa/data_siswa.php" class="nav-link">
                <img src="../assets/Data_Siswa.png" class="icon" alt="Data Siswa">
                <span class="menu-text">Data Murid</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../guru absensi/absensi_siswa.php" class="nav-link">
                <img src="../assets/absensi.png" class="icon" alt="Absensi">
                <span class="menu-text">Absensi</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../guru perizinan/perizinan.php" class="nav-link">
                <img src="../assets/Perizinan.png" class="icon" alt="Perizinan">
                <span class="menu-text">Perizinan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../guru kalender/kalender.php" class="nav-link">
                <img src="../assets/Kalender.png" class="icon" alt="Kalender">
                <span class="menu-text">Kalender</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
       
    </div>
</div>

<script>
/**
 * Toggle Sidebar Function
 * - Desktop: Collapse/Expand
 * - Mobile/Tablet: Slide In/Out dengan Overlay
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const hamburger = document.getElementById('hamburgerBtn');

    // Mobile/Tablet Mode (< 992px)
    if (window.innerWidth < 992) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        hamburger.classList.toggle('active');
        
        // Prevent body scroll saat sidebar terbuka
        if (sidebar.classList.contains('show')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    } 
    // Desktop Mode (>= 992px)
    else {
        sidebar.classList.toggle('collapsed');
        sidebar.classList.toggle('expanded');
    }
}

/**
 * Initialize Sidebar on Page Load
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-close sidebar saat klik menu (Mobile/Tablet only)
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                const hamburger = document.getElementById('hamburgerBtn');
                
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Highlight active menu berdasarkan current URL
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split('/').pop();
    
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage) {
            link.classList.add('active');
        }
    });

    // Ensure sidebar state on page load
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth >= 992) {
        sidebar.classList.add('expanded');
        sidebar.classList.remove('show');
    }
});

/**
 * Handle Window Resize
 * Reset sidebar state saat resize window
 */
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const hamburger = document.getElementById('hamburgerBtn');
        
        // Reset ke Desktop mode jika window >= 992px
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            hamburger.classList.remove('active');
            document.body.style.overflow = '';
            
            // Ensure expanded state di desktop
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('expanded');
            }
        } else {
            // Remove desktop classes di mobile
            sidebar.classList.remove('collapsed', 'expanded');
        }
    }, 250);
});

/**
 * Close Sidebar dengan ESC Key (Mobile/Tablet)
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    }
});

/**
 * Prevent Scroll Chain pada Sidebar (Mobile Touch)
 */
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.addEventListener('touchmove', function(e) {
            e.stopPropagation();
        }, { passive: true });
    }
});

/**
 * Handle Swipe Gesture untuk Close Sidebar (Mobile)
 */
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    let touchStartX = 0;
    let touchEndX = 0;

    sidebar.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    sidebar.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        // Swipe left untuk close sidebar
        if (touchStartX - touchEndX > 50 && sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    }
});
</script>