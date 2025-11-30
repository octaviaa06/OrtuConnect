<?php
session_name('SESS_ADMIN');
session_start();
$active_page = 'kalender';

// Cek autentikasi dan peran admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php?error=Harap login sebagai admin!");
    exit;
}

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

// Fungsi Helper untuk Fetch API
function fetchApiData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return [];
}

// Hitung komponen tanggal untuk kalender
$first_day_of_month = mktime(0, 0, 0, $current_month, 1, $current_year);
$number_of_days     = date('t', $first_day_of_month);
$date_components    = getdate($first_day_of_month);
$month_name         = date('F', $first_day_of_month);
$day_of_week        = $date_components['wday'];

// Panggil API untuk mendapatkan agenda
$api_agenda_url = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php?month={$current_month}&year={$current_year}";
$data = fetchApiData($api_agenda_url);
$agendaList = $data['data'] ?? [];

// Kelompokkan agenda berdasarkan tanggal
$agendaByDate = [];
foreach ($agendaList as $agenda) {
    $date_key = date('Y-m-d', strtotime($agenda['tanggal']));
    $agendaByDate[$date_key][] = $agenda;
}

// Tentukan tanggal yang dipilih
$default_day = (date('Y') == $current_year && date('n') == $current_month) ? date('j') : 1;
$selected_day = $_GET['day'] ?? $default_day;
$selected_date_full = date('Y-m-d', mktime(0, 0, 0, $current_month, $selected_day, $current_year));
$selected_agenda    = $agendaByDate[$selected_date_full] ?? [];

// Translate bulan ke Indonesia
$bulan_indonesia = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$month_name_id = $bulan_indonesia[$month_name] ?? $month_name;
$from_param = 'kalender';
$_GET['from'] = $from_param;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kalender | OrtuConnect</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="kalender.css" />
    <link rel="stylesheet" href="../profil/profil.css">
    <link rel="stylesheet" href="../admin/sidebar.css">
</head>

<body>
    <div class="d-flex">
        <?php include '../admin/sidebar.php'; ?>
        <div class="flex-grow-1 main-content" style="background-image:url('../background/Data Guru(1).png'); background-size:cover; background-position:center; background-attachment: fixed;">
            <div class="container-fluid py-3 py-md-4">

                <div class="d-flex justify-content-between align-items-center mb-4 header-fixed">
                    <h4 class="fw-bold text-primary m-0">Kalender</h4>
                    <?php include '../profil/profil.php'; ?>
                </div>

                <button class="btn btn-primary btn-lg w-100 mb-4 fw-bold btn-tambah-agenda" onclick="openModalTambahAgenda()">
                    <span class="me-2">+</span> Tambah Agenda
                </button>

                <div class="row g-4">
                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm border-0 p-3 p-md-4 kalender-container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold m-0"><?= $month_name_id ?> <?= $current_year ?></h5>
                                <div class="kalender-nav-buttons">
                                    <a href="Kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=prev&day=<?= $selected_day ?>" class="nav-arrow me-2" title="Bulan Sebelumnya">&lt;</a>
                                    <a href="Kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=next&day=<?= $selected_day ?>" class="nav-arrow" title="Bulan Berikutnya">&gt;</a>
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
                                    if (($day_of_week + $day - 1) % 7 == 0) $class .= ' minggu';
                                    if ($is_today) $class .= ' today';
                                    if ($is_selected) $class .= ' selected-day';
                                    if ($has_agenda) $class .= ' has-agenda';

                                    $link = "Kalender.php?month={$current_month}&year={$current_year}&day={$day}";

                                    echo "<a href='{$link}' class='{$class}' data-date='{$date_string}'><span>{$day}</span></a>";
                                }

                                // Hari kosong akhir bulan
                                $cells_after = ($day_of_week + $number_of_days) % 7;
                                if ($cells_after > 0) {
                                    for ($i = $cells_after; $i < 7; $i++) {
                                        echo "<div class='tanggal-kosong'></div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm border-0 p-3 p-md-4 daftar-kegiatan-container">
                            <h5 class="fw-bold mb-3 text-primary">Agenda Kegiatan</h5>
                            <p class="text-muted mb-4">Agenda untuk tanggal: <b><?= date('j F Y', strtotime($selected_date_full)) ?></b></p>

                            <div id="daftarAgendaContent">
                                <?php if (empty($selected_agenda)): ?>
                                    <div class="alert alert-info text-center rounded-3">
                                        <i class="bi bi-calendar-x"></i> Tidak ada agenda pada tanggal ini.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($selected_agenda as $kegiatan): ?>
                                        <div class="kegiatan-item d-flex justify-content-between align-items-center mb-3 p-3 border rounded" 
                                            onclick="lihatDetailAgenda(<?= htmlspecialchars(json_encode($kegiatan), ENT_QUOTES, 'UTF-8') ?>)">
                                            <div class="flex-grow-1">
                                                <p class="fw-semibold m-0 text-dark"><?= htmlspecialchars($kegiatan['nama_kegiatan'] ?? 'Kegiatan Tanpa Nama') ?></p>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3"></i> 
                                                    <?= date('j F Y', strtotime($kegiatan['tanggal'] ?? $selected_date_full)) ?>
                                                </small>
                                            </div>
                                            <div class="action-buttons" onclick="event.stopPropagation()">
                                                <button class="btn btn-sm btn-success me-2" onclick="editAgenda(<?= $kegiatan['id'] ?? 0 ?>)">
                                                    <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Edit</span>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteAgenda(<?= $kegiatan['id'] ?? 0 ?>)">
                                                    <i class="bi bi-trash"></i> <span class="d-none d-md-inline">Hapus</span>
                                                </button>
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

    <!-- Modal Notifikasi Success -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content success-modal-content">
                <div class="modal-body text-center p-4 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="success-checkmark mb-3">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle class="checkmark-circle" cx="40" cy="40" r="38" fill="none" stroke="#a8d5a8" stroke-width="3"/>
                            <path class="checkmark-check" fill="none" stroke="#5cb85c" stroke-width="4" stroke-linecap="round" d="M20,42 L32,54 L60,26"/>
                        </svg>
                    </div>
                    <h5 class="fw-bold mb-2" style="color: #4a4a4a;" id="successTitle">Berhasil menambah data!</h5>
                    <p class="text-muted mb-4" id="successMessage">Data agenda berhasil ditambahkan.</p>
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Agenda -->
    <div class="modal fade" id="detailAgendaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detail Agenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">
                            <i class="bi bi-calendar-event"></i> Nama Kegiatan
                        </label>
                        <p id="detailNamaKegiatan" class="form-control-plaintext border rounded p-3 bg-light mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">
                            <i class="bi bi-calendar3"></i> Tanggal
                        </label>
                        <p id="detailTanggal" class="form-control-plaintext border rounded p-3 bg-light mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">
                            <i class="bi bi-file-text"></i> Deskripsi
                        </label>
                        <p id="detailDeskripsi" class="form-control-plaintext border rounded p-3 bg-light mb-0" style="min-height: 80px;"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Agenda -->
    <div class="modal fade" id="agendaModal" tabindex="-1" aria-labelledby="agendaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formAgenda" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title" id="agendaModalLabel">
                            <i class="bi bi-calendar-plus me-2"></i>Tambah Agenda
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="agendaId" name="id">
                        <div class="mb-3">
                            <label for="agendaNama" class="form-label fw-semibold">
                                <i class="bi bi-calendar-event text-primary"></i> Nama Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="agendaNama" name="nama_kegiatan" required 
                                    placeholder="Masukkan nama kegiatan">
                            <div class="invalid-feedback">
                                Nama kegiatan wajib diisi!
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="agendaTanggal" class="form-label fw-semibold">
                                <i class="bi bi-calendar3 text-primary"></i> Tanggal <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="agendaTanggal" name="tanggal" required>
                            <div class="invalid-feedback">
                                Tanggal wajib diisi!
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="agendaDeskripsi" class="form-label fw-semibold">
                                <i class="bi bi-file-text text-primary"></i> Deskripsi (Opsional)
                            </label>
                            <textarea class="form-control" id="agendaDeskripsi" name="deskripsi" rows="3" 
                                        placeholder="Tambahkan deskripsi kegiatan (opsional)"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanAgenda">
                            <i class="bi bi-check-circle me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus agenda ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const AGENDA_API = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php";
        let currentAgendaId = null;

        // Fungsi Notifikasi Success Modal
        function showSuccessModal(message, actionType = 'menambah') {
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            const titleMap = {
                'menambah': 'Berhasil menambah data!',
                'mengedit': 'Berhasil mengedit data!',
                'menghapus': 'Berhasil menghapus data!'
            };
            
            const messageMap = {
                'menambah': 'Data agenda berhasil ditambahkan.',
                'mengedit': 'Data agenda berhasil diperbarui.',
                'menghapus': 'Data agenda berhasil dihapus.'
            };

            document.getElementById('successTitle').textContent = titleMap[actionType] || titleMap['menambah'];
            document.getElementById('successMessage').textContent = message || messageMap[actionType];
            
            modal.show();
            
            // Auto close after 3 seconds and reload
            setTimeout(() => {
                modal.hide();
                setTimeout(() => location.reload(), 300);
            }, 3000);
        }

        function openModalTambahAgenda() {
            document.getElementById('agendaId').value = '';
            document.getElementById('agendaNama').value = '';
            document.getElementById('agendaTanggal').value = '<?= $selected_date_full ?>';
            document.getElementById('agendaDeskripsi').value = '';
            document.getElementById('agendaModalLabel').innerHTML = '<i class="bi bi-calendar-plus me-2"></i>Tambah Agenda';
            document.getElementById('btnSimpanAgenda').innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan';
            
            document.getElementById('formAgenda').classList.remove('was-validated');
            
            new bootstrap.Modal(document.getElementById('agendaModal')).show();
        }

        function formatTanggal(tanggal) {
            if (!tanggal) return '-';
            const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const d = new Date(tanggal);
            
            if (isNaN(d.getTime())) return tanggal; 

            return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
        }

        function lihatDetailAgenda(agenda) {
            document.getElementById('detailNamaKegiatan').textContent = agenda.nama_kegiatan || '-';
            document.getElementById('detailTanggal').textContent = formatTanggal(agenda.tanggal) || '-';
            document.getElementById('detailDeskripsi').textContent = agenda.deskripsi || 'Tidak ada deskripsi';
            
            new bootstrap.Modal(document.getElementById('detailAgendaModal')).show();
        }

        async function editAgenda(id) {
            if (!id) return alert('ID Agenda tidak valid.');
            
            try {
                const res = await fetch(`${AGENDA_API}?id=${id}`);
                const data = await res.json();
                
                if (data.status === 'success' && data.data) {
                    const a = data.data;
                    document.getElementById('agendaId').value = a.id;
                    document.getElementById('agendaNama').value = a.nama_kegiatan;
                    document.getElementById('agendaTanggal').value = a.tanggal;
                    document.getElementById('agendaDeskripsi').value = a.deskripsi || '';
                    document.getElementById('agendaModalLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Agenda';
                    document.getElementById('btnSimpanAgenda').innerHTML = '<i class="bi bi-check-circle me-1"></i> Perbarui';
                    
                    document.getElementById('formAgenda').classList.remove('was-validated');
                    
                    const detailModalEl = document.getElementById('detailAgendaModal');
                    const detailModal = bootstrap.Modal.getInstance(detailModalEl);
                    if (detailModal) detailModal.hide();

                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    
                    new bootstrap.Modal(document.getElementById('agendaModal')).show();
                } else {
                    alert('Gagal memuat data agenda: ' + (data.message || 'Data tidak ditemukan.'));
                }
            } catch (error) {
                console.error('Edit error:', error);
                alert("Terjadi kesalahan koneksi.");
            }
        }

        function deleteAgenda(id) {
            if (!id) return alert('ID Agenda tidak valid.');

            currentAgendaId = id;
            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        }

        // Event listener untuk konfirmasi hapus
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
            if (!currentAgendaId) return;
            
            try {
                const res = await fetch(AGENDA_API, {
                    method: 'DELETE',
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id: currentAgendaId })
                });
                const data = await res.json();
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                modal.hide();
                
                if (data.status === 'success') {
                    showSuccessModal(data.message || 'Data agenda berhasil dihapus.', 'menghapus');
                } else {
                    alert(data.message || 'Gagal menghapus agenda.');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert("Terjadi kesalahan koneksi.");
            }
        });

        // Reset currentAgendaId ketika modal ditutup
        document.getElementById('confirmDeleteModal').addEventListener('hidden.bs.modal', function() {
            currentAgendaId = null;
        });

        document.getElementById('formAgenda').addEventListener('submit', async e => {
            e.preventDefault();
            
            const form = e.target;
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            
            const id = document.getElementById('agendaId').value;
            const method = id ? 'PUT' : 'POST';
            const actionType = id ? 'mengedit' : 'menambah';
            const formData = {
                id,
                nama_kegiatan: document.getElementById('agendaNama').value.trim(),
                tanggal: document.getElementById('agendaTanggal').value,
                deskripsi: document.getElementById('agendaDeskripsi').value.trim()
            };

            if (!formData.nama_kegiatan || !formData.tanggal) {
                alert('Nama atau Tanggal kegiatan wajib diisi!');
                return;
            }

            const btn = document.getElementById('btnSimpanAgenda');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

            try {
                const res = await fetch(AGENDA_API, {
                    method,
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(formData)
                });
                const data = await res.json();

                if (data.status === 'success') {
                    const modalElement = document.getElementById('agendaModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    const message = data.message || (id ? 'Data agenda berhasil diperbarui.' : 'Data agenda berhasil ditambahkan.');

                    if (modalInstance) {
                        modalElement.addEventListener('hidden.bs.modal', function handler() {
                            showSuccessModal(message, actionType);
                            modalElement.removeEventListener('hidden.bs.modal', handler);
                        }, { once: true }); 

                        modalInstance.hide();
                    } else {
                        showSuccessModal(message, actionType);
                    }
                } else {
                    alert(data.message || 'Gagal menyimpan agenda.');
                }
            } catch (error) {
                console.error('Submit error:', error);
                alert("Terjadi kesalahan koneksi.");
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });
    </script>
</body>
</html>