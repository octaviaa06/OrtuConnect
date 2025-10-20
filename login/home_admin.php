<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #f8f9ff;
  padding: 20px;
}
h1 {
  color: #0056d2;
}
a {
  color: red;
  text-decoration: none;
  font-weight: bold;
}
</style>
</head>
<body>
<h1>Halo Admin <?php echo $_SESSION['username']; ?>!</h1>
<p>Selamat datang di Dashboard Admin.</p>
<a href="logout.php">Logout</a>
</body>
</html>
