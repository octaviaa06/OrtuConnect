<?php
session_start();

function redirectError($msg) {
    $_SESSION['error'] = $msg;
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectError("Akses tidak diizinkan");
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['error'] = "Username dan password wajib diisi";
    header("Location: index.php");
    exit;
}

// ===== API LOGIN =====
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/login.php";
$payload = json_encode([
    "username" => $username,
    "password" => $password
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    $_SESSION['error'] = "Koneksi ke server gagal";
    header("Location: index.php");
    exit;
}

$result = json_decode($response, true);

if (!$result || empty($result['success'])) {
    // Menampilkan pesan error yang dikembalikan dari API
    $error_message = $result['message'] ?? "Username atau password salah";
    $_SESSION['error'] = $error_message;
    header("Location: index.php");
    exit;
}

$user = $result['user'] ?? [];
if (empty($user['role'])) {
    $_SESSION['error'] = "Data akun tidak valid";
    header("Location: index.php");
    exit;
}

// ===== LOGIN SUKSES =====
$_SESSION['login'] = true;
$_SESSION['id_akun'] = $user['id_akun'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['login_time'] = time();

// khusus guru
if ($_SESSION['role'] === 'guru' && !empty($user['kelas'])) {
    $_SESSION['kelas'] = $user['kelas'];
}

$redirect = ($_SESSION['role'] === 'admin')
    ? "../dashboard_admin/home_admin.php"
    : "../dashboard_guru/home_guru.php";

header("Location: $redirect");
exit;
?>