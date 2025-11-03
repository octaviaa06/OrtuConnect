<?php
session_start();


if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.php");
    exit;
}

if (isset($_POST['cancel_logout'])) {
    header("Location: ../dashboard_admin/home_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konfirmasi Logout</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card shadow p-4 text-center" style="width: 350px;">
    <h5 class="mb-3 text-primary fw-bold">Konfirmasi Logout</h5>
    <p>Apakah Anda yakin ingin keluar dari akun ini?</p>
    <form method="POST">
      <button type="submit" name="confirm_logout" class="btn btn-danger w-100 mb-2">Ya, Logout</button>
      <button type="submit" name="cancel_logout" class="btn btn-secondary w-100">Batal</button>
    </form>
  </div>
</body>
</html>
