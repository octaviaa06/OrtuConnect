<?php
session_name('SESS_GURU');
session_start();


// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// Helper function untuk format tanggal Indonesia
function formatTanggalID($tanggal, $withTime = false) {
    if (empty($tanggal) || $tanggal === '0000-00-00' || $tanggal === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    $datetime = new DateTime($tanggal);
    $hari = $datetime->format('d');
    $bulanNum = $datetime->format('m');
    $tahun = $datetime->format('Y');
    
    $hasil = $hari . ' ' . $bulan[$bulanNum] . ' ' . $tahun;
    
    if ($withTime) {
        $jam = $datetime->format('H:i');
        $hasil .= ' pukul ' . $jam;
    }
    
    return $hasil;
}

function formatRangeTanggal($tanggal_mulai, $tanggal_selesai = null) {
    if (empty($tanggal_mulai)) {
        return '-';
    }
    
    if (empty($tanggal_selesai) || $tanggal_selesai === $tanggal_mulai) {
        return formatTanggalID($tanggal_mulai);
    }
    
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    $dt_mulai = new DateTime($tanggal_mulai);
    $dt_selesai = new DateTime($tanggal_selesai);
    
    $hari_mulai = $dt_mulai->format('d');
    $bulan_mulai = $dt_mulai->format('m');
    $tahun_mulai = $dt_mulai->format('Y');
    
    $hari_selesai = $dt_selesai->format('d');
    $bulan_selesai = $dt_selesai->format('m');
    $tahun_selesai = $dt_selesai->format('Y');
    
    if ($bulan_mulai === $bulan_selesai && $tahun_mulai === $tahun_selesai) {
        return $hari_mulai . ' - ' . $hari_selesai . ' ' . $bulan[$bulan_mulai] . ' ' . $tahun_mulai;
    }
    
    if ($tahun_mulai === $tahun_selesai) {
        return $hari_mulai . ' ' . $bulan[$bulan_mulai] . ' - ' . $hari_selesai . ' ' . $bulan[$bulan_selesai] . ' ' . $tahun_mulai;
    }
    
    return $hari_mulai . ' ' . $bulan[$bulan_mulai] . ' ' . $tahun_mulai . ' - ' . $hari_selesai . ' ' . $bulan[$bulan_selesai] . ' ' . $tahun_selesai;
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


$from_param = 'perizinan';
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
        <!-- Sidebar -->
        <?php include '../guru/sidebar.php'; ?>

 <!-- MAIN CONTENT -->
    <div class="flex-grow-1 main-content"
         style="background-image:url('../background/Data Siswa(1).png'); background-size:cover; background-position:center;">

        <div class="container-fluid py-3">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <h4 class="fw-bold text-primary m-0 page-title">Perizinan</h4>

                <div class="profile-area">
                    <?php 
                    $_GET['from'] = $from_param;
                    include "../profil/profil.php"; 
                    ?>
                </div>
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
                                            <small><?= formatRangeTanggal($izin['tanggal_mulai'] ?? '', $izin['tanggal_selesai'] ?? '') ?></small><br>
                                            <span style="font-size: 0.85em; color: #666;">Diajukan: <?= formatTanggalID($izin['tanggal_pengajuan'] ?? '', true) ?></span>
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
                            <textarea class="form-control" id="alasanTolak" name="alasan" rows="4" placeholder="Contoh: Dokumen tidak lengkap, format tidak sesuai, dll..." required></textarea>
                            <small class="text-muted d-block mt-2">Alasan ini akan dikirimkan ke orang tua siswa</small>
                        </div>
                        <input type="hidden" id="id_izin_tolak" value="">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiTolak">Tolak Izin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notif Box -->
    <div id="notifBox" class="notif" style="position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; color: white; display: none; z-index: 9999;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // ============ MODAL & BUTTON LISTENERS ============
        const modalAlasanTolak = new bootstrap.Modal(document.getElementById('modalAlasanTolak'));
        const btnKonfirmasiTolak = document.getElementById('btnKonfirmasiTolak');

        attachButtonListeners();

        function attachButtonListeners() {
            // SETUJUI
            document.querySelectorAll(".btn-setujui").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id_izin = this.getAttribute("data-id");
                    
                    if (!id_izin) {
                        showNotif("Error: ID izin tidak ditemukan", "error");
                        return;
                    }
                    
                    if (confirm("Apakah Anda yakin ingin MENYETUJUI izin ini?")) {
                        updateStatusIzin(id_izin, "Disetujui", null);
                    }
                });
            });

            // TOLAK - Buka modal
            document.querySelectorAll(".btn-tolak").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id_izin = this.getAttribute("data-id");
                    
                    if (!id_izin) {
                        showNotif("Error: ID izin tidak ditemukan", "error");
                        return;
                    }
                    
                    // Set ID izin ke hidden input
                    document.getElementById('id_izin_tolak').value = id_izin;
                    // Clear textarea
                    document.getElementById('alasanTolak').value = '';
                    // Buka modal
                    modalAlasanTolak.show();
                });
            });
        }

        // Konfirmasi penolakan
        btnKonfirmasiTolak.addEventListener("click", function() {
            const id_izin = document.getElementById('id_izin_tolak').value;
            const alasan = document.getElementById('alasanTolak').value.trim();
            
            if (!alasan) {
                showNotif("⚠️ Alasan penolakan harus diisi!", "error");
                return;
            }
            
            // Tutup modal
            modalAlasanTolak.hide();
            
            // Update status
            updateStatusIzin(id_izin, "Ditolak", alasan);
        });

        // ============ UPDATE STATUS ============
        function updateStatusIzin(id_izin, status, alasan) {
            const apiUrl = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";
            
            const payload = {
                id_izin: parseInt(id_izin),
                status: status,
                id_guru_verifikasi: <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0 ?>
            };

            // Tambahkan alasan jika ditolak
            if (alasan) {
                payload.alasan_penolakan = alasan;
            }

            // Disable button saat proses
            document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => {
                btn.disabled = true;
            });

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
                    const pesan = status === 'Disetujui' ? '✓ Izin Disetujui!' : '✗ Izin Ditolak!';
                    showNotif(pesan, "success");
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showNotif("❌ " + (data.message || "Gagal memperbarui status"), "error");
                    // Enable button lagi jika gagal
                    document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => {
                        btn.disabled = false;
                    });
                }
            })
            .catch(error => {
                showNotif("❌ Error: " + error.message, "error");
                // Enable button lagi jika error
                document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => {
                    btn.disabled = false;
                });
            });
        }

        // ============ NOTIFIKASI ============
        function showNotif(message, type) {
            const notifBox = document.getElementById("notifBox");
            
            if (type === 'success') {
                notifBox.style.backgroundColor = "#28a745";
            } else if (type === 'error') {
                notifBox.style.backgroundColor = "#dc3545";
            }
            
            notifBox.textContent = message;
            notifBox.style.display = "block";
            
            setTimeout(() => {
                notifBox.style.display = "none";
            }, 3000);
        }

        // Re-attach listeners setelah modal ditutup
        document.getElementById('modalAlasanTolak').addEventListener('hidden.bs.modal', function() {
            attachButtonListeners();
        });
        
    </script>
</body>
</html>