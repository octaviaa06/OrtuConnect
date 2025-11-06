<?php

session_start();

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: index.php?error=Data login tidak lengkap!");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password']; 
 
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

$response = curl_exec($ch);
curl_close($ch);


$result = json_decode($response, true);


if ($result && isset($result['success']) && $result['success'] === true) {


    $user = $result['user']; 


    $_SESSION['id_akun']    = $user['id_akun'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    if ($_SESSION['role'] === 'admin') {

 
        header("Location: ../dashboard_admin/home_admin.php");


    } elseif ($_SESSION['role'] === 'guru') {

        header("Location: ../dashboard_guru/home_guru.php");
    } else {
        
        header("Location: index.php?error=Role tidak dikenali!");
    }

    exit;

} else {
    
    $error_msg = isset($result['message']) ? $result['message'] : "Login gagal, periksa koneksi API!";
 
    header("Location: index.php?error=" . urlencode($error_msg));
    exit;
}
?>
