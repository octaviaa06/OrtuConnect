<?php
session_start();

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: index.php?error=Data login tidak lengkap!");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];
$role     = isset($_POST['role']) ? $_POST['role'] : ''; // opsional kalau kamu pakai role

// 🔹 Ganti dengan URL endpoint API login kamu
$api_url = "http://ortuconnect.atwebpages.com/api/login.php"; // <-- ubah sesuai alamat API kamu

// Siapkan data yang dikirim ke API
$data = [
    "username" => $username,
    "password" => $password
];

// Kirim request ke API menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
curl_close($ch);

// Decode JSON dari API
$result = json_decode($response, true);

// Cek apakah login berhasil
if ($result && isset($result['success']) && $result['success'] === true) {
    $user = $result['data'];

    // Simpan sesi login
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = isset($user['role']) ? $user['role'] : $role;

    // Redirect sesuai role (kalau ada)
    if ($_SESSION['role'] === 'admin') {
        header("Location: home_admin.php");
    } else {
        header("Location: home_guru.php");
    }
    exit;
} else {
    // Tampilkan pesan error dari API (kalau ada)
    $error_msg = isset($result['message']) ? $result['message'] : "Login gagal, periksa koneksi API!";
    header("Location: index.php?error=" . urlencode($error_msg));
    exit;
}
?>
