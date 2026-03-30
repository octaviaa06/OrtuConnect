<?php
session_start();

/* =========================
   ANTI BACK & CACHE
========================= */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

/* =========================
   JIKA BELUM LOGIN
========================= */
if (!isset($_SESSION['login'])) {
    header("Location: ../login/index.php");
    exit;
}

$from_page = $_POST['from'] ?? $_GET['from'] ?? '';

/* =========================
   PROSES LOGOUT
========================= */
if (isset($_POST['confirm_logout'])) {

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    // Replace history supaya tidak bisa back
    echo "<script>
        window.location.replace('../login/index.php');
    </script>";
    exit;
}

/* =========================
   BATAL LOGOUT
========================= */
if (isset($_POST['cancel_logout'])) {

    $redirect_map = [
        // ADMIN
        'dashboard admin' => '../dashboard_admin/home_admin.php',
        'DataGuru'        => '../admin data guru/DataGuru.php',
        'data'            => '../admin data siswa/DataSiswa.php',
        'absensi'         => '../admin absensi/Absensi.php',
        'perizinan_admin'       => '../admin perizinan/Perizinan.php',
        'kalender'        => '../admin kalender/Kalender.php',

        // GURU
        'dashboard guru'  => '../dashboard_guru/home_guru.php',
        'data_siswa'      => '../guru data siswa/data_siswa.php',
        'absensi_siswa'   => '../guru absensi/absensi_siswa.php',
        'perizinan_siswa' => '../guru perizinan/perizinan.php',
        'kalender guru'   => '../guru kalender/kalender.php',
    ];

    // Default berdasarkan role
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'guru') {
        $default = '../dashboard_guru/home_guru.php';
    } else {
        $default = '../dashboard_admin/home_admin.php';
    }

    $target = $redirect_map[$from_page] ?? $default;
    header("Location: $target");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar</title>

    <!-- CSS ASLI -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{
            background:linear-gradient(135deg,#667eea,#764ba2);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0
        }
        .card-logout{
            max-width:380px;
            border:none;
            border-radius:24px;
            background:white;
            box-shadow:0 20px 50px rgba(0,0,0,.2);
            overflow:hidden
        }
        .icon-logout{
            width:90px;
            height:90px;
            background:linear-gradient(135deg,#4361ee,#3f37c9);
            color:white;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:-50px auto 20px;
            box-shadow:0 10px 30px rgba(67,97,238,.4)
        }
        .btn-keluar,.btn-batal{
            height:56px;
            font-weight:600;
            font-size:1.05rem;
            border-radius:16px;
            transition:all .3s
        }
        .btn-keluar{
            background:linear-gradient(135deg,#4361ee,#3f37c9);
            border:none;
            box-shadow:0 8px 25px rgba(67,97,238,.4)
        }
        .btn-keluar:hover{
            transform:translateY(-4px);
            box-shadow:0 15px 35px rgba(67,97,238,.5)
        }
        .btn-batal{
            background:#f8f9ff;
            color:#4361ee;
            border:2px solid #e0e6ff
        }
        .btn-batal:hover{
            background:#e0e6ff;
            border-color:#4361ee;
            transform:translateY(-3px)
        }
    </style>
</head>
<body>

<div class="card-logout text-center pt-5 pb-4 px-4">
    <div class="icon-logout">
        <i class="bi bi-box-arrow-right" style="font-size:2.2rem"></i>
    </div>

    <h4 class="mt-4 mb-2 fw-bold text-dark">Yakin ingin keluar?</h4>
    <p class="text-muted mb-5 px-3" style="font-size:0.95rem">
        Sesi akan berakhir dan Anda akan dialihkan ke halaman login.
    </p>

    <form method="POST" class="d-grid gap-3 px-4">
        <input type="hidden" name="from" value="<?= htmlspecialchars($from_page) ?>">

        <button type="submit" name="confirm_logout" class="btn btn-keluar text-white">
            Ya, Keluar Sekarang
        </button>

        <button type="submit" name="cancel_logout" class="btn btn-batal">
            Batal, Kembali
        </button>
    </form>
</div>

</body>
</html>
