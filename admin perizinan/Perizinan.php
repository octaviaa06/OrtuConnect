<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'perizinan';

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Pastikan admin login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Format tanggal Indonesia
function formatTanggalID($tanggal, $withTime = false) {
    if (empty($tanggal) || $tanggal === '0000-00-00' || $tanggal === '0000-00-00 00:00:00') return '-';

    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    $dt = new DateTime($tanggal);
    $hasil = $dt->format('d') . ' ' . $bulan[$dt->format('m')] . ' ' . $dt->format('Y');

    if ($withTime) $hasil .= ' pukul ' . $dt->format('H:i');
    return $hasil;
}

// Format range tanggal
function formatRangeTanggal($tgl_mulai, $tgl_selesai = null) {
    if (empty($tgl_mulai)) return '-';

    if (empty($tgl_selesai) || $tgl_selesai === $tgl_mulai) {
        return formatTanggalID($tgl_mulai);
    }

    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    $mulai = new DateTime($tgl_mulai);
    $selesai = new DateTime($tgl_selesai);

    $hm = $mulai->format('d');
    $bm = $mulai->format('m');
    $tm = $mulai->format('Y');

    $hs = $selesai->format('d');
    $bs = $selesai->format('m');
    $ts = $selesai->format('Y');

    if ($bm === $bs && $tm === $ts) {
        return "$hm - $hs {$bulan[$bm]} $tm";
    }

    if ($tm === $ts) {
        return "$hm {$bulan[$bm]} - $hs {$bulan[$bs]} $tm";
    }

    return "$hm {$bulan[$bm]} $tm - $hs {$bulan[$bs]} $ts";
}

// Ambil data perizinan API
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php?t=" . time();
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_FRESH_CONNECT => true,
    CURLOPT_FORBID_REUSE => true
]);
$response = curl_exec($ch);
if (curl_errno($ch)) $response = json_encode(["success" => false, "data" => []]);
curl_close($ch);

$data = json_decode($response, true);
$perizinanList = $data['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perizinan | OrtuConnect</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Perizinan.css">
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../admin/sidebar.css">
</head>

<body>
<div class="d-flex">
    <!-- Sidebar -->
    <?php include '../admin/sidebar.php'; ?>

    <!-- Content -->
    <div class="flex-grow-1 main-content"
         style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">

        <div class="container-fluid py-3">
            <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                <h4 class="fw-bold text-primary m-0">Perizinan</h4>
                <?php include '../profil/profil.php'; ?>
            </div>

            <div class="card shadow-sm border-0 p-4" style="border-radius:16px;">
                <h5 class="fw-bold mb-4">Daftar Perizinan Murid (Total: <?= count($perizinanList) ?>)</h5>

                <!-- Search -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="search-container position-relative" style="max-width:400px;">
                        <img src="../assets/cari.png" alt="Cari" class="search-icon">
                        <input type="text" id="searchInput" class="form-control search-input"
                               placeholder="Cari perizinan berdasarkan nama...">
                    </div>
                </div>

                <!-- Table -->
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
                        <?php else: $no = 1;
                            foreach ($perizinanList as $izin):
                                $status = $izin['status'] ?? 'Menunggu';
                                ?>
                                <tr class="izin-item" data-id="<?= htmlspecialchars($izin['id_izin'] ?? '') ?>">
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($izin['nama_siswa'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($izin['kelas'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($izin['jenis_izin'] ?? 'N/A') ?></td>

                                    <td>
                                        <small>
                                            <?= formatRangeTanggal($izin['tanggal_mulai'], $izin['tanggal_selesai']) ?>
                                        </small>
                                        <br>
                                        <span style="font-size:.85em; color:#666;">
                                            Diajukan: <?= formatTanggalID($izin['tanggal_pengajuan'], true) ?>
                                        </span>
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
                                            <button class="btn btn-sm btn-success me-1 btn-setujui"
                                                    data-id="<?= htmlspecialchars($izin['id_izin']) ?>">
                                                ✓ Setujui
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-tolak"
                                                    data-id="<?= htmlspecialchars($izin['id_izin']) ?>">
                                                ✕ Tolak
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Penolakan -->
<div class="modal fade" id="modalAlasanTolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Alasan Penolakan Izin</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAlasanTolak">
                    <div class="mb-3">
                        <label class="form-label">
                            Masukkan Alasan Penolakan <span class="text-danger">*</span>
                        </label>

                        <textarea class="form-control" id="alasanTolak" name="alasan"
                                  rows="4" placeholder="Contoh: Dokumen tidak lengkap..." required></textarea>

                        <small class="text-muted d-block mt-2">
                            Alasan ini akan dikirimkan ke orang tua siswa
                        </small>
                    </div>

                    <input type="hidden" id="id_izin_tolak">
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger" id="btnKonfirmasiTolak">Tolak Izin</button>
            </div>

        </div>
    </div>
</div>

<!-- Notification Box -->
<div id="notifBox" class="notif"
     style="position:fixed; top:20px; right:20px; padding:15px 20px; border-radius:8px;
            color:white; display:none; z-index:9999;"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ========== SEARCH ========== */
document.getElementById("searchInput")?.addEventListener("keyup", function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll(".izin-item").forEach(item => {
        const nama = item.querySelector("td:nth-child(2)").textContent.toLowerCase();
        item.style.display = nama.includes(keyword) ? "" : "none";
    });
});

/* ========== MODAL & BUTTON ========== */
const modalAlasanTolak = new bootstrap.Modal(document.getElementById('modalAlasanTolak'));
const btnKonfirmasiTolak = document.getElementById('btnKonfirmasiTolak');

attachButtonListeners();

function attachButtonListeners() {

    // SETUJUI
    document.querySelectorAll(".btn-setujui").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            if (!id) return showNotif("Error: ID izin tidak ditemukan", "error");

            if (confirm("Apakah Anda yakin ingin MENYETUJUI izin ini?")) {
                updateStatusIzin(id, "Disetujui", null);
            }
        });
    });

    // TOLAK -> buka modal
    document.querySelectorAll(".btn-tolak").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            if (!id) return showNotif("Error: ID izin tidak ditemukan", "error");

            document.getElementById('id_izin_tolak').value = id;
            document.getElementById('alasanTolak').value = '';
            modalAlasanTolak.show();
        });
    });
}

/* ========== KONFIRMASI TOLAK ========== */
btnKonfirmasiTolak.addEventListener("click", function () {
    const id = document.getElementById('id_izin_tolak').value;
    const alasan = document.getElementById('alasanTolak').value.trim();

    if (!alasan) return showNotif("⚠ Alasan penolakan harus diisi!", "error");

    modalAlasanTolak.hide();
    updateStatusIzin(id, "Ditolak", alasan);
});

/* ========== UPDATE STATUS API ========== */
function updateStatusIzin(id_izin, status, alasan) {
    const apiUrl = "https://ortuconnect.pbltifnganjuk.com/api/perizinan.php";

    const payload = {
        id_izin: parseInt(id_izin),
        status: status,
        id_guru_verifikasi: <?= $_SESSION['user_id'] ?? 0 ?>
    };

    if (alasan) payload.alasan_penolakan = alasan;

    document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => btn.disabled = true);

    fetch(apiUrl, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showNotif(status === "Disetujui" ? "✓ Izin Disetujui!" : "✗ Izin Ditolak!", "success");
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotif("❌ " + (d.message || "Gagal memperbarui status"), "error");
            }
        })
        .catch(err => showNotif("❌ Error: " + err.message, "error"))
        .finally(() => {
            document.querySelectorAll(`[data-id="${id_izin}"]`).forEach(btn => btn.disabled = false);
        });
}

/* ========== NOTIFIKASI ========== */
function showNotif(msg, type) {
    const box = document.getElementById("notifBox");
    box.style.backgroundColor = type === "success" ? "#28a745" : "#dc3545";
    box.textContent = msg;
    box.style.display = "block";

    setTimeout(() => box.style.display = "none", 3000);
}

// Re-attach listener
document.getElementById('modalAlasanTolak')
    .addEventListener('hidden.bs.modal', attachButtonListeners);
</script>

</body>
</html>
