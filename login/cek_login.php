<?php
session_name("SESS_LOGIN");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    redirectError("Username dan password wajib diisi");
}

$api_url = "https://ortuconnect.pbltifnganjuk.com/api/login.php";
$payload = json_encode(["username" => $username, "password" => $password]);

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
    redirectError("Koneksi ke server gagal. Coba lagi nanti.");
}

$result = json_decode($response, true);

if (!$result || !isset($result['success']) || $result['success'] !== true) {
    $msg = $result['message'] ?? "Username atau password salah";
    redirectError($msg);
}

$user = $result['user'] ?? [];
if (empty($user['role'])) {
    redirectError("Data akun tidak valid");
}

// Login sukses → buat session sesuai role
$role = $user['role'];
$new_session_name = "SESS_" . strtoupper($role);
session_write_close();
session_name($new_session_name);
session_start();

$_SESSION['id_akun'] = $user['id_akun'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $role;
$_SESSION['login_time'] = time();

$redirect = ($role === "admin")
    ? "../dashboard_admin/home_admin.php"
    : "../dashboard_guru/home_guru.php";

header("Location: $redirect");
exit;
?>