<?php
// Tambahkan kode ini di baris teratas untuk mendiagnosis
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- Akhir kode diagnosis ---


session_start();

// Ambil halaman asal dengan aman
$from_page = $_GET['from'] ?? ($_POST['from'] ?? '');

// Tentukan Role Dasar 
$role = 'admin'; 
// Cek dari session role sebagai fallback terkuat jika from_page kosong
if (isset($_SESSION['role']) && $_SESSION['role'] === 'guru') {
    $role = 'guru';
}
// Cek dari from_page jika from_page dikirimkan
if (!empty($from_page)) {
    if (strpos($from_page, 'guru') !== false || 
        $from_page == 'data_siswa' || 
        $from_page == 'absensi guru' || 
        $from_page == 'perizinan ' || 
        $from_page == 'kalender guru') {
        $role = 'guru';
    }
}


// Proses konfirmasi/batal logout
if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.php");
    exit;
}

if (isset($_POST['cancel_logout'])) {
    $from = htmlspecialchars($from_page, ENT_QUOTES); 
    $url = '';

    switch ($from) {
        // --- ADMIN CASES ---
        case 'dashboard admin':
            $url = '../dashboard_admin/home_admin.php';
            break;
        // ... (Tambahkan semua kasus ADMIN lainnya di sini) ...
        case 'dataguru': 
            $url = '../admin data guru/DataGuru.php';
            break;
        case 'datasiswa': 
            $url = '../admin data siswa/DataSiswa.php';
            break;
        case 'absensi': 
            $url = '../admin absensi/Absensi.php';
            break;
        case 'perizinan': 
            $url = '../admin perizinan/Perizinan.php';
            break;
        case 'kalender': 
            $url = '../admin kalender/Kalender.php';
            break;

        // --- GURU CASES ---
        case 'dashboard guru':
            $url = '../dashboard_guru/home_guru.php';
            break;
        // ... (Tambahkan semua kasus GURU lainnya di sini) ...
        case 'data_siswa': 
            $url = '../guru data siswa/data_siswa.php';
            break;
        case 'absensi guru': 
            $url = '../guru absensi/absensi_siswa.php';
            break;
        case 'perizinan siswa': 
            $url = '../guru perizinan/perizinan.php';
            break;
        case 'kalender guru': 
            $url = '../guru kalender/kalender.php';
            break;
            
        // --- DEFAULT CASE ---
        default:
            if ($role === 'guru') {
                $url = '../dashboard_guru/home_guru.php';
            } else {
                $url = '../dashboard_admin/home_admin.php';
            }
            break;
    }

    if (!empty($url)) {
        header("Location: $url");
        exit;
    }
    header("Location: ../login/index.php");
    exit;
}

// Jika tidak ada tombol yang diklik (Konfirmasi atau Batal), tampilkan halaman konfirmasi
// Variabel $from_page sudah tersedia di sini untuk dilewatkan ke tampilan
include 'logout.php'; 

?>