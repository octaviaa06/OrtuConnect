<?php
// security.php atau includes/security.php
session_start();

/**
 * Untuk halaman yang HARUS login (dashboard, profile, dll)
 */
function requireLogin() {
    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        // Destroy session dulu untuk bersih-bersih
        session_unset();
        session_destroy();
        
        // Redirect ke login dengan no-cache
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Location: login.php?error=session_expired&t=" . time());
        exit();
    }
    
    // Set headers untuk mencegah caching
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}

/**
 * Untuk halaman yang TIDAK BOLEH diakses jika sudah login (login, register)
 */
function requireLogout() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // Jika sudah login, redirect ke dashboard
        header("Location: dashboard.php");
        exit();
    }
    
    // Set headers anti-cache
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}

/**
 * Fungsi logout yang proper
 */
function doLogout() {
    // Hapus semua session
    session_unset();
    session_destroy();
    session_write_close();
    
    // Hapus session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Set headers no-cache
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Redirect ke login dengan timestamp
    header("Location: login.php?msg=logged_out&t=" . time());
    exit();
}
?>