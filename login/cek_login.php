<?php
session_start();

// Pastikan data dikirim lengkap
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: index.php?error=Data login tidak lengkap!");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// 🔹 Ganti dengan URL endpoint API login kamu
$api_url = "http://ortuconnect.atwebpages.com/api/login.php"; // ubah sesuai API kamu

// Siapkan data login untuk dikirim ke API
$data = [
    "username" => $username,
    "password" => $password
];

// Kirim request ke API dengan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
curl_close($ch);

// Decode hasil JSON dari API
$result = json_decode($response, true);

// Cek hasil dari API
if ($result && isset($result['success']) && $result['success'] === true) {

    // Ambil data user dari respons API
    $user = $result['user']; // sesuai dengan struktur JSON dari API kamu

    // Simpan data user ke session
    $_SESSION['id_akun']  = $user['id_akun'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    // Arahkan user berdasarkan role
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin/admin.php");
    } elseif ($_SESSION['role'] === 'guru') {
        header("Location: home_guru.php");
    } else {
        // Jika role tidak dikenal, arahkan kembali ke login
        header("Location: index.php?error=Role tidak dikenali!");
    }

    exit;

} else {
    // Jika gagal login, tampilkan pesan error dari API
    $error_msg = isset($result['message']) ? $result['message'] : "Login gagal, periksa koneksi API!";
    header("Location: index.php?error=" . urlencode($error_msg));
    exit;
}
?>
