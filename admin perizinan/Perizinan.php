<?php
session_name('SESS_ADMIN');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

// Ambil data perizinan (dengan cache buster)
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/perizinan.php?t=" . time();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
$response = curl_exec($ch);
$ch = null;

$data = json_decode($response, true) ?? [];
$perizinanList = $data['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Perizinan | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Perizinan.css"> <!-- CSS Anda yang sudah dipisah -->
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../admin/sidebar.css">
</head>
<body>

<div class="d-flex position-relative">
    <?php include '../admin/sidebar.php'; ?>

    <div class="main-content bg-perizinan-guru">
        <div class="container-fluid py-3 py-md-4">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                <h4 class="fw-bold text-primary m-0">Perizinan Murid</h4>
                <?php include '../profil/profil.php'; ?>
            </div>

            <!-- CARD UTAMA -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">

                    <h5 class="fw-bold text-primary mb-4">Daftar Pengajuan Izin</h5>

                    <!-- SEARCH BAR -->
                    <div class="d-flex justify-content-end mb-4">
                        <div class="search-container position-relative">
                            <img src="../assets/cari.png" alt="Cari" class="search-icon">
                            <input type="text" id="searchInput" class="form-control search-input" placeholder="Cari nama murid...">
                        </div>
                    </div>

                    <!-- TOMBOL SCROLL NAVIGATION (Muncul otomatis via JS di mobile) -->
                    <div class="scroll-nav" id="scrollNav">
                        <button class="scroll-btn" id="scrollLeft">
                            <svg fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                            </svg>
                            Geser Kiri
                        </button>
                        <button class="scroll-btn" id="scrollRight">
                            Geser Kanan
                            <svg fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- WRAPPER TABEL — SESUAI STYLE.CSS ANDA -->
                    <div class="table-responsive" id="tableWrapper">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">NO</th>
                                    <th>Nama Murid</th>
                                    <th>Kelas</th>
                                    <th>Jenis Izin</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="izinBody">
                                <?php if (empty($perizinanList)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <img src="../assets/empty-perizinan.png" alt="Kosong" width="80" class="mb-3 opacity-50">
                                            <br>Belum ada pengajuan izin.
                                        </td>
                                    </tr>
                                <?php else: $no = 1; foreach ($perizinanList as $izin): 
                                    $status = strtolower($izin['status'] ?? 'pending');
                                ?>
                                    <tr class="izin-item">
                                        <td class="text-center fw-bold"><?= $no++ ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($izin['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($izin['kelas'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($izin['jenis_izin'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="text-muted small"><?= htmlspecialchars($izin['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted small"><?= !empty($izin['tanggal_pengajuan']) ? date('d/m/Y', strtotime($izin['tanggal_pengajuan'])) : '-' ?></td>
                                        <td class="text-center">
                                            <?php if ($status === 'disetujui'): ?>
                                                <span class="badge bg-success fs-6">Disetujui</span>
                                            <?php elseif ($status === 'ditolak'): ?>
                                                <span class="badge bg-danger fs-6">Ditolak</span>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success me-1 btn-setujui" data-id="<?= (int)($izin['id_izin'] ?? 0) ?>">
                                                    Setujui
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-tolak" data-id="<?= (int)($izin['id_izin'] ?? 0) ?>">
                                                    Tolak
                                                </button>
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
</div>

<!-- NOTIFIKASI — SESUAI POSISI DI style.css (bottom center) -->
<div id="notifBox" class="notif"></div>

<!-- MODAL KONFIRMASI (Bootstrap Modal) -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Verifikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p id="confirmText" class="mb-0">Apakah Anda yakin ingin <strong>menyetujui</strong> izin ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirmYesBtn" class="btn btn-primary">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // === JS LOGIC (tanpa CSS inline) ===
    const tableWrapper = document.getElementById('tableWrapper');
    const scrollNav = document.getElementById('scrollNav');
    const scrollLeftBtn = document.getElementById('scrollLeft');
    const scrollRightBtn = document.getElementById('scrollRight');

    // Cek apakah butuh scroll nav (mobile only)
    function checkScrollButtons() {
        if (window.innerWidth <= 992) {
            const hasScroll = tableWrapper.scrollWidth > tableWrapper.clientWidth;
            scrollNav.classList.toggle('active', hasScroll);
            if (hasScroll) updateButtonStates();
        } else {
            scrollNav.classList.remove('active');
        }
    }

        function updateStatusIzin(id_izin, status) {
            const apiUrl = "https://ortuconnect.pbltifnganjuk.com/api/admin/perizinan.php";
            
            document.getElementById('confirmText').innerHTML = 
                `Apakah Anda yakin ingin <strong>${actionText}</strong> izin ini?`;
            
            pendingAction = { id, status };
            confirmModal?.show();
        });
    });

    document.getElementById('confirmYesBtn')?.addEventListener('click', function() {
        if (pendingAction.id && pendingAction.status) {
            updateStatus(pendingAction.id, pendingAction.status);
            confirmModal?.hide();
            pendingAction = { id: null, status: null };
        }
    });

    function updateStatus(id_izin, status) {
        fetch('https://ortuconnect.pbltifnganjuk.com/api/admin/perizinan.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_izin: parseInt(id_izin),
                status: status,
                tanggal_verifikasi: new Date().toISOString().split('T')[0],
                id_guru_verifikasi: <?= (int)($_SESSION['user_id'] ?? 0) ?>
            })
        })
        .then(r => r.json())
        .then(res => {
            showNotif(res.success ? '✅ Berhasil diverifikasi!' : (res.message || '❌ Gagal'), res.success);
            if (res.success) setTimeout(() => location.reload(), 1800);
        })
        .catch(() => showNotif('❌ Gagal terhubung ke server!', false));
    }

    function showNotif(msg, success = true) {
        const box = document.getElementById('notifBox');
        if (!box) return;
        box.textContent = msg;
        box.className = 'notif ' + (success ? 'success' : 'error');
        box.style.display = 'block';

        setTimeout(() => {
            box.style.opacity = '0';
            setTimeout(() => box.style.display = 'none', 300);
        }, 2500);
    }
</script>
</body>
</html>