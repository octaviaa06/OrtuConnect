<?php
session_start();

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Ganti API endpoint untuk mengambil data PERIZINAN
$api_url = "https://ortuconnect.atwebpages.com/api/admin/absensi.php"; // ASUMSI: API Perizinan yang benar
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    // Jika cURL error, set respons menjadi array kosong
    $response = json_encode(["data" => []]);
}
curl_close($ch);

$data = json_decode($response, true);
$perizinanList = $data['data'] ?? []; // Pastikan ini adalah array, jika gagal akan menjadi array kosong
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Perizinan | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Perizinan.css" />
</head>
<body>
    <div class="d-flex">
        <div id="sidebar" class="sidebar bg-primary text-white p-3 expanded">
            <div class="text-center mb-4">
                <img src="../assets/slide.png" id="toggleSidebar" alt="Slide" class="slide-btn" />
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="../dashboard_admin/home_admin.php" class="nav-link">
                        <img src="../assets/Dashboard.png" class="icon" /><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin data guru/DataGuru.php" class="nav-link">
                        <img src="../assets/Data Guru.png" class="icon" /><span>Data Guru</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin data siswa/DataSiswa.php" class="nav-link">
                        <img src="../assets/Data Siswa.png" class="icon" /><span>Data Murid</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin absensi/Absensi.php" class="nav-link">
                        <img src="../assets/absensi.png" class="icon" /><span>Absensi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin perizinan/Perizinan.php" class="nav-link active">
                        <img src="../assets/Perizinan.png" class="icon" /><span>Perizinan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../admin kalender/Kalender.php" class="nav-link">
                        <img src="../assets/Kalender.png" class="icon" /><span>Kalender</span>
                    </a>
                </li>
            </ul>
        </div>

        <div
            class="flex-grow-1 main-content"
            style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;"
        >
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
                        <div class="search-container position-relative" style="max-width: 400px;">
                            <img src="../assets/cari.png" alt="Cari" class="search-icon" />
                            <input
                                type="text"
                                id="searchInput"
                                class="form-control search-input"
                                placeholder="Cari perizinan berdasarkan nama..."
                            />
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="perizinanTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th style="width: 20%;">Nama Murid</th>
                                    <th style="width: 10%;">Kelas</th>
                                    <th style="width: 15%;">Alasan</th>
                                    <th style="width: 15%;">Tanggal</th>
                                    <th style="width: 35%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($perizinanList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada data perizinan.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($perizinanList as $izin): ?>
                                        <tr class="izin-item">
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($izin['nama_murid'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($izin['alasan'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($izin['tanggal'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $status = htmlspecialchars($izin['status'] ?? 'Pending');
                                                if ($status === 'Disetujui'): ?>
                                                    <span class="badge bg-success">Disetujui</span>
                                                <?php elseif ($status === 'Ditolak'): ?>
                                                    <span class="badge bg-danger">Ditolak</span>
                                                <?php else: ?>
                                                    <button
                                                        class="btn btn-sm btn-success me-2 btn-setujui"
                                                        data-id="<?= htmlspecialchars($izin['id'] ?? '') ?>"
                                                    >
                                                        Setujui
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-danger btn-tolak"
                                                        data-id="<?= htmlspecialchars($izin['id'] ?? '') ?>"
                                                    >
                                                        Tolak
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
        // Fungsi notifikasi SEMENTARA
        function showNotif(message, isSuccess = true) {
            const notifBox = document.getElementById("notifBox");
            notifBox.textContent = message;
            notifBox.style.backgroundColor = isSuccess ? "#28a745" : "#dc3545";
            notifBox.style.display = "block";
            setTimeout(() => {
                notifBox.style.display = "none";
            }, 3000);
        }

        document.addEventListener("DOMContentLoaded", () => {
            const sidebar = document.getElementById("sidebar");
            document.getElementById("toggleSidebar").addEventListener("click", () => sidebar.classList.toggle("collapsed"));

            const profileBtn = document.getElementById("profileToggle");
            const profileCard = document.getElementById("profileCard");
            profileBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                profileCard.classList.toggle("show");
            });
            document.addEventListener("click", (e) => {
                if (!profileBtn.contains(e.target)) profileCard.classList.remove("show");
            });

            // === LOGIKA PENCARIAN
            const searchInput = document.getElementById("searchInput");
            searchInput.addEventListener("keyup", () => {
                const keyword = searchInput.value.toLowerCase();
                document.querySelectorAll(".izin-item").forEach((item) => {
                    // Mengambil teks dari kolom Nama Murid (child ke-2)
                    const nama = item.querySelector("td:nth-child(2)").textContent.toLowerCase();
                    item.style.display = nama.includes(keyword) ? "" : "none";
                });
            });

            // === LOGIKA TOMBOL SETUJUI/TOLAK
            const handleAksi = async (id, aksi) => {
                if (!confirm(`Yakin ingin ${aksi} perizinan ini?`)) return;

                // ASUMSI: URL API untuk Setujui/Tolak
                const api_aksi = `https://ortuconnect.atwebpages.com/api/admin/absensi.php `;

                try {
                    const res = await fetch(api_aksi, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            id_perizinan: id,
                            aksi: aksi, // 'Setujui' atau 'Tolak'
                        }),
                    });
                    const data = await res.json();
                    if (data.status === "success") {
                        showNotif(data.message || `Perizinan berhasil di${aksi.toLowerCase()}!`, true);
                        // Muat ulang data/halaman untuk refresh status
                        location.reload();
                    } else {
                        showNotif(data.message || `Gagal ${aksi} perizinan.`, false);
                    }
                } catch (err) {
                    showNotif("Terjadi kesalahan koneksi.", false);
                }
            };

            document.getElementById("perizinanTable").addEventListener("click", (e) => {
                const target = e.target;
                if (target.classList.contains("btn-setujui")) {
                    handleAksi(target.dataset.id, "Setujui");
                } else if (target.classList.contains("btn-tolak")) {
                    handleAksi(target.dataset.id, "Tolak");
                }
            });
        });
    </script>
</body>
</html>
