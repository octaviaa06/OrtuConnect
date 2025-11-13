<?php
session_start();

// Ambil parameter asal halaman
$from_page = $_GET['from'] ?? ($_POST['from'] ?? 'dashboard');

// Jika klik "Ya, Logout"
if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.php");
    exit;
}

// Jika klik "Batal"
if (isset($_POST['cancel_logout'])) {
    switch ($from_page) {

       case 'dashboard admin':
            header("Location: ../dashboard_admin/home_admin.php");
            break;
        case 'dataguru':
            header("Location: ../admin data guru/DataGuru.php");
            break;
        case 'datasiswa':
            header("Location: ../admin data siswa/DataSiswa.php");
            break;
        case 'absensi':
            header("Location: ../admin absensi/Absensi.php");
            break;
        case 'perizinan':
            header("Location: ../admin perizinan/Perizinan.php");
            break;
        case 'kalender':
            header("Location: ../admin kalender/Kalender.php");
            break;

 case 'absensi guru':
            header("Location: ../guru absensi/absensi_siswa.php");
            break;
case 'kalender guru':
            header("Location: ../guru kalender/kalender.php");
            break;

              default:
            header("Location: ../dashboard_admin/home_admin.php");
            break;
    }
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
      <input type="hidden" name="from" value="<?= htmlspecialchars($from_page) ?>">
      <button type="submit" name="confirm_logout" class="btn btn-danger w-100 mb-2">Ya, Logout</button>
      <button type="submit" name="cancel_logout" class="btn btn-secondary w-100">Batal</button>
    </form>
  </div>
</body>
</html>
