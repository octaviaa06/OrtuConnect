<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil data dari session
$role_raw = $_SESSION['role'] ?? 'user';
$role = ucfirst($role_raw);
$username = $_SESSION['username'] ?? 'user';
$initial = strtoupper(substr($username, 0, 1));

// === LOGIKA PENENTUAN HALAMAN ASAL UNTUK CANCEL LOGOUT ===

// 1. Coba ambil 'from' dari URL halaman yang meng-include profil.php
$current_page_from = $_GET['from'] ?? '';

// 2. Tentukan URL Default jika 'from' tidak terdefinisi (misalnya dari Dashboard utama)
if (empty($current_page_from)) {
    if ($role_raw === 'guru') {
        $from_page_param = 'dashboard guru';
    } elseif ($role_raw === 'admin') {
        $from_page_param = 'dashboard admin';
    } else {
        $from_page_param = 'dashboard';
    }
} else {
    // Jika 'from' ada di URL, gunakan nilai tersebut.
    $from_page_param = $current_page_from;
}

// Bangun tautan Logout ke halaman KONFIRMASI (Perbaikan utama)
$logout_link = "../logout/konfirmasi_logout.php?from=" . urlencode($from_page_param);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profil User</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="profil.css">
</head>
<body>

    <div class="profile-container">
        <div class="user-profile" id="profileToggle">
            <div class="profile-avatar"><?= $initial ?></div>
            <span class="username-text d-none d-md-inline"><?= $role ?></span>
        </div>

        <div class="dropdown-card" id="profileCard">
            <div class="card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar"><?= $initial ?></div>
                    <div>
                        <div class="fw-semibold text-primary"><?= htmlspecialchars($role) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($username) ?></div>
                    </div>
                </div>
            </div>
            <hr class="my-0">
            <a href="<?= $logout_link ?>" class="logout-link d-flex align-items-center gap-2 text-decoration-none">
                <img src="../assets/keluar.png" width="18" alt="Logout">
                <span>Logout</span>
            </a>
            </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const btn = document.getElementById('profileToggle');
            const card = document.getElementById('profileCard');
            if (!btn || !card) return;

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                card.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!btn.contains(e.target) && !card.contains(e.target)) {
                    card.classList.remove('show');
                }
            });

            window.addEventListener('scroll', () => {
                card.classList.remove('show');
            });
        });
    </script>

</body>
</html>