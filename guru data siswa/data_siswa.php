<?php
session_name('SESS_GURU');
session_start();

// Verifikasi role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// Ambil data API
$api_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/data_siswa.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $response = json_encode(["data" => []]);
}
$ch = null;

$data = json_decode($response, true);
$siswaList = $data['data'] ?? [];

$from_param = 'data_siswa';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="data_siswa.css">
    <link rel="stylesheet" href="../profil/profil.css">
     <link rel="stylesheet" href="../guru/sidebar.css">
</head>
<body>

<div class="d-flex">

    <!-- SIDEBAR (GUNAKAN SIDEBAR.PHP YANG BENAR) -->
    <?php include'../guru/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 main-content"
         style="background-image:url('../background/Data Siswa(1).png'); background-size:cover; background-position:center;">

        <div class="container-fluid py-3">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <h4 class="fw-bold text-primary m-0 page-title">Data Murid</h4>

                <div class="profile-area">
                    <?php 
                    $_GET['from'] = $from_param;
                    include "../profil/profil.php"; 
                    ?>
                </div>
            </div>

            <!-- SEARCH BAR -->
            <div class="search-action-bar mb-4">
                <div class="search-container flex-grow-1">
                    <img src="../assets/cari.png" alt="Cari" class="search-icon">
                    <input type="text" id="searchInput" class="search-input"
                           placeholder="Cari murid berdasarkan nama atau kelas...">
                </div>
            </div>

            <!-- DATA SISWA -->
            <div class="row g-3" id="siswaContainer">

                <?php if (empty($siswaList)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <img src="../assets/empty-state.png" alt="No Data" style="width: 80px; opacity: 0.5;" class="mb-2">
                            <p class="mb-0">Tidak ada data murid.</p>
                        </div>
                    </div>

                <?php else: ?>

                    <?php foreach ($siswaList as $siswa): 
                        $nama = htmlspecialchars($siswa['nama_siswa']);
                        $kata = explode(' ', $nama);
                        $inisial = (count($kata) >= 2)
                          ? strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1))
                          : strtoupper(substr($kata[0], 0, 2));
                    ?>

                    <div class="col-lg-4 col-md-6 col-12 siswa-item">
                        <div class="card siswa-card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-inisial"><?= $inisial ?></div>
                                    <div class="ms-3 flex-grow-1">
                                        <h5 class="siswa-name mb-1"><?= $nama ?></h5>
                                        <span class="siswa-class"><?= htmlspecialchars($siswa['kelas']); ?></span>
                                    </div>
                                </div>

                                <div class="siswa-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Jenis Kelamin:</span>
                                        <span class="detail-value"><?= htmlspecialchars($siswa['gender']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Orang Tua:</span>
                                        <span class="detail-value"><?= htmlspecialchars($siswa['nama_ortu']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">No. Telp:</span>
                                        <span class="detail-value"><?= htmlspecialchars($siswa['no_telp_ortu']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Alamat:</span>
                                        <span class="detail-value"><?= nl2br(htmlspecialchars($siswa['alamat'])); ?></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>
    </div>
</div>

<script>
// ===== PENCARIAN SISWA =====
const searchInput = document.getElementById('searchInput');
const siswaContainer = document.getElementById('siswaContainer');

searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase();
    const items = siswaContainer.querySelectorAll('.siswa-item');

    let visible = 0;

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const match = text.includes(keyword);
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const noMsg = document.getElementById('noResultMessage');

    if (visible === 0 && keyword !== '') {
        if (!noMsg) {
            const msg = document.createElement('div');
            msg.id = 'noResultMessage';
            msg.className = 'col-12';
            msg.innerHTML = '<div class="alert alert-warning text-center">Tidak ada murid yang cocok.</div>';
            siswaContainer.appendChild(msg);
        }
    } else if (noMsg) {
        noMsg.remove();
    }
});
</script>

</body>
</html>