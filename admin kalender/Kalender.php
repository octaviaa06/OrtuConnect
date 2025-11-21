<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'Kalender';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

$current_month = $_GET['month'] ?? 11;
$current_year  = $_GET['year'] ?? 2025;

// Navigasi bulan (next / prev)
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
$api_agenda_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_agenda_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ch = null;

if ($httpCode !== 200 || empty($response)) {
    $response = json_encode(["data" => []]);
}

$data        = json_decode($response, true);
$agendaList  = $data['data'] ?? [];

// Kelompokkan agenda berdasarkan tanggal
$agendaByDate = [];
foreach ($agendaList as $agenda) {
    $date_key = date('Y-m-d', strtotime($agenda['tanggal']));
    $agendaByDate[$date_key][] = $agenda;
}

// Tentukan tanggal yang dipilih
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
    <link rel="stylesheet" href="kalender.css" />
    <link rel="stylesheet" href="../profil/profil.css">
</head>

<body>
    <div class="d-flex">
        <?php include '../admin/sidebar.php'; ?>
        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center;">
            <div class="container-fluid py-3">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Kalender</h4>
                   <?php include '../profil/profil.php'; ?>
                </div>

                <!-- Tombol Tambah Agenda -->
                <button class="btn btn-primary btn-lg w-100 mb-5 fw-bold" onclick="openModalTambahAgenda()" style="border-radius: 12px;">
                    <span class="me-2">+</span> Tambah Agenda
                </button>

                <div class="row">
                    <!-- Kalender -->
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

                                // Hari kosong di awal bulan
                                for ($i = 0; $i < $day_of_week; $i++) {
                                    echo "<div class='tanggal-kosong'></div>";
                                }

                                // Isi tanggal-tanggal
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

                                // Hari kosong akhir bulan
                                while ($day_counter <= 42) {
                                    if ($day_counter % 7 == 1) break;
                                    echo "<div class='tanggal-kosong'></div>";
                                    $day_counter++;
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- DAFTAR KEGIATAN -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 p-4 daftar-kegiatan-container">
                            <h5 class="fw-bold mb-3 text-primary">Agenda Kegiatan</h5>
                            <p class="text-muted mb-4">Agenda untuk tanggal: <b><?= date('j F Y', strtotime($selected_date_full)) ?></b></p>

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
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-success me-2" onclick="editAgenda(<?= $kegiatan['id'] ?? 0 ?>)">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteAgenda(<?= $kegiatan['id'] ?? 0 ?>)">Hapus</button>
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

    <!-- NOTIFIKASI -->
    <div id="notifBox" class="notif"></div>

    <!-- TAMBAH/EDIT AGENDA -->
    <div class="modal fade" id="agendaModal" tabindex="-1" aria-labelledby="agendaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formAgenda">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agendaModalLabel">Tambah Agenda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="agendaId" name="id">
                        <div class="mb-3">
                            <label for="agendaNama" class="form-label">Nama Kegiatan</label>
                            <input type="text" class="form-control" id="agendaNama" name="nama_kegiatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="agendaTanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="agendaTanggal" name="tanggal" required>
                        </div>
                        <div class="mb-3">
                            <label for="agendaDeskripsi" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="agendaDeskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanAgenda">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--  SCRIPT  -->
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const AGENDA_API = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php";

    function showNotif(message, isSuccess = true) {
        const notifBox = document.getElementById("notifBox");
        notifBox.textContent = message;
        notifBox.style.backgroundColor = isSuccess ? "#28a745" : "#dc3545";
        notifBox.style.display = "block";
        setTimeout(() => notifBox.style.display = "none", 3000);
    }

    function openModalTambahAgenda() {
        document.getElementById('agendaId').value = '';
        document.getElementById('agendaNama').value = '';
        document.getElementById('agendaTanggal').value = '<?= $selected_date_full ?>';
        document.getElementById('agendaDeskripsi').value = '';
        document.getElementById('agendaModalLabel').textContent = 'Tambah Agenda';
        document.getElementById('btnSimpanAgenda').textContent = 'Simpan';
        new bootstrap.Modal(document.getElementById('agendaModal')).show();
    }

    async function editAgenda(id) {
        try {
            const res = await fetch(`${AGENDA_API}?id=${id}`);
            const data = await res.json();
            if (data.status === 'success' && data.data) {
                const a = data.data;
                document.getElementById('agendaId').value = a.id;
                document.getElementById('agendaNama').value = a.nama_kegiatan;
                document.getElementById('agendaTanggal').value = a.tanggal;
                document.getElementById('agendaDeskripsi').value = a.deskripsi || '';
                document.getElementById('agendaModalLabel').textContent = 'Edit Agenda';
                document.getElementById('btnSimpanAgenda').textContent = 'Perbarui';
                new bootstrap.Modal(document.getElementById('agendaModal')).show();
            } else showNotif('Gagal memuat data agenda.', false);
        } catch (error) {
            console.error('Edit error:', error);
            showNotif("Terjadi kesalahan koneksi.", false);
        }
    }

    async function deleteAgenda(id) {
        if (!confirm("Yakin ingin menghapus agenda ini?")) return;
        try {
            const res = await fetch(AGENDA_API, {
                method: 'DELETE',
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            console.log('Delete response:', data);
            if (data.status === 'success') {
                showNotif(data.message || 'Agenda berhasil dihapus!');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotif(data.message || 'Gagal menghapus agenda.', false);
            }
        } catch (error) {
            console.error('Delete error:', error);
            showNotif("Terjadi kesalahan koneksi.", false);
        }
    }

    // PERBAIKAN UTAMA: Form Submit Handler dengan data lengkap
    document.getElementById('formAgenda').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('agendaId').value;
        const method = id ? 'PUT' : 'POST';
        
        // Siapkan data dengan field yang lengkap
        const requestData = {
            nama_kegiatan: document.getElementById('agendaNama').value.trim(),
            tanggal: document.getElementById('agendaTanggal').value,
            deskripsi: document.getElementById('agendaDeskripsi').value.trim() || '' // Pastikan tidak null
        };

        // Jika edit, tambahkan ID
        if (id) {
            requestData.id = id;
        }

        // Validasi data
        if (!requestData.nama_kegiatan) {
            showNotif('Nama kegiatan harus diisi!', false);
            return;
        }

        if (!requestData.tanggal) {
            showNotif('Tanggal harus diisi!', false);
            return;
        }

        const btn = document.getElementById('btnSimpanAgenda');
        const originalText = btn.textContent;
        
        btn.disabled = true;
        btn.textContent = 'Loading...';

        try {
            console.log('Sending data:', requestData);

            const res = await fetch(AGENDA_API, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(requestData)
            });

            console.log('Response status:', res.status);
            const textResponse = await res.text();
            console.log('Raw response:', textResponse);

            let data;
            try {
                data = JSON.parse(textResponse);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid JSON response from server');
            }

            console.log('Parsed data:', data);

            if (data.status === 'success') {
                showNotif(data.message || (id ? 'Agenda diperbarui!' : 'Agenda ditambahkan!'));
                
                // Tutup modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('agendaModal'));
                if (modal) modal.hide();
                
                // Reload setelah delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
                
            } else {
                // Tampilkan error message yang lebih spesifik
                const errorMsg = data.message || 'Gagal menyimpan agenda.';
                showNotif(errorMsg, false);
                
                // Debug tambahan
                if (errorMsg.includes('tidak lengkap')) {
                    console.error('Data yang dikirim:', requestData);
                }
            }

        } catch (error) {
            console.error('Submit error:', error);
            showNotif("Terjadi kesalahan: " + error.message, false);
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

</script>
</body>
</html>