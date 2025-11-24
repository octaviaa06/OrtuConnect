<?php
session_start();
ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request";
    header("Location: index.php");
    exit;
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    $_SESSION['error'] = "Wajib memasukkan username dan password";
    header("Location: index.php");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// 1. Cek kosong
if (trim($username) === '' || trim($password) === '') {
    $_SESSION['error'] = "Wajib memasukkan username dan password";
    header("Location: index.php");
    exit;
}

// 2. Cegah spasi diawal/akhir
if ($username !== trim($username) || $password !== trim($password)) {
    $_SESSION['error'] = "Username atau password tidak boleh ada spasi diawal/akhir";
    header("Location: index.php");
    exit;
}

$username = trim($username);
$password = trim($password);

// === API LOGIN ===
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/login.php";
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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Cek API error
if ($response === false || $http_code !== 200) {
    $_SESSION['error'] = "Gagal koneksi ke server: $error";
    header("Location: index.php");
    exit;
}

$result = json_decode($response, true);

if (!$result || !isset($result['success']) || $result['success'] !== true) {
    $_SESSION['error'] = $result['message'] ?? "Username atau password salah";
    header("Location: index.php");
    exit;
}

$user = $result['user'] ?? null;
if (!$user || !isset($user['role']) || !isset($user['id_akun'])) {
    $_SESSION['error'] = "Data user tidak lengkap dari API";
    header("Location: index.php");
    exit;
}

$role = $user['role'];

// ===== SESSION LOGIN BERDASARKAN ROLE =====
$session_name = 'SESS_' . strtoupper($role);
session_name($session_name);
session_start();
session_regenerate_id(true);

$_SESSION['id_akun'] = $user['id_akun'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $role;
$_SESSION['login_time'] = time();

$redirect = ($role === 'admin') 
    ? '../dashboard_admin/home_admin.php'
    : '../dashboard_guru/home_guru.php';

header("Location: $redirect");
exit;
?>
