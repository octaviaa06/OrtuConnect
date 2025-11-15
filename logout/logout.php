<?php
session_start();

// Ambil halaman asal dengan aman
$from_page = $_GET['from'] ?? ($_POST['from'] ?? 'dashboard admin');
$from_page = $_GET['from'] ?? ($_POST['from'] ?? 'dashboard guru');

// Proses konfirmasi logout
if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login/index.php");
    exit;
}

// Proses batal logout
if (isset($_POST['cancel_logout'])) {
    $from = htmlspecialchars($from_page, ENT_QUOTES); // Aman dari XSS

    switch ($from) {
        case 'dashboard admin':
            $url = '../dashboard_admin/home_admin.php';
            break;
        case 'dataguru':
            $url = '../admin data guru/DataGuru.php';
            break;
        case 'datasiswa':
            $url = '../admin data siswa/DataSiswa.php';
            break;
        case 'absensi':
            $url = '../admin absensi/Absensi.php';
            break;
        case 'perizinan':
            $url = '../admin perizinan/Perizinan.php';
            break;
        case 'kalender':
            $url = '../admin kalender/Kalender.php';
            break;
        case 'data_siswa':
            $url = '../guru data siswa/data_siswa.php';
            break;
        case 'absensi guru':
            $url = '../guru absensi/absensi_siswa.php';
            break;
        case 'perizinan siswa':
            $url = '../guru perizinan/perizinan.php';
            break;
        case 'kalender guru':
            $url = '../guru kalender/kalender.php';
            break;
        case 'dashboard guru':
            $url = '../dashboard_guru/home_guru.php';
            break;
        default:
            $url = '../dashboard_admin/home_admin.php';
            break;
    }

    header("Location: $url");
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
