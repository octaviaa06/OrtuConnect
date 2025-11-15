<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil data dari session
$role = ucfirst($_SESSION['role'] ?? 'user');
$username = $_SESSION['username'] ?? 'user';
$initial = strtoupper(substr($username, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profil User</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- CSS Terpisah -->
  <link rel="stylesheet" href="profil.css">
</head>
<body>

  <!-- WRAPPER CONTAINER untuk positioning -->
  <div class="profile-container">
    <!-- USER PROFILE BUTTON -->
    <div class="user-profile" id="profileToggle">
      <div class="profile-avatar"><?= $initial ?></div>
      <span class="username-text d-none d-md-inline"><?= $role ?></span>
    </div>

    <!-- DROPDOWN CARD -->
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
      <a href="../logout/logout.php" class="logout-link d-flex align-items-center gap-2 text-decoration-none">
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