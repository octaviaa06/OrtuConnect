<?php 
session_start(); 

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>Perizinan | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../guru/sidebar.css" />
</head>
<body>
    <div class="d-flex main-wrapper">

        <?php include '../guru/sidebar.php'; ?>
        
        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <div class="flex-grow-1 main-content bg-perizinan-admin">
            <div class="container-fluid py-3">

                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <div class="d-flex align-items-center">
                        
                        <h4 class="fw-bold text-primary m-0">Perizinan</h4>
                    </div>
                    <?php include '../profil/profil.php'; ?>
                </div>

                <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                    <h5 class="fw-bold mb-4">Daftar Perizinan Murid (Total: <?= count($perizinanList) ?>)</h5>

                    <div class="d-flex justify-content-end mb-3">
                        <div class="search-container">
                            <img src="../assets/cari.png" alt="Cari" class="search-icon" />
                            <input type="text" id="searchInput" class="search-input" placeholder="Cari perizinan berdasarkan nama..." />
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
                                    <tr class="izin-item">
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
                                                <button class="btn btn-sm btn-success me-1 btn-setujui" type="button" 
                                                    data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>"
                                                    data-nama="<?= htmlspecialchars($izin['nama_siswa'] ?? 'N/A') ?>"
                                                    data-kelas="<?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?>"
                                                    data-jenis="<?= htmlspecialchars($izin['jenis_izin'] ?? 'N/A') ?>"
                                                    data-tanggal="<?= htmlspecialchars($izin['tanggal_range'] ?? '-') ?>">
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

    <div class="modal fade" id="modalKonfirmasiSetujui" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Konfirmasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-center mb-3">Apakah Anda yakin ingin menyetujui izin ini?</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Nama:</td><td class="fw-bold" id="setujuiNama">-</td></tr>
                        <tr><td class="text-muted">Kelas:</td><td class="fw-bold" id="setujuiKelas">-</td></tr>
                        <tr><td class="text-muted">Jenis:</td><td class="fw-bold" id="setujuiJenis">-</td></tr>
                        <tr><td class="text-muted">Tanggal:</td><td class="fw-bold" id="setujuiTanggal">-</td></tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiSetujui">Ya, Setujui</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAlasanTolak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Alasan Penolakan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="alasanTolak" rows="4" placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiTolak">Tolak Izin</button>
                </div>
            </div>
        </div>
    </div>

    <div id="notifBox" class="notif"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";
        const USER_ID = <?= $_SESSION['user_id'] ?? 0 ?>;
        let currentIdIzin = null;

        // PENCARIAN
        document.getElementById("searchInput").addEventListener("keyup", function() {
            const keyword = this.value.toLowerCase();
            document.querySelectorAll(".izin-item").forEach(item => {
                item.style.display = item.innerText.toLowerCase().includes(keyword) ? "" : "none";
            });
        });

        // LOGIKA MODAL SETUJUI
        const modalSetuju = new bootstrap.Modal(document.getElementById('modalKonfirmasiSetujui'));
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-setujui")) {
                const btn = e.target;
                currentIdIzin = btn.getAttribute("data-id");
                document.getElementById("setujuiNama").textContent = btn.getAttribute("data-nama");
                document.getElementById("setujuiKelas").textContent = btn.getAttribute("data-kelas");
                document.getElementById("setujuiJenis").textContent = btn.getAttribute("data-jenis");
                document.getElementById("setujuiTanggal").textContent = btn.getAttribute("data-tanggal");
                modalSetuju.show();
            }
        });

        document.getElementById('btnKonfirmasiSetujui').addEventListener("click", () => {
            modalSetuju.hide();
            updateStatusIzin(currentIdIzin, "Disetujui");
        });

        // LOGIKA MODAL TOLAK
        const modalTolak = new bootstrap.Modal(document.getElementById('modalAlasanTolak'));
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btn-tolak")) {
                currentIdIzin = e.target.getAttribute("data-id");
                document.getElementById('alasanTolak').value = '';
                modalTolak.show();
            }
        });

        document.getElementById('btnKonfirmasiTolak').addEventListener("click", () => {
            const alasan = document.getElementById('alasanTolak').value.trim();
            if (!alasan) return alert("Alasan harus diisi!");
            modalTolak.hide();
            updateStatusIzin(currentIdIzin, "Ditolak", alasan);
        });

        function updateStatusIzin(id, status, alasan = null) {
            const payload = { id_izin: parseInt(id), status: status, id_guru_verifikasi: USER_ID };
            if (alasan) payload.alasan_penolakan = alasan;

            fetch(API_URL, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotif("Berhasil memperbarui status!", "success");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotif(data.message, "error");
                }
            });
        }

        function showNotif(message, type) {
            const box = document.getElementById("notifBox");
            box.className = `notif ${type}`;
            box.textContent = message;
            box.style.display = "block";
            setTimeout(() => { box.style.display = "none"; }, 3000);
        }
    </script>
</body>
</html>