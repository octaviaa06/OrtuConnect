<?php
ob_start(); 
session_start(); 

function handleErrorRedirect($msg) {
    $_SESSION['error'] = $msg;
    header("Location: index.php");
    exit;
}

// 1. Validasi Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleErrorRedirect("Invalid request");
}

// 2. Validasi Input Ada
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    handleErrorRedirect("Data tidak lengkap");
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// 3. Validasi Input Kosong
if ($username === '' || $password === '') {
    handleErrorRedirect("Username atau password kosong"); 
}

// 4. Persiapan cURL
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
$curl_error = curl_error($ch);
curl_close($ch); // Gunakan curl_close()

// 5. Cek Koneksi API
if ($response === false || $http_code !== 200) {
$msg = "Gagal koneksi server. " . ($curl_error ? $curl_error : "Kode: $http_code");
    handleErrorRedirect($msg);
}

$result = json_decode($response, true);

// 6. Cek Response API (Sukses/Gagal Login)
if (!$result || !isset($result['success']) || $result['success'] !== true) {
    $error_msg = $result['message'] ?? "Username atau password salah";
    handleErrorRedirect($error_msg);
}

$user = $result['user'] ?? null;
if (!$user || !isset($user['role']) || !isset($user['id_akun'])) {
    handleErrorRedirect("Data user tidak lengkap dari API");
}

// 7. Setup Session
$role = $user['role']; 
$session_name = 'SESS_' . strtoupper($role);
session_name($session_name);
// Panggil session_start() lagi untuk memastikan session_name baru diterapkan
session_start(); 
session_regenerate_id(true);

// 8. Cek jika user sudah login sebelumnya (opsional, untuk redirect cepat)
if (isset($_SESSION['role']) && $_SESSION['role'] === $role && isset($_SESSION['username']) && $_SESSION['username'] === $user['username']) {
$redirect = $role === 'admin' 
? '../dashboard_admin/home_admin.php' 
: '../dashboard_guru/home_guru.php';
header("Location: $redirect");
exit;
}

// 9. Simpan Data Baru ke Session
$_SESSION['id_akun'] = $user['id_akun'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']  = $user['role'];
$_SESSION['login_time'] = time(); 

// 10. Redirect sesuai Role
$redirect = $role === 'admin' 
? '../dashboard_admin/home_admin.php' 
 : '../dashboard_guru/home_guru.php';

if (!file_exists($redirect)) {
    handleErrorRedirect("Halaman tujuan tidak ditemukan: " . basename($redirect));
}

header("Location: $redirect");
exit;
?>