<?php
session_name('SESS_ADMIN');
session_start();

// Cek login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    exit;
}

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);
$kelas = $data['kelas'] ?? '';
$tanggal = $data['tanggal'] ?? date('Y-m-d');
$filterType = $data['filter_type'] ?? 'hari';

// Hitung rentang tanggal
function getDateRange($type, $date) {
    $dateObj = new DateTime($date);
    
    switch($type) {
        case 'minggu':
            // Mulai dari hari Senin minggu ini
            $dateObj->modify('monday this week');
            $start = $dateObj->format('Y-m-d');
            $dateObj->modify('+6 days'); // Sampai Minggu
            $end = $dateObj->format('Y-m-d');
            break;
        case 'bulan':
            // Mulai dari tanggal 1 hingga akhir bulan
            $start = $dateObj->format('Y-m-01');
            $dateObj->modify('last day of this month');
            $end = $dateObj->format('Y-m-d');
            break;
        default: // 'hari'
            $start = $date;
            $end = $date;
    }
    
    return ['start' => $start, 'end' => $end];
}

$dateRange = getDateRange($filterType, $tanggal);

// Ambil data absensi dari API untuk setiap hari dalam rentang (dengan paralel)
function getAbsensiDataRange($kelas, $startDate, $endDate) {
    $allData = [];
    $currentDate = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    
    // Siapkan semua URL terlebih dahulu
    $urls = [];
    $datesList = [];
    while ($currentDate <= $endDateObj) {
        $dateStr = $currentDate->format('Y-m-d');
        $datesList[] = $dateStr;
        $urls[] = "https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php?kelas=" . urlencode($kelas) . "&tanggal=" . urlencode($dateStr);
        $currentDate->modify('+1 day');
    }
    
    // Gunakan curl_multi untuk request paralel
    $mh = curl_multi_init();
    $handles = [];
    
    foreach ($urls as $index => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_multi_add_handle($mh, $ch);
        $handles[$index] = $ch;
    }
    
    // Eksekusi semua request secara paralel
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh, 0.1);
    } while ($running > 0);
    
    // Proses semua response
    foreach ($handles as $index => $ch) {
        $response = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_multi_remove_handle($mh, $ch);
      $ch=null;
      
        
        if ($httpCode === 200 && !empty($response)) {
            $responseData = json_decode($response, true);
            $data = $responseData['data'] ?? [];
            
            foreach ($data as $item) {
                $item['tanggal_absensi'] = $datesList[$index];
                $allData[] = $item;
            }
        }
    }
    
    curl_multi_close($mh);
    return $allData;
}

$absensiData = getAbsensiDataRange($kelas, $dateRange['start'], $dateRange['end']);

// Hitung statistik berdasarkan nama murid (kumpulkan dari semua tanggal)
$statistics = [];
if (!empty($absensiData)) {
    foreach ($absensiData as $item) {
        $namaId = $item['id_siswa'];
        if (!isset($statistics[$namaId])) {
            $statistics[$namaId] = [
                'nama' => $item['nama_siswa'],
                'Hadir' => 0,
                'Izin' => 0,
                'Sakit' => 0,
                'Alpa' => 0
            ];
        }
        $status = $item['status_absensi'] ?? '';
        if (!empty($status) && isset($statistics[$namaId][$status])) {
            $statistics[$namaId][$status]++;
        }
    }
}

// Generate HTML untuk PDF
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #0066cc;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #0066cc;
            color: white;
            padding: 10px;
            text-align: center;
            border: 1px solid #0066cc;
            font-size: 12px;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
        }
        td:first-child {
            text-align: center;
        }
        td:nth-child(2) {
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            font-weight: bold;
            background-color: #e6f2ff;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI</h1>
        <p><strong>Kelas:</strong> <?= htmlspecialchars($kelas) ?></p>
        <p><strong>Periode:</strong> 
            <?php
            $startFormatted = date('d M Y', strtotime($dateRange['start']));
            $endFormatted = date('d M Y', strtotime($dateRange['end']));
            if ($dateRange['start'] === $dateRange['end']) {
                echo $startFormatted;
            } else {
                echo $startFormatted . ' - ' . $endFormatted;
            }
            ?>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Murid</th>
                <th style="width: 15%;">Hadir</th>
                <th style="width: 15%;">Izin</th>
                <th style="width: 15%;">Sakit</th>
                <th style="width: 15%;">Alpa</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $totalHadir = 0;
            $totalIzin = 0;
            $totalSakit = 0;
            $totalAlpa = 0;
            
            foreach ($statistics as $stat):
                $totalHadir += $stat['Hadir'];
                $totalIzin += $stat['Izin'];
                $totalSakit += $stat['Sakit'];
                $totalAlpa += $stat['Alpa'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($stat['nama']) ?></td>
                    <td><?= $stat['Hadir'] ?></td>
                    <td><?= $stat['Izin'] ?></td>
                    <td><?= $stat['Sakit'] ?></td>
                    <td><?= $stat['Alpa'] ?></td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (!empty($statistics)): ?>
                <tr class="total-row">
                    <td colspan="2">TOTAL</td>
                    <td><?= $totalHadir ?></td>
                    <td><?= $totalIzin ?></td>
                    <td><?= $totalSakit ?></td>
                    <td><?= $totalAlpa ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Tanggal Cetak: <?= date('d-m-Y H:i:s') ?>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Gunakan dompdf untuk generate PDF
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Absensi_' . $kelas . '_' . date('Y-m-d') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $dompdf->output();
exit;
?>