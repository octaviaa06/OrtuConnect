<?php
session_start();
$active_page = 'perizinan';
include '../admin/sidebar.php';
// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Ambil data perizinan
$api_url = "http://ortuconnect.atwebpages.com/api/admin/absensi.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
if (curl_errno($ch)) $response = json_encode(["data" => []]);
curl_close($ch);

$data = json_decode($response, true);
$perizinanList = $data['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Perizinan | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Perizinan.css" /> 
    <link rel="stylesheet" href="SidebarAnimation.css" /> 
</head>
<body>
    <div class="d-flex">
   <!-- Sidebar -->
<?php include '../admin/sidebar.php'; ?>


        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Perizinan</h4>
                    <div class="profile-btn" id="profileToggle">
                        <div class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <span class="fw-semibold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <div class="profile-card" id="profileCard">
                            <h6><?= ucfirst($_SESSION['role']) ?></h6>
                            <p><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
                            <hr />
                            <a href="../logout/logout.php?from=perizinan" class="logout-btn">
                                <img src="../assets/keluar.png" alt="Logout" /> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                    <h5 class="fw-bold mb-4">Daftar Perizinan Murid</h5>

                    <div class="d-flex justify-content-end mb-3">
                        <div class="search-container position-relative" style="max-width:400px;">
                            <img src="../assets/cari.png" alt="Cari" class="search-icon" />
                            <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari perizinan berdasarkan nama..." />
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="perizinanTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width:5%;">NO</th>
                                    <th style="width:20%;">Nama Murid</th>
                                    <th style="width:10%;">Kelas</th>
                                    <th style="width:15%;">Alasan</th>
                                    <th style="width:15%;">Tanggal</th>
                                    <th style="width:35%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($perizinanList)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Tidak ada data perizinan.</td></tr>
                                <?php else: $no=1; foreach ($perizinanList as $izin): ?>
                                    <tr class="izin-item">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($izin['nama_murid'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['alasan'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['tanggal'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php $status = htmlspecialchars($izin['status'] ?? 'Pending');
                                            if ($status === 'Disetujui'): ?>
                                                <span class="badge bg-success">Disetujui</span>
                                            <?php elseif ($status === 'Ditolak'): ?>
                                                <span class="badge bg-danger">Ditolak</span>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success me-2 btn-setujui" data-id="<?= htmlspecialchars($izin['id'] ?? '') ?>">Setujui</button>
                                                <button class="btn btn-sm btn-danger btn-tolak" data-id="<?= htmlspecialchars($izin['id'] ?? '') ?>">Tolak</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="notifBox" class="notif"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleSidebar");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");
            // sidebar.classList.toggle("expanded"); // expanded tidak diperlukan jika collapsed yang menentukan state
        });

        // === Profil toggle
        const profileBtn = document.getElementById("profileToggle");
        const profileCard = document.getElementById("profileCard");
        profileBtn.addEventListener("click", e => {
            e.stopPropagation();
            profileCard.classList.toggle("show");
        });
        document.addEventListener("click", e => {
            if (!profileBtn.contains(e.target)) profileCard.classList.remove("show");
        });

        // === Pencarian
        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("keyup", () => {
            const keyword = searchInput.value.toLowerCase();
            document.querySelectorAll(".izin-item").forEach(item => {
                const nama = item.querySelector("td:nth-child(2)").textContent.toLowerCase();
                item.style.display = nama.includes(keyword) ? "" : "none";
            });
        });
        
        // Catatan: Logika Setujui/Tolak (AJAX) perlu ditambahkan di sini.
    });
    </script>
</body>
</html>