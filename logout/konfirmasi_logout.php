<?php
session_start();
$from_page = $_POST['from'] ?? $_GET['from'] ?? '';

// Tentukan role (bisa dari session atau dari $from_page)
$role = $_SESSION['role'] ?? 'admin'; // default admin kalau belum login

// Jika $from_page mengandung kata kunci guru, paksa role guru
if (strpos($from_page, 'guru') !== false || 
    in_array($from_page, ['data_siswa', 'absensi guru', 'perizinan siswa', 'kalender guru'])) {
    $role = 'guru';
}

// ==================== PROSES LOGOUT CONFIRM ====================
if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.php");
    exit;
}

// ==================== PROSES BATAL LOGOUT ====================
if (isset($_POST['cancel_logout'])) {
    
    // Mapping halaman asal → URL tujuan (EXACT MATCH + FALLBACK)
    $redirect_map = [
        // ADMIN
        'dashboard admin'   => '../dashboard_admin/home_admin.php',
        'DataGuru'          => '../admin data guru/DataGuru.php',
        'data'         => '../admin data siswa/DataSiswa.php',
        'absensi'           => '../admin absensi/Absensi.php',
        'Perizinan'         => '../admin perizinan/Perizinan.php',
        'kalender'          => '../admin kalender/Kalender.php',

        // GURU
        'dashboard guru'    => '../dashboard_guru/home_guru.php',
        'data_siswa'        => '../guru data siswa/data_siswa.php',
        'absensi_siswa'      => '../guru absensi/absensi_siswa.php',
        'perizinan'   => '../guru perizinan/perizinan.php',
        'kalender guru'     => '../guru kalender/kalender.php',
    ];

    $target = $redirect_map[$from_page] ?? null;

    // Jika tidak ada di map, coba fallback berdasarkan role
    if (!$target) {
        $target = $role === 'guru' 
            ? '../dashboard_guru/home_guru.php' 
            : '../dashboard_admin/home_admin.php';
    }

    header("Location: $target");
    exit;
}

// Jika belum ada aksi (tampilkan konfirmasi logout)
// Pastikan $from_page dikirim ke logout.php
include 'logout.php';
?>