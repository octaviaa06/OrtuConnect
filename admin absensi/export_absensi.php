<?php
require_once __DIR__ . '/vendor/autoload.php'; // pastikan kamu sudah install library composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

$kelas = $_GET['kelas'] ?? '';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'pdf';

if (!$kelas) {
    die("Kelas tidak ditemukan.");
}

// 🔹 Ambil data absensi dari API
$api_url = "https://ortuconnect.atwebpages.com/api/admin/absensi.php?kelas=" . urlencode($kelas) . "&tanggal=" . urlencode($tanggal);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$absensiList = $data['data'] ?? [];

if (empty($absensiList)) {
    die("Tidak ada data absensi untuk kelas ini.");
}

// 🔹 Buat judul umum
$judul = "Laporan Absensi Kelas $kelas - Tanggal " . date("d/m/Y", strtotime($tanggal));


// =========================================================
// 🧾 EKSPOR PDF
// =========================================================
if ($type === 'pdf') {
    $html = "
    <style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    h2 { text-align: center; color: #0d6efd; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background-color: #0d6efd; color: white; }
    </style>
    <h2>$judul</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Murid</th>
                <th>Status Absensi</th>
            </tr>
        </thead>
        <tbody>";
    $no = 1;
    foreach ($absensiList as $abs) {
        $html .= "
        <tr>
            <td>{$no}</td>
            <td>" . htmlspecialchars($abs['nama_murid']) . "</td>
            <td>" . htmlspecialchars($abs['status_absensi']) . "</td>
        </tr>";
        $no++;
    }
    $html .= "</tbody></table>";

    // 🔹 Gunakan Dompdf untuk ekspor PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Absensi_Kelas_{$kelas}_{$tanggal}.pdf", ["Attachment" => true]);
    exit;
}


// =========================================================
// 📊 EKSPOR EXCEL
// =========================================================
elseif ($type === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $sheet->setCellValue('A1', 'LAPORAN ABSENSI');
    $sheet->mergeCells('A1:C1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->setCellValue('A2', $judul);
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A4', 'No');
    $sheet->setCellValue('B4', 'Nama Murid');
    $sheet->setCellValue('C4', 'Status Absensi');
    $sheet->getStyle('A4:C4')->getFont()->setBold(true);

    // Isi data
    $row = 5;
    $no = 1;
    foreach ($absensiList as $abs) {
        $sheet->setCellValue("A$row", $no++);
        $sheet->setCellValue("B$row", $abs['nama_murid']);
        $sheet->setCellValue("C$row", $abs['status_absensi']);
        $row++;
    }

    // Styling dasar
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getStyle('A4:C' . ($row - 1))
          ->getBorders()->getAllBorders()
          ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Output file Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=Absensi_Kelas_{$kelas}_{$tanggal}.xlsx");
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

else {
    die("Format ekspor tidak dikenal.");
}
?>
