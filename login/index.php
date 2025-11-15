<?php
session_start();
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
    <div class="left">
        <h1>Selamat Datang,</h1>
    </div>

    <div class="right">
        <form action="cek_login.php" method="POST" class="login-box">
            <img src="../../assets/logo.png" alt="Logo OrtuConnect" class="logo-img">

            <input type="text" name="username" placeholder="Username" required>

           <input type="password" name="password" id="password" placeholder="Password" required>

            <button type="submit">Masuk</button>

            <?php
            if (isset($_GET['error'])) {
                echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
            }
            ?>
        </form>
    </div>
</div>

<script>
document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordField = document.getElementById("password");
    const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
    passwordField.setAttribute("type", type);
    this.classList.toggle("show");
});
</script>
</body>
</html>