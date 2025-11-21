<?php
ob_start(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=Invalid request");
    exit;
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: index.php?error=Data tidak lengkap");
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

if ($username === '' || $password === '') {
    header("Location: index.php?error=Username atau password kosong");
    exit;
}

$api_url = "http://ortuconnect.atwebpages.com/api/login.php";
$data = [
    "username" => $username,
    "password" => $password
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Jika SSL error
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout 10 detik

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ch = null;

if ($response === false || $http_code !== 200) {
 
    header("Location: index.php?error=" . urlencode("Gagal koneksi ke server: $error"));
    exit;
}

$result = json_decode($response, true);

if (!$result || !isset($result['success']) || $result['success'] !== true) {
    $error_msg = $result['message'] ?? "Username atau password salah";
    header("Location: index.php?error=" . urlencode($error_msg));
    exit;
}

$user = $result['user'] ?? null;
if (!$user || !isset($user['role']) || !isset($user['id_akun'])) {
    header("Location: index.php?error=Data user tidak lengkap dari API");
    exit;
}

$role = $user['role']; 

$session_name = 'SESS_' . strtoupper($role);
session_name($session_name);
session_start();

session_regenerate_id(true);

if (isset($_SESSION['role']) && $_SESSION['role'] === $role && $_SESSION['username'] === $user['username']) {
    $redirect = $role === 'admin' 
        ? '../dashboard_admin/home_admin.php' 
        : '../dashboard_guru/home_guru.php';
    header("Location: $redirect");
    exit;
}

$_SESSION['id_akun'] = $user['id_akun'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];
$_SESSION['login_time'] = time(); 

$redirect = $role === 'admin' 
    ? '../dashboard_admin/home_admin.php' 
    : '../dashboard_guru/home_guru.php';

if (!file_exists($redirect)) {
    header("Location: index.php?error=Halaman tidak ditemukan: " . basename($redirect));
    exit;
}

header("Location: $redirect");
exit;
?>