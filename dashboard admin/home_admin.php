<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin | OrtuConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="dashboard_admin.css">
</head>
<body>
  <div class="d-flex">
   <!-- SIDEBAR -->
<div id="sidebar" class="bg-white border-end vh-100 p-2 expanded">
  <div class="text-center mb-3">
    <img src="../assets/logo.png" alt="Logo" class="logo">
    <img src="../assets/slide.png" alt="Slide" class="slide-btn" onclick="toggleSidebar()">
  </div>

  <ul class="nav flex-column">
    <li class="nav-item" onclick="navigate('home_admin.php')">
      <a class="nav-link active"><img src="../assets/Dashboard.png" class="icon"> <span>Dashboard</span></a>
    </li>
    <li class="nav-item" onclick="navigate('data_guru.php')">
      <a class="nav-link"><img src="../assets/Data Guru.png" class="icon"> <span>Data Guru</span></a>
    </li>
    <li class="nav-item" onclick="navigate('data_siswa.php')">
      <a class="nav-link"><img src="../assets/Data Siswa.png" class="icon"> <span>Data Murid</span></a>
    </li>
    <li class="nav-item" onclick="navigate('absensi.php')">
      <a class="nav-link"><img src="../assets/absensi.png" class="icon"> <span>Absensi</span></a>
    </li>
    <li class="nav-item" onclick="navigate('perizinan.php')">
      <a class="nav-link"><img src="../assets/Perizinan.png" class="icon"> <span>Perizinan</span></a>
    </li>
    <li class="nav-item" onclick="navigate('kalender.php')">
      <a class="nav-link"><img src="../assets/Kalender.png" class="icon"> <span>Kalender</span></a>
    </li>
  </ul>
</div>


    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 main-content" style="background-image: url('../assets/background/Dashboard Admin.png'); background-size: cover;">
      <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="fw-bold text-primary">Dashboard</h4>
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center" style="width:35px; height:35px;">A</div>
            <span class="fw-semibold text-primary">Admin</span>
          </div>
        </div>

        <!-- REKAP -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="card text-center shadow-sm border-primary">
              <div class="card-body">
                <h6 class="text-primary">Jumlah Guru</h6>
                <h3>32</h3>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card text-center shadow-sm border-primary">
              <div class="card-body">
                <h6 class="text-primary">Jumlah Siswa</h6>
                <h3>240</h3>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card text-center shadow-sm border-primary">
              <div class="card-body">
                <h6 class="text-primary">Kosong</h6>
                <h3>-</h3>
              </div>
            </div>
          </div>
        </div>

        <!-- AKSES CEPAT -->
        <h5 class="text-primary mb-2">Akses Cepat</h5>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="card text-center border-primary shadow-sm py-3">
              <div class="card-body fw-semibold">Generate Akun Guru</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card text-center border-primary shadow-sm py-3">
              <div class="card-body fw-semibold">Generate Akun Siswa</div>
            </div>
          </div>
        </div>

        <!-- IZIN & AGENDA -->
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-primary shadow-sm">
              <div class="card-body">
                <h6 class="text-primary">Izin Menunggu</h6>
                <div class="border-top pt-2 mt-2">
                  <p><strong>Bryan Mbouemo</strong> - Sakit Panas</p>
                  <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm">Setujui</button>
                    <button class="btn btn-danger btn-sm">Tolak</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-primary shadow-sm">
              <div class="card-body">
                <h6 class="text-primary">Agenda Terdekat</h6>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item">Rapat Guru - Jumat, 1 Nov</li>
                  <li class="list-group-item">Upacara Bendera - Senin</li>
                  <li class="list-group-item">Ujian Tengah Semester</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    function navigate(page) {
      window.location.href = page;
    }
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("collapsed");
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
