<?php
include "koneksi.php";

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['role'])) {
    header("Location: index.php");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];

$sql = "SELECT * FROM akun WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if ($data) {
    if ($data['role'] == $role) {
        session_start();
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];

        if ($role == 'admin') {
            header("Location: home_admin.php");
        } else {
            header("Location: home_guru.php");
        }
    } else {
        header("Location: index.php?error=Role tidak sesuai!");
    }
} else {
    header("Location: index.php?error=Username atau Password salah!");
}
?>
