<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Siswa - Guru</title>
  <link rel="stylesheet" href="data_siswa.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

  <!-<!-- ===== SIDEBAR ===== -->
<div id="sidebar" class="sidebar bg-primary text-white p-3 expanded">
  <div class="text-center mb-4">
    <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn">
  </div>

  <ul class="nav flex-column">
    <li class="nav-item">
      <a href="home_guru.php" class="nav-link">
        <img src="../assets/Dashboard.png" class="icon">
        <span>Dashboard</span>
      </a>
    </li>

    <li class="nav-item">
      <a href="../guru data siswa/data_siswa.php" class="nav-link active">
        <img src="../assets/Data Siswa.png" class="icon">
        <span>Data Siswa</span>
      </a>
    </li>

    <li class="nav-item">
      <a href="../guru absensi/absensi.php" class="nav-link">
        <img src="../assets/absensi.png" class="icon">
        <span>Absensi</span>
      </a>
    </li>

    <li class="nav-item">
      <a href="../guru perizinan/perizinan.php" class="nav-link">
        <img src="../assets/Perizinan.png" class="icon">
        <span>Perizinan</span>
      </a>
    </li>

    <li class="nav-item">
      <a href="../guru kalender/kalender.php" class="nav-link">
        <img src="../assets/Kalender.png" class="icon">
        <span>Kalender</span>
      </a>
    </li>
  </ul>
</div>


  <!-- ===== MAIN CONTENT ===== -->
  <div class="main-content">
    <div class="header-fixed">
      <h2 style="color:#0d6efd; font-weight:600;">Data Siswa</h2>

      <div class="profile-btn" id="profileBtn">
        <div class="profile-avatar">G</div>
        <div class="profile-card" id="profileCard">
          <p style="margin-bottom:10px; font-weight:600;">Guru</p>
          <a href="../login/logout.php" class="logout-btn">
            <img src="../assets/logout.png" alt="Logout">
            <span>Logout</span>
          </a>
        </div>
      </div>
    </div>

    <hr style="border:1px solid #dbe1f0; margin:20px 0;">

    <!-- SEARCH BAR -->
    <div style="display:flex; align-items:center; background:white; border-radius:12px; border:1px solid #cdd6ec; padding:10px 18px; max-width:600px;">
      <img src="../assets/search.png" style="width:22px; margin-right:10px;">
      <input type="text" placeholder="Cari data siswa..." style="flex:1; border:none; outline:none; font-size:15px;">
    </div>

    <!-- CARD DATA SISWA -->
    <div style="display:flex; gap:25px; margin-top:35px; flex-wrap:wrap;">
      <div class="dashboard-card" style="width:300px; height:210px;"></div>
      <div class="dashboard-card" style="width:300px; height:210px;"></div>
      <div class="dashboard-card" style="width:300px; height:210px;"></div>
    </div>
  </div>

  <!-- ===== SCRIPT ===== -->
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleSidebar = document.getElementById('toggleSidebar');
    const profileBtn = document.getElementById('profileBtn');
    const profileCard = document.getElementById('profileCard');

    toggleSidebar.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });

    profileBtn.addEventListener('click', () => {
      profileCard.classList.toggle('show');
    });

    // Klik di luar profil untuk menutup popup
    document.addEventListener('click', function(e) {
      if (!profileBtn.contains(e.target)) {
        profileCard.classList.remove('show');
      }
    });
  </script>

</body>
</html>
