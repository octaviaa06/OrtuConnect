<?php
// MULAI SESSION UNTUK GURU
session_name('SESS_GURU');
session_start();
$active_page = 'kalender guru';

// CEK APAKAH SUDAH LOGIN SEBAGAI GURU
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../login/index.php?error=Harap login sebagai guru!");
    exit;
}

// ====== LOGIKA KALENDER ======
// AMBIL BULAN DAN TAHUN SAAT INI ATAU DARI URL
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
$ch = null;

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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kalender | OrtuConnect</title>
    <!-- LOAD BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- LOAD CUSTOM CSS -->
    <link rel="stylesheet" href="kalender.css" />
    <link rel="stylesheet" href="../profil/profil.css" />
    <link rel="stylesheet" href="../guru/sidebar.css" />
</head>

<body>
    <div class="d-flex">
        <!-- SIDEBAR GURU -->
        <?php include '../guru/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <div class="flex-grow-1 main-content kalender-bg">
            <div class="container-fluid py-3">

                <!-- HEADER DENGAN PROFIL -->
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Kalender</h4>
                    <?php include '../profil/profil.php'; ?>
                </div>

                <div class="mb-5"></div>

                <!-- KONTEN KALENDER -->
                <div class="row">
                    <!-- KALENDER BULANAN -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 p-4 kalender-container">
                            <!-- HEADER KALENDER DENGAN NAVIGASI -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold m-0"><?= htmlspecialchars(date('F', $first_day_of_month), ENT_QUOTES) ?> <?= (int)$current_year ?></h5>
                                <div class="kalender-nav-buttons">
                                    <a href="kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=prev&day=<?= $selected_day ?>" class="nav-arrow me-2"><</a>
                                    <a href="kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=next&day=<?= $selected_day ?>" class="nav-arrow">></a>
                                </div>
                            </div>

                            <!-- GRID KALENDER -->
                            <div class="kalender-grid">
                                <!-- HEADER HARI -->
                                <div class="hari-header minggu">Minggu</div>
                                <div class="hari-header">Senin</div>
                                <div class="hari-header">Selasa</div>
                                <div class="hari-header">Rabu</div>
                                <div class="hari-header">Kamis</div>
                                <div class="hari-header">Jumat</div>
                                <div class="hari-header">Sabtu</div>

                                <?php
                                // TAMPILKAN TANGGAL KOSONG UNTUK HARI SEBELUM BULAN BERJALAN
                                $day_counter = 1;
                                for ($i = 0; $i < $day_of_week; $i++) echo "<div class='tanggal-kosong'></div>";

                                // TAMPILKAN SEMUA HARI DALAM BULAN
                                for ($day = 1; $day <= $number_of_days; $day++) {
                                    $date_string = date('Y-m-d', mktime(0, 0, 0, $current_month, $day, $current_year));
                                    $is_today = ($date_string == date('Y-m-d'));
                                    $is_selected = ($day == $selected_day);
                                    $has_agenda = isset($agendaByDate[$date_string]);

                                    // CEK APAKAH HARI MINGGU
                                    $date_obj = new DateTime($date_string);
                                    $day_of_week_num = (int)$date_obj->format('w'); // 0 = Minggu
                                    $is_minggu = ($day_of_week_num === 0);

                                    // TENTUKAN CLASS CSS
                                    $class = 'tanggal-item';
                                    if ($is_minggu) $class .= ' minggu'; // WARNA BEDA UNTUK MINGGU
                                    if ($is_today) $class .= ' today';   // HIGHLIGHT HARI INI
                                    if ($is_selected) $class .= ' selected-day'; // HARI YANG DIPILIH
                                    if ($has_agenda) $class .= ' has-agenda';    // ADA AGENDA

                                    $link = "kalender.php?month={$current_month}&year={$current_year}&day={$day}";
                                    echo "<a href='{$link}' class='{$class}' data-date='{$date_string}'><span>{$day}</span></a>";

                                    $day_counter++;
                                }

                                // TAMPILKAN TANGGAL KOSONG SETELAH BULAN BERAKHIR
                                while ($day_counter <= 42) {
                                    if ($day_counter % 7 == 1) break;
                                    echo "<div class='tanggal-kosong'></div>";
                                    $day_counter++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- DAFTAR KEGIATAN UNTUK TANGGAL TERPILIH -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 p-4 daftar-kegiatan-container">
                            <h5 class="fw-bold mb-3 text-primary">Daftar Kegiatan</h5>
                            <p class="text-muted mb-4">Kegiatan untuk tanggal: <b><?= htmlspecialchars(date('j F Y', strtotime($selected_date_full)), ENT_QUOTES) ?></b></p>

                            <!-- KONTEN AGENDA -->
                            <div id="daftarAgendaContent">
                                <?php if (empty($selected_agenda)): ?>
                                    <div class="alert alert-info text-center">Tidak ada agenda pada tanggal ini.</div>
                                <?php else: ?>
                                    <?php foreach ($selected_agenda as $kegiatan): ?>
                                        <div class="kegiatan-item d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                            <div>
                                                <p class="fw-semibold m-0">
                                                    <!-- LINK UNTUK MODAL DETAIL -->
                                                    <a href="#" 
                                                       class="text-decoration-none text-dark"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#detailModal"
                                                       data-id="<?= (int)$kegiatan['id'] ?>"
                                                       data-nama="<?= htmlspecialchars($kegiatan['nama_kegiatan'] ?? '-', ENT_QUOTES) ?>"
                                                       data-tanggal="<?= htmlspecialchars($kegiatan['tanggal'] ?? '-', ENT_QUOTES) ?>"
                                                       data-deskripsi="<?= htmlspecialchars($kegiatan['deskripsi'] ?? 'Tidak ada deskripsi.', ENT_QUOTES) ?>">
                                                        <?= htmlspecialchars($kegiatan['nama_kegiatan'] ?? 'Kegiatan Tanpa Nama') ?>
                                                    </a>
                                                </p>
                                                <small class="text-muted"><?= date('j F Y', strtotime($kegiatan['tanggal'] ?? $selected_date_full)) ?></small>
                                            </div>
                                            <!-- TOMBOL LIHAT DETAIL -->
                                            <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#detailModal"
                                                    data-id="<?= (int)$kegiatan['id'] ?>"
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

    <!-- MODAL DETAIL KEGIATAN -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="detailModalLabel">Detail Kegiatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5 class="fw-bold" id="modal-nama">Nama Kegiatan</h5>
                    <p class="text-muted mb-3" id="modal-tanggal">10 November 2025</p>
                    <div>
                        <label class="form-label fw-bold">Deskripsi</label>
                        <p id="modal-deskripsi" class="mb-0">...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOAD BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ISI MODAL DENGAN DATA KEGIATAN
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // AMBIL DATA DARI ATTRIBUTE
                const nama = this.getAttribute('data-nama') || '—';
                const tanggal = this.getAttribute('data-tanggal') || '—';
                const deskripsi = this.getAttribute('data-deskripsi') || 'Tidak ada deskripsi.';

                // ISI DATA KE MODAL
                document.getElementById('modal-nama').textContent = nama;
                if (tanggal !== '—') {
                    const date = new Date(tanggal);
                    document.getElementById('modal-tanggal').textContent = 
                        date.toLocaleDateString('id-ID', {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                } else {
                    document.getElementById('modal-tanggal').textContent = '—';
                }
                document.getElementById('modal-deskripsi').textContent = deskripsi;
            });
        });

        // HANDLE SCROLL PADA MODAL UNTUK MOBILE
        const detailModal = document.getElementById('detailModal');
        if (detailModal) {
            detailModal.addEventListener('show.bs.modal', function () {
                if (window.innerWidth <= 992) {
                    document.body.style.overflow = 'auto';
                }
            });
            detailModal.addEventListener('hidden.bs.modal', function () {
                if (window.innerWidth <= 992) {
                    document.body.style.overflow = '';
                }
            });
        }
    </script>
</body>
</html>