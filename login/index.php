<?php
// ======== SESSION & ERROR HANDLING ========
session_name("SESS_LOGIN");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil error jika ada
$error_message = '';
if (!empty($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Jika sudah login, langsung redirect
if (!empty($_SESSION['role'])) {
    $role = $_SESSION['role'];
    $redirect = ($role === "admin")
        ? "../dashboard_admin/home_admin.php"
        : "../dashboard_guru/home_guru.php";
    header("Location: $redirect");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OrtuConnect</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <!-- Bagian Kiri -->
    <div class="left">
        <h1>Selamat Datang</h1>
    </div>

    <!-- Bagian Kanan -->
    <div class="right">
        <form action="cek_login.php" method="POST" class="login-box" id="loginForm">
            <img src="../assets/logo.png" alt="Logo OrtuConnect" class="logo-img">

            <!-- Username -->
            <input type="text" name="username" id="username" placeholder="Username" required
                   oninvalid="this.setCustomValidity('Username wajib diisi')"
                   oninput="this.setCustomValidity('')">

            <!-- Password -->
            <input type="password" name="password" id="password" placeholder="Password" required
                   oninvalid="this.setCustomValidity('Password wajib diisi')"
                   oninput="this.setCustomValidity('')">

            <!-- Tombol Masuk -->
            <button type="submit" id="submitBtn">Masuk</button>

            <!-- NOTIFIKASI ERROR (PAKAI JS BIAR SELALU MUNCUL) -->
            <div class="notification error-notification" id="errorNotification" style="display:none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <span id="errorText"></span>
            </div>
        </form>
    </div>
</div>

<script>
// Cegah spasi di awal username
document.getElementById("username").addEventListener("input", function () {
    this.value = this.value.replace(/^\s+/, "");
});

// Prevent double submit
document.getElementById("loginForm").addEventListener("submit", function () {
    document.getElementById("submitBtn").disabled = true;
    document.getElementById("submitBtn").innerHTML = "Memproses...";
});

// TAMPILKAN NOTIFIKASI ERROR DENGAN JAVASCRIPT (100% MUNCUL DI SEMUA HOSTING)
<?php if (!empty($error_message)): ?>
document.addEventListener("DOMContentLoaded", function () {
    const notif = document.getElementById("errorNotification");
    const text = document.getElementById("errorText");
    text.textContent = <?= json_encode($error_message) ?>;
    notif.style.display = "flex";

    // Auto hide setelah 6 detik
    setTimeout(() => {
        notif.style.animation = "slideOutUp 0.5s ease forwards";
        setTimeout(() => { notif.style.display = "none"; }, 500);
    }, 6000);

    // Focus ke username
    document.getElementById("username").focus();
});
<?php endif; ?>
</script>

</body>
</html>