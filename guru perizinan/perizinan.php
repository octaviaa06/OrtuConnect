<?php
session_name('SESS_GURU');
session_start();
$active_page = 'perizinan_siswa';

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// Ambil data perizinan dari API
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php?t=" . time();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
$response = curl_exec($ch);
if (curl_errno($ch)) $response = json_encode(["success" => false, "data" => []]);
curl_close($ch);

$data = json_decode($response, true);
$perizinanList = $data['data'] ?? [];
$from_param = 'perizinan_siswa'; 
$_GET['from'] = $from_param;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Perizinan | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" /> 
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../guru/sidebar.css" />
</head>
<body>
    <div class="d-flex">

        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
            <div class="container-fluid py-3">
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Perizinan</h4>
                    <?php include '../profil/profil.php'; ?>
                </div>

                <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                    <h5 class="fw-bold mb-4">Daftar Perizinan Murid (Total: <?= count($perizinanList) ?>)</h5>

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
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($perizinanList)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data perizinan.</td></tr>
                                <?php else: 
                                    $no = 1; 
                                    foreach ($perizinanList as $izin): 
                                        $status = $izin['status'] ?? 'Menunggu';
                                ?>
                                    <tr class="izin-item" data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($izin['nama_siswa'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($izin['jenis_izin'] ?? 'N/A') ?></td>
                                        <td>
                                            <small><?= htmlspecialchars($izin['tanggal_range'] ?? '-') ?></small><br>
                                            <span style="font-size: 0.85em; color: #666;">Diajukan: <?= htmlspecialchars($izin['tanggal_pengajuan'] ?? '-') ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($izin['keterangan'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($status === 'Disetujui'): ?>
                                                <span class="badge bg-success">Disetujui</span>
                                            <?php elseif ($status === 'Ditolak'): ?>
                                                <span class="badge bg-danger">Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status === 'Menunggu'): ?>
                                                <button class="btn btn-sm btn-success me-1 btn-setujui" type="button" data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>">
                                                    ✓ Setujui
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-tolak" type="button" data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>">
                                                    ✕ Tolak
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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

    <!-- Modal Alasan Penolakan -->
    <div class="modal fade" id="modalAlasanTolak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Alasan Penolakan Izin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAlasanTolak">
                        <div class="mb-3">
                            <label for="alasanTolak" class="form-label">Masukkan Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alasanTolak" name="alasan" rows="4" required></textarea>
                            <small class="text-muted d-block mt-2">Alasan ini akan dikirimkan ke orang tua siswa</small>
                        </div>
                        <input type="hidden" id="id_izin_tolak">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiTolak">Tolak Izin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Setujui -->
    <div class="modal fade" id="modalKonfirmasiSetujui" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Konfirmasi Persetujuan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>Menyetujui</strong> izin ini?</p>
                    <input type="hidden" id="id_izin_setujui">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiSetujui">Setujui</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notif Box -->
    <div id="notifBox" class="notif" style="position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; color: white; display: none; z-index: 9999;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const API_URL = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";
        const USER_ID = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>;

        let currentIdIzin = null;

        const modalAlasanTolak = new bootstrap.Modal(document.getElementById('modalAlasanTolak'));
        const modalKonfirmasiSetujui = new bootstrap.Modal(document.getElementById('modalKonfirmasiSetujui'));

        // ============ PENCARIAN ============
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("keyup", function() {
                const keyword = this.value.toLowerCase();
                document.querySelectorAll(".izin-item").forEach(item => {
                    const nama = item.querySelector("td:nth-child(2)").textContent.toLowerCase();
                    item.style.display = nama.includes(keyword) ? "" : "none";
                });
            });
        }

        // ============ BUTTON SETUJUI (OPEN MODAL) ============
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-setujui")) {
                const id_izin = e.target.getAttribute("data-id");
                document.getElementById('id_izin_setujui').value = id_izin;
                modalKonfirmasiSetujui.show();
            }
        });

        // ============ KONFIRMASI SETUJUI ============
        document.getElementById("btnKonfirmasiSetujui").addEventListener("click", function() {
            const id = document.getElementById("id_izin_setujui").value;
            modalKonfirmasiSetujui.hide();
            updateStatusIzin(id, "Disetujui", null);
        });

        // ============ BUTTON TOLAK ============
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-tolak")) {
                const id_izin = e.target.getAttribute("data-id");
                currentIdIzin = id_izin;
                document.getElementById('alasanTolak').value = '';
                modalAlasanTolak.show();
            }
        });

        document.getElementById('btnKonfirmasiTolak').addEventListener("click", function() {
            const alasan = document.getElementById('alasanTolak').value.trim();
            modalAlasanTolak.hide();
            updateStatusIzin(currentIdIzin, "Ditolak", alasan);
        });

        // ============ UPDATE STATUS IZIN ============
        function updateStatusIzin(id_izin, status, alasan) {
            const payload = {
                id_izin: parseInt(id_izin),
                status: status,
                id_guru_verifikasi: USER_ID
            };

            if (alasan) payload.alasan_penolakan = alasan;

            fetch(API_URL, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotif(status === 'Disetujui' ? "✓ Izin Disetujui!" : "✗ Izin Ditolak!", "success");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotif("❌ " + (data.message || "Gagal memperbarui status"), "error");
                }
            })
            .catch(error => showNotif("❌ Error: " + error.message, "error"));
        }

        // ============ NOTIFIKASI ============
        function showNotif(message, type) {
            const notifBox = document.getElementById("notifBox");
            notifBox.style.backgroundColor = type === 'success' ? "#28a745" : "#dc3545";
            notifBox.textContent = message;
            notifBox.style.display = "block";
            
            setTimeout(() => notifBox.style.display = "none", 3000);
        }
    </script>

</body>
</html>