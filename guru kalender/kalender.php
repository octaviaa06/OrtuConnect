<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
$active_page = 'kalender guru';

// CEK APAKAH SUDAH LOGIN SEBAGAI GURU
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

$current_month = $_GET['month'] ?? date('n');
$current_year = $_GET['year'] ?? date('Y');

// NAVIGASI BULAN (NEXT/PREV)
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

// HITUNG INFORMASI BULAN
$first_day_of_month = mktime(0, 0, 0, $current_month, 1, $current_year);
$number_of_days = date('t', $first_day_of_month); // Jumlah hari dalam bulan
$date_components = getdate($first_day_of_month);
$day_of_week = $date_components['wday']; // Hari pertama bulan (0=Minggu)

// AMBIL DATA AGENDA DARI API
$api_agenda_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_agenda_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);

// JIKA API ERROR, GUNAKAN DATA DUMMY
if (curl_errno($ch)) {
    $response = json_encode([
        "data" => [
            [
                'id' => 1,
                'nama_kegiatan' => 'Memakai Baju Batik',
                'tanggal' => '2025-11-10',
                'deskripsi' => 'Seluruh siswa wajib memakai baju batik nasional.'
            ],
            [
                'id' => 2,
                'nama_kegiatan' => 'Pertemuan Orang Tua',
                'tanggal' => '2025-11-15',
                'deskripsi' => 'Rapat koordinasi dengan wali murid kelas 8A.'
            ]
        ]
    ]);
}
curl_close($ch);

// PROSES DATA AGENDA
$data = json_decode($response, true);
$agendaList = $data['data'] ?? [];

// KELOMPOKKAN AGENDA BERDASARKAN TANGGAL
$agendaByDate = [];
foreach ($agendaList as $agenda) {
    $date_key = date('Y-m-d', strtotime($agenda['tanggal']));
    $agendaByDate[$date_key][] = $agenda;
}

// TENTUKAN HARI YANG DIPILIH
$selected_day  = $_GET['day'] ?? ((date('Y') == $current_year && date('n') == $current_month) ? date('j') : 1);
$selected_date_full = date('Y-m-d', mktime(0, 0, 0, $current_month, $selected_day, $current_year));
$selected_agenda = $agendaByDate[$selected_date_full] ?? [];

// UNTUK PROFIL.PHP - TENTUKAN HALAMAN ASAL
$from_param = 'kalender guru'; 
$_GET['from'] = $from_param;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>Kalender | OrtuConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
   <link rel="stylesheet" href="kalender.css?v=1.1">
    <link rel="stylesheet" href="../profil/profil.css" />
    <link rel="stylesheet" href="../guru/sidebar.css" />
</head>

<body>
    <div class="d-flex wrapper-container">
        <?php include '../guru/sidebar.php'; ?>

        <div class="flex-grow-1 main-content kalender-bg">
            <div class="container-fluid py-3">

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 header-fixed-custom">
                    <h4 class="fw-bold text-primary m-0">Kalender</h4>
                    <div class="profile-section-mobile">
                        <?php include '../profil/profil.php'; ?>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-7 col-lg-12 mb-3">
                        <div class="card shadow-sm border-0 p-3 p-md-4 kalender-card-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold m-0"><?= htmlspecialchars(date('F', $first_day_of_month), ENT_QUOTES) ?> <?= (int)$current_year ?></h5>
                                <div class="kalender-nav-buttons">
                                    <a href="kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=prev&day=<?= $selected_day ?>" class="nav-arrow me-2"><i class="bi bi-chevron-left"></i></a>
                                    <a href="kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=next&day=<?= $selected_day ?>" class="nav-arrow"><i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>

                            <div class="kalender-grid">
                                <div class="hari-header minggu">Min</div>
                                <div class="hari-header">Sen</div>
                                <div class="hari-header">Sel</div>
                                <div class="hari-header">Rab</div>
                                <div class="hari-header">Kam</div>
                                <div class="hari-header">Jum</div>
                                <div class="hari-header">Sab</div>

                                <?php
                                // TAMPILKAN TANGGAL KOSONG
                                $day_counter = 1;
                                for ($i = 0; $i < $day_of_week; $i++) {
                                    echo "<div class='tanggal-kosong'></div>";
                                    $day_counter++;
                                }

                                // TAMPILKAN SEMUA HARI
                                for ($day = 1; $day <= $number_of_days; $day++) {
                                    $date_string = date('Y-m-d', mktime(0, 0, 0, $current_month, $day, $current_year));
                                    $is_today = ($date_string == date('Y-m-d'));
                                    $is_selected = ($day == $selected_day);
                                    $has_agenda = isset($agendaByDate[$date_string]);
                                    $is_minggu = (date('w', strtotime($date_string)) == 0);

                                    $class = 'tanggal-item';
                                    if ($is_minggu) $class .= ' minggu';
                                    if ($is_today) $class .= ' today';
                                    if ($is_selected) $class .= ' selected-day';
                                    if ($has_agenda) $class .= ' has-agenda';

                                    $link = "kalender.php?month={$current_month}&year={$current_year}&day={$day}";
                                    echo "<a href='{$link}' class='{$class}'><span>{$day}</span></a>";

                                    $day_counter++;
                                }

                                // TANGGAL KOSONG AKHIR
                                while ($day_counter <= 42) {
                                    if ($day_counter % 7 == 1 && $day_counter > $number_of_days + $day_of_week) break;
                                    echo "<div class='tanggal-kosong'></div>";
                                    $day_counter++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5 col-lg-12">
                        <div class="card shadow-sm border-0 p-4 daftar-kegiatan-container">
                            <h5 class="fw-bold mb-3 text-primary">Daftar Kegiatan</h5>
                            <p class="text-muted mb-4 small">Kegiatan pada: <b><?= htmlspecialchars(date('j F Y', strtotime($selected_date_full)), ENT_QUOTES) ?></b></p>

                            <div id="daftarAgendaContent">
                                <?php if (empty($selected_agenda)): ?>
                                    <div class="alert alert-light text-center border py-4">
                                        <i class="bi bi-calendar-x text-muted mb-2 d-block fs-2"></i>
                                        Tidak ada agenda.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($selected_agenda as $kegiatan): ?>
                                        <div class="kegiatan-item d-flex justify-content-between align-items-center mb-3 p-3 border rounded bg-white shadow-xs">
                                            <div>
                                                <p class="fw-semibold m-0 text-dark">
                                                    <?= htmlspecialchars($kegiatan['nama_kegiatan'] ?? 'Tanpa Nama') ?>
                                                </p>
                                                <small class="text-muted"><?= date('j F Y', strtotime($kegiatan['tanggal'])) ?></small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#detailModal"
                                                    data-nama="<?= htmlspecialchars($kegiatan['nama_kegiatan'] ?? '-', ENT_QUOTES) ?>"
                                                    data-tanggal="<?= htmlspecialchars($kegiatan['tanggal'] ?? '-', ENT_QUOTES) ?>"
                                                    data-deskripsi="<?= htmlspecialchars($kegiatan['deskripsi'] ?? 'Tidak ada deskripsi.', ENT_QUOTES) ?>">
                                                Lihat
                                            </button>
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

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Detail Kegiatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h5 class="fw-bold" id="modal-nama">Nama Kegiatan</h5>
                    <p class="text-primary fw-semibold small mb-3" id="modal-tanggal"></p>
                    <div class="bg-light p-3 rounded-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Deskripsi</label>
                        <p id="modal-deskripsi" class="mb-0 text-dark">...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(item => {
            item.addEventListener('click', function() {
                const nama = this.getAttribute('data-nama');
                const tanggal = this.getAttribute('data-tanggal');
                const deskripsi = this.getAttribute('data-deskripsi');

                document.getElementById('modal-nama').textContent = nama;
                document.getElementById('modal-deskripsi').textContent = deskripsi;
                
                if (tanggal !== '-') {
                    const date = new Date(tanggal);
                    document.getElementById('modal-tanggal').innerHTML = '<i class="bi bi-calendar-event me-2"></i>' + date.toLocaleDateString('id-ID', {
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                    });
                }
            });
        });
    </script>
</body>
</html>