<?php
ob_start();
session_start();

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

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if ($username === '' || $password === '') {
    $_SESSION['error'] = "Wajib memasukkan username dan password";
    header("Location: index.php");
    exit;
}

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

if ($response === false || $http_code !== 200) {
    $_SESSION['error'] = "Gagal koneksi ke server";
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
    $_SESSION['error'] = "Data user tidak lengkap dari server";
    header("Location: index.php");
    exit;
}

$role = $user['role'];

session_write_close();

$session_name = "SESS_" . strtoupper($role);
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
