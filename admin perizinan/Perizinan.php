<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'perizinan';

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// PERBAIKAN: Tambahkan cache buster agar data selalu fresh
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/perizinan.php?t=" . time();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// PERBAIKAN: Tambahkan fresh connection untuk mencegah cache
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
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
    <link rel="stylesheet" href="../admin/sidebar.css" />
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="Perizinan.css" /> 
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../admin/sidebar.php'; ?>

        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Perizinan</h4>
                   <?php include '../profil/profil.php'; ?>
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
                                    <th>NO</th>
                                    <th>Nama Murid</th>
                                    <th>Kelas</th>
                                    <th>Jenis Izin</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($perizinanList)): ?>
                                    <tr><td colspan="7" class="text-center text-muted">Tidak ada data perizinan.</td></tr>
                                <?php else: 
                                    $no = 1; 
                                    foreach ($perizinanList as $izin): 
                                        $status = strtolower($izin['status'] ?? 'pending');
                                ?>
                                    <tr class="izin-item">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($izin['nama_siswa'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['jenis_izin'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['keterangan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($izin['tanggal_pengajuan'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($status === 'disetujui'): ?>
                                                <span class="badge bg-success">Disetujui</span>
                                            <?php elseif ($status === 'ditolak'): ?>
                                                <span class="badge bg-danger">Ditolak</span>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success me-2 btn-setujui" data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>">Setujui</button>
                                                <button class="btn btn-sm btn-danger btn-tolak" data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>">Tolak</button>
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

        // Pencarian
        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("keyup", () => {
            const keyword = searchInput.value.toLowerCase();
            document.querySelectorAll(".izin-item").forEach(item => {
                const nama = item.querySelector("td:nth-child(2)").textContent.toLowerCase();
                item.style.display = nama.includes(keyword) ? "" : "none";
            });
        });

        // Button Setujui & Tolak
        attachButtonListeners();

        function attachButtonListeners() {
            document.querySelectorAll(".btn-setujui").forEach(btn => {
                btn.addEventListener("click", function() {
                    const id_izin = this.getAttribute("data-id");
                    if (!id_izin) {
                        showNotif("Error: ID izin tidak ditemukan", "error");
                        return;
                    }
                    if (confirm("Apakah Anda yakin ingin menyetujui izin ini?")) {
                        updateStatusIzin(id_izin, "disetujui");
                    }
                });
            });

            document.querySelectorAll(".btn-tolak").forEach(btn => {
                btn.addEventListener("click", function() {
                    const id_izin = this.getAttribute("data-id");
                    if (!id_izin) {
                        showNotif("Error: ID izin tidak ditemukan", "error");
                        return;
                    }
                    if (confirm("Apakah Anda yakin ingin menolak izin ini?")) {
                        updateStatusIzin(id_izin, "ditolak");
                    }
                });
            });
        }

        function updateStatusIzin(id_izin, status) {
            const apiUrl = "https://ortuconnect.pbltifnganjuk.com/api/admin/perizinan.php";
            
            const payload = {
                id_izin: parseInt(id_izin),
                status: status,
                tanggal_verifikasi: new Date().toISOString().split('T')[0],
                id_guru_verifikasi: <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>
            };

            fetch(apiUrl, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotif("Status izin berhasil diperbarui", "success");
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotif(data.message || "Gagal memperbarui status", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showNotif("Terjadi kesalahan: " + error, "error");
            });
        }

        function showNotif(message, type) {
            const notifBox = document.getElementById("notifBox");
            notifBox.textContent = message;
            notifBox.className = "notif " + type;
            notifBox.style.display = "block";
            
            setTimeout(() => {
                notifBox.style.display = "none";
            }, 3000);
        }
        
    </script>
</body>
</html>