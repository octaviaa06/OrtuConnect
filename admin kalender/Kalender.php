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
$month_name         = date('F', $first_day_of_month); // Hanya bulan, tanpa tahun
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

$data       = json_decode($response, true);
$agendaList = $data['data'] ?? [];

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

// Translate bulan ke Indonesia
$bulan_indonesia = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$month_name_id = $bulan_indonesia[$month_name] ?? $month_name;
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
                                    <a href="Kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=prev&day=<?= $selected_day ?>" class="nav-arrow me-2">&lt;</a>
                                    <a href="Kalender.php?month=<?= $current_month ?>&year=<?= $current_year ?>&nav=next&day=<?= $selected_day ?>" class="nav-arrow">&gt;</a>
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

    <div id="notifBox" class="notif" role="alert" aria-live="assertive" aria-atomic="true">
        <span id="notifMessage"></span>
    </div>

    <div class="modal fade" id="detailAgendaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detail Agenda</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <div class="modal fade" id="agendaModal" tabindex="-1" aria-labelledby="agendaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formAgenda">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agendaModalLabel">
                            <i class="bi bi-calendar-plus me-2"></i>Tambah Agenda
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const AGENDA_API = "https://ortuconnect.pbltifnganjuk.com/api/admin/agenda.php";

        // =========================================================================
        // FUNGSI NOTIFIKASI TOAST (Menggunakan CSS yang Disediakan)
        // =========================================================================
        function showNotif(message, isSuccess = true) {
            const notifBox = document.getElementById("notifBox");
            const notifMessage = document.getElementById("notifMessage");
            if (!notifBox) return;
            
            // Atur pesan dan kelas
            notifMessage.textContent = message;
            
            // 1. Reset kelas (hilangkan show, success, error)
            notifBox.classList.remove('success', 'error', 'show');

            // 2. Tentukan kelas baru
            if (isSuccess) {
                notifBox.classList.add('success');
            } else {
                notifBox.classList.add('error');
            }
            
            // 3. Tampilkan Toast (tambahkan kelas show)
            void notifBox.offsetWidth; // Memicu reflow/render
            notifBox.classList.add('show');
            
            // 4. Sembunyikan Toast setelah 3 detik
            setTimeout(() => {
                notifBox.classList.remove('show');
            }, 3000); 
        }
        // =========================================================================

        function openModalTambahAgenda() {
            document.getElementById('agendaId').value = '';
            document.getElementById('agendaNama').value = '';
            document.getElementById('agendaTanggal').value = '<?= $selected_date_full ?>';
            document.getElementById('agendaDeskripsi').value = '';
            document.getElementById('agendaModalLabel').innerHTML = '<i class="bi bi-calendar-plus me-2"></i>Tambah Agenda';
            document.getElementById('btnSimpanAgenda').innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan';
            
            // Reset validasi
            document.getElementById('formAgenda').classList.remove('was-validated');
            
            new bootstrap.Modal(document.getElementById('agendaModal')).show();
        }

        function lihatDetailAgenda(agenda) {
            document.getElementById('detailNamaKegiatan').textContent = agenda.nama_kegiatan || '-';
            document.getElementById('detailTanggal').textContent = formatTanggal(agenda.tanggal) || '-';
            document.getElementById('detailDeskripsi').textContent = agenda.deskripsi || 'Tidak ada deskripsi';
            
            new bootstrap.Modal(document.getElementById('detailAgendaModal')).show();
        }

        function formatTanggal(tanggal) {
            const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const d = new Date(tanggal);
            return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
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
                    document.getElementById('agendaModalLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Agenda';
                    document.getElementById('btnSimpanAgenda').innerHTML = '<i class="bi bi-check-circle me-1"></i> Perbarui';
                    
                    document.getElementById('formAgenda').classList.remove('was-validated');
                    
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    
                    new bootstrap.Modal(document.getElementById('agendaModal')).show();
                } else {
                    showNotif('Gagal memuat data agenda.', false);
                }
            } catch {
                showNotif("Terjadi kesalahan koneksi.", false);
            }
        }

        async function deleteAgenda(id) {
            const confirmDelete = await showConfirmModal(
                'Konfirmasi Hapus',
                'Apakah Anda yakin ingin menghapus agenda ini? Tindakan ini tidak dapat dibatalkan.',
                'Hapus',
                'Batal'
            );
            
            if (!confirmDelete) return;
            
            try {
                const res = await fetch(AGENDA_API, {
                    method: 'DELETE',
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    // Tampilkan notifikasi
                    showNotif(data.message || 'Agenda berhasil dihapus!', true);
                    
                    // Muat ulang halaman setelah notifikasi selesai ditampilkan
                    setTimeout(() => location.reload(), 3400); // 3 detik tampil + 0.4 detik transisi/animasi hilang
                } else {
                    showNotif(data.message || 'Gagal menghapus agenda.', false);
                }
            } catch {
                showNotif("Terjadi kesalahan koneksi.", false);
            }
        }

        // Fungsi helper untuk modal konfirmasi
        function showConfirmModal(title, message, confirmText, cancelText) {
            return new Promise((resolve) => {
                const existingModal = document.getElementById('confirmDeleteModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                const modalHTML = `
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-exclamation-triangle me-2"></i>${title}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-0">${message}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">
                                        <i class="bi bi-x-circle me-1"></i> ${cancelText}
                                    </button>
                                    <button type="button" class="btn btn-danger" id="confirmBtn">
                                        <i class="bi bi-trash me-1"></i> ${confirmText}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                const modalElement = document.getElementById('confirmDeleteModal');
                const modal = new bootstrap.Modal(modalElement);
                
                document.getElementById('confirmBtn').onclick = () => {
                    modal.hide();
                    resolve(true);
                };
                
                modalElement.addEventListener('hidden.bs.modal', () => {
                    modalElement.remove();
                }, { once: true });

                document.getElementById('cancelBtn').onclick = () => {
                    modal.hide();
                    resolve(false);
                };
                
                modal.show();
            });
        }

        // FUNGSI SUBMIT FORM
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
            const formData = {
                id,
                nama_kegiatan: document.getElementById('agendaNama').value.trim(),
                tanggal: document.getElementById('agendaTanggal').value,
                deskripsi: document.getElementById('agendaDeskripsi').value.trim()
            };

            if (!formData.nama_kegiatan || !formData.tanggal) {
                showNotif('Nama atau Tanggal kegiatan wajib diisi!', false);
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
                    const message = data.message || (id ? 'Agenda diperbarui!' : 'Agenda ditambahkan!');

                    if (modalInstance) {
                        modalElement.addEventListener('hidden.bs.modal', function handler() {
                            showNotif(message, true);
                            setTimeout(() => location.reload(), 3400); 
                            modalElement.removeEventListener('hidden.bs.modal', handler);
                        }, { once: true }); 

                        modalInstance.hide();
                    } else {
                        showNotif(message, true);
                        setTimeout(() => location.reload(), 3400);
                    }
                } else {
                    showNotif(data.message || 'Gagal menyimpan agenda.', false);
                }
            } catch {
                showNotif("Terjadi kesalahan koneksi.", false);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        });

    </script>
</body>
</html>