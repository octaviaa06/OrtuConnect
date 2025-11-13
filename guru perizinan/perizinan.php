<?php
session_name('SESS_GURU');
session_start();

// Validasi admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// DEBUG MODE - Set ke false setelah selesai testing
$debug_mode = true;

// Fungsi untuk fetch API dengan error handling
function fetchPerizinanData($debug = false) {
    // PENTING: Gunakan API endpoint yang SAMA dengan code asli
    $api_url = "https://ortuconnect.atwebpages.com/api/admin/absensi.php";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    if ($debug) {
        echo "<!-- DEBUG INFO:\n";
        echo "API URL: " . $api_url . "\n";
        echo "HTTP Code: " . $httpCode . "\n";
        echo "cURL Error: " . $curlError . "\n";
        echo "Raw Response: " . substr($response, 0, 500) . "\n";
        echo "-->\n";
    }
    
    if (curl_errno($ch)) {
        if ($debug) echo "<!-- cURL Error: " . $curlError . " -->\n";
        curl_close($ch);
        return [];
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        if ($debug) echo "<!-- HTTP Error Code: " . $httpCode . " -->\n";
        return [];
    }
    
    $data = json_decode($response, true);
    
    if ($debug) {
        echo "<!-- Decoded Data: " . print_r($data, true) . " -->\n";
    }
    
    // Coba berbagai kemungkinan struktur response
    if (isset($data['data'])) {
        return $data['data'];
    } elseif (is_array($data) && !empty($data)) {
        return $data;
    }
    
    return [];
}

$perizinanList = fetchPerizinanData($debug_mode);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Perizinan | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="style.css" />   
</head>

<body>

    <div class="d-flex">
        <!-- Include Sidebar -->
        <?php include '../guru/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center; min-height:100vh;">
            <div class="container-fluid py-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Perizinan</h4>
                    <div class="profile-btn" id="profileToggle" style="cursor: pointer;">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                        <span class="fw-semibold text-primary">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </span>
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

                <!-- Card Content -->
                <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                    <h5 class="fw-bold mb-4">Daftar Perizinan Murid</h5>

                    <!-- Search Bar -->
                    <div class="d-flex justify-content-end mb-3">
                        <div class="search-container position-relative" style="max-width: 400px; width: 100%;">
                            <img src="../assets/cari.png" alt="Cari" class="search-icon" />
                            <input
                                type="text"
                                id="searchInput"
                                class="form-control search-input"
                                placeholder="Cari berdasarkan ID siswa atau kelas..."
                            />
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="perizinanTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th style="width: 15%;">ID Siswa</th>
                                    <th style="width: 10%;">Kelas</th>
                                    <th style="width: 20%;">Alasan</th>
                                    <th style="width: 15%;">Tanggal Mulai</th>
                                    <th style="width: 15%;">Tanggal Selesai</th>
                                    <th style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($perizinanList)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Tidak ada data perizinan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($perizinanList as $izin): ?>
                                        <tr class="izin-item" 
                                            data-id-siswa="<?= htmlspecialchars($izin['id_siswa'] ?? '') ?>"
                                            data-kelas="<?= htmlspecialchars($izin['kelas'] ?? '') ?>">
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($izin['id_siswa'] ?? 'N/A') ?></td> 
                                            <td><?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($izin['keterangan'] ?? 'N/A') ?></td> 
                                            <td><?= htmlspecialchars($izin['tanggal_mulai'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($izin['tanggal_selesai'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $status = strtoupper($izin['status'] ?? 'MENUNGGU');
                                                
                                                if ($status === 'DISETUJUI'): ?>
                                                    <span class="badge bg-success">Disetujui</span>
                                                <?php elseif ($status === 'DITOLAK'): ?>
                                                    <span class="badge bg-danger">Ditolak</span>
                                                <?php else: ?>
                                                    <button
                                                        class="btn btn-sm btn-success me-2 btn-setujui"
                                                        data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>"
                                                        data-id-siswa="<?= htmlspecialchars($izin['id_siswa'] ?? '') ?>">
                                                        Setujui
                                                    </button>
                                                    <button
                                                        class="btn btn-sm btn-danger btn-tolak"
                                                        data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>"
                                                        data-id-siswa="<?= htmlspecialchars($izin['id_siswa'] ?? '') ?>">
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

    <!-- Notification Box -->
    <div id="notifBox" class="notif"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Utility Functions
        const showLoading = (show) => {
            document.getElementById("loadingOverlay").style.display = show ? "flex" : "none";
        };

        const showNotif = (message, isSuccess = true) => {
            const notifBox = document.getElementById("notifBox");
            notifBox.textContent = message;
            notifBox.style.backgroundColor = isSuccess ? "#28a745" : "#dc3545";
            notifBox.style.display = "block";
            
            setTimeout(() => {
                notifBox.style.display = "none";
            }, 3000);
        };

        // Main App Logic
        document.addEventListener("DOMContentLoaded", () => {
            // Profile Dropdown
            const profileBtn = document.getElementById("profileToggle");
            const profileCard = document.getElementById("profileCard");
            
            if (profileBtn && profileCard) {
                profileBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    profileCard.classList.toggle("show");
                });
                
                document.addEventListener("click", (e) => {
                    if (!profileBtn.contains(e.target)) {
                        profileCard.classList.remove("show");
                    }
                });
            }

            // Search Functionality
            const searchInput = document.getElementById("searchInput");
            if (searchInput) {
                searchInput.addEventListener("input", () => {
                    const keyword = searchInput.value.toLowerCase().trim();
                    const rows = document.querySelectorAll(".izin-item");
                    
                    rows.forEach((row) => {
                        const idSiswa = row.dataset.idSiswa.toLowerCase();
                        const kelas = row.dataset.kelas.toLowerCase();
                        const shouldShow = idSiswa.includes(keyword) || kelas.includes(keyword);
                        row.style.display = shouldShow ? "" : "none";
                    });
                });
            }

            // Handle Approve/Reject Actions
            const handleAksi = async (idIzin, idSiswa, aksi) => {
                const aksiText = aksi === "Setujui" ? "menyetujui" : "menolak";
                
                if (!confirm(`Yakin ingin ${aksiText} perizinan untuk siswa ${idSiswa}?`)) {
                    return;
                }

                showLoading(true);

                const apiUrl = "http://ortuconnect.atwebpages.com/api/admin/absensi.php";
                
                try {
                    const response = await fetch(apiUrl, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            id_izin: idIzin,
                            aksi: aksi,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        showNotif(data.message || `Perizinan berhasil di${aksiText}!`, true);
                        
                        // Reload page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotif(data.message || `Gagal ${aksiText} perizinan.`, false);
                    }
                } catch (error) {
                    console.error("Fetch error:", error);
                    showNotif("Terjadi kesalahan koneksi. Silakan coba lagi.", false);
                } finally {
                    showLoading(false);
                }
            };

            // Event Delegation for Action Buttons
            const tableBody = document.querySelector("#perizinanTable tbody");
            
            if (tableBody) {
                tableBody.addEventListener("click", (e) => {
                    const target = e.target;
                    
                    if (target.classList.contains("btn-setujui")) {
                        e.preventDefault();
                        const idIzin = target.dataset.id;
                        const idSiswa = target.dataset.idSiswa;
                        handleAksi(idIzin, idSiswa, "Setujui");
                    } else if (target.classList.contains("btn-tolak")) {
                        e.preventDefault();
                        const idIzin = target.dataset.id;
                        const idSiswa = target.dataset.idSiswa;
                        handleAksi(idIzin, idSiswa, "Tolak");
                    }
                });
            }
        });
    </script>
</body>
</html>