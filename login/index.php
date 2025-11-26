<?php
session_start();

$error_message = '';

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']); // Hapus error setelah ditampilkan
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OrtuConnect</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <!-- Bagian Kiri -->
    <div class="left">
        <h1>Selamat Datang</h1>
    </div>

    <!-- Bagian Kanan -->
    <div class="right">
        <form action="cek_login.php" method="POST" class="login-box">

            <img src="../assets/logo.png" alt="Logo OrtuConnect" class="logo-img">

            <!-- Input Username -->
            <input 
                type="text"
                name="username"
                id="username"
                placeholder="Username"
                required
                oninvalid="this.setCustomValidity('Wajib diisi terlebih dahulu')"
                oninput="this.setCustomValidity('')"
            >

            <!-- Input Password -->
            <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required
                oninvalid="this.setCustomValidity('Wajib diisi terlebih dahulu')"
                oninput="this.setCustomValidity('')"
            >

            <!-- Tombol Masuk -->
            <button type="submit">Masuk</button>

            <!-- Notifikasi Error -->
            <?php if ($error_message): ?>
                <div class="notification error-notification">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

        </form>
    </div>
</div>

<script>
// Cegah spasi di awal username
document.getElementById("username").addEventListener("input", function () {
    this.value = this.value.replace(/^\s+/, "");
});
</script>

</body>
</html>
