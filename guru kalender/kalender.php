<?php
session_name('SESS_GURU');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// ====== LOGIKA KALENDER ======
$current_month = $_GET['month'] ?? date('n');
$current_year  = $_GET['year'] ?? date('Y');

if (isset($_GET['nav'])) {
    if ($_GET['nav'] === 'next') {
        $current_month++;
        if ($current_month > 12) {
            $current_month = 1;
            $current_year++;
        }
    } elseif ($_GET['nav'] === 'prev') {
        $current_month--;
        if ($current_month < 1) {
            $current_month = 12;
            $current_year--;
        }
    }
}

$first_day_of_month = mktime(0, 0, 0, $current_month, 1, $current_year);
$number_of_days     = date('t', $first_day_of_month);
$date_components    = getdate($first_day_of_month);
$month_name         = date('F Y', $first_day_of_month);
$day_of_week        = $date_components['wday'];

$api_agenda_url = "https://ortuconnect.atwebpages.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_agenda_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $response = json_encode([
        "data" => [
            ['id' => 1, 'nama_kegiatan' => 'Memakai Baju Batik', 'tanggal' => '2025-11-10']
        ]
    ]);
}
curl_close($ch);

$data        = json_decode($response, true);
$agendaList  = $data['data'] ?? [];

$agendaByDate = [];
foreach ($agendaList as $agenda) {
    $date_key = date('Y-m-d', strtotime($agenda['tanggal']));
    $agendaByDate[$date_key][] = $agenda;
}

$selected_day       = $_GET['day'] ?? ((date('Y') == $current_year && date('n') == $current_month) ? date('j') : 1);
$selected_date_full = date('Y-m-d', mktime(0, 0, 0, $current_month, $selected_day, $current_year));
$selected_agenda    = $agendaByDate[$selected_date_full] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kalender | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../guru/sidebar.css" /> 
    <link rel="stylesheet" href="kalender.css" />
</head>

<body>
    <div class="d-flex">
        <!-- ✅ PANGGIL SIDEBAR DI SINI -->
        <?php include '../guru/sidebar.php'; ?>

        <!-- ✅ KONTEN UTAMA -->
        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
            <div class="container-fluid py-3">

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Kalender</h4>
                    <div class="profile-btn" id="profileToggle">
                        <div class="profile-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                        <span class="fw-semibold text-primary"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <div class="profile-card" id="profileCard">
                            <h6><?= ucfirst($_SESSION['role']) ?></h6>
                            <p><?= htmlspecialchars($_SESSION['username']) ?>@gmail.com</p>
                            <hr />
                            <a href="../logout/logout.php?from=kalender" class="logout-btn">
                                <img src="../assets/keluar.png" alt="Logout" /> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mb-5"></div>

                <div class="row">
                    <!-- KOLOM KIRI: KALENDER -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 p-4 kalender-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold m-0"><?= str_replace(date('Y'), '', $month_name) ?> <?= $current_year ?></h5>
                                <div class="kalender-nav-buttons">
                                    <a href="Kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=prev&day=<?= $selected_day ?>" class="nav-arrow me-2">&lt;</a>
                                    <a href="Kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=next&day=<?= $selected_day ?>" class="nav-arrow">&gt;</a>
                                </div>
                            </div>

                            <div class="kalender-grid">
                                <div class="hari-header minggu">Minggu</div>
                                <div class="hari-header">Senin</div>
                                <div class="hari-header">Selasa</div>
                                <div class="hari-header">Rabu</div>
                                <div class="hari-header">Kamis</div>
                                <div class="hari-header">Jumat</div>
                                <div class="hari-header">Sabtu</div>

                                <?php
                                $day_counter = 1;
                                for ($i = 0; $i < $day_of_week; $i++) echo "<div class='tanggal-kosong'></div>";

                                for ($day = 1; $day <= $number_of_days; $day++) {
                                    $date_string = date('Y-m-d', mktime(0, 0, 0, $current_month, $day, $current_year));
                                    $is_today    = ($date_string == date('Y-m-d'));
                                    $is_selected = ($day == $selected_day);
                                    $has_agenda  = isset($agendaByDate[$date_string]);

                                    $class = 'tanggal-item';
                                    if ($day_counter % 7 == 1) $class .= ' minggu';
                                    if ($is_today) $class .= ' today';
                                    if ($is_selected) $class .= ' selected-day';
                                    if ($has_agenda) $class .= ' has-agenda';

                                    $link = "Kalender.php?month={$current_month}&year={$current_year}&day={$day}";
                                    echo "<a href='{$link}' class='{$class}' data-date='{$date_string}'><span>{$day}</span></a>";

                                    $day_counter++;
                                }

                                while ($day_counter <= 42) {
                                    if ($day_counter % 7 == 1) break;
                                    echo "<div class='tanggal-kosong'></div>";
                                    $day_counter++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: DAFTAR KEGIATAN -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 p-4 daftar-kegiatan-container">
                            <h5 class="fw-bold mb-3 text-primary">Daftar Kegiatan</h5>
                            <p class="text-muted mb-4">Kegiatan untuk tanggal: <b><?= date('j F Y', strtotime($selected_date_full)) ?></b></p>

                            <div id="daftarAgendaContent">
                                <?php if (empty($selected_agenda)): ?>
                                    <div class="alert alert-info text-center">Tidak ada agenda pada tanggal ini.</div>
                                <?php else: ?>
                                    <?php foreach ($selected_agenda as $kegiatan): ?>
                                        <div class="kegiatan-item d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                            <div>
                                                <p class="fw-semibold m-0"><?= htmlspecialchars($kegiatan['nama_kegiatan'] ?? 'Kegiatan Tanpa Nama') ?></p>
                                                <small class="text-muted"><?= date('j F Y', strtotime($kegiatan['tanggal'] ?? $selected_date_full)) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const profileBtn = document.getElementById("profileToggle");
            const profileCard = document.getElementById("profileCard");
            if (profileBtn && profileCard) {
                profileBtn.addEventListener("click", e => {
                    e.stopPropagation();
                    profileCard.classList.toggle("show");
                });
                document.addEventListener("click", e => {
                    if (!profileBtn.contains(e.target)) {
                        profileCard.classList.remove("show");
                    }
                });
            }
        });
    </script>
</body>
</html>
