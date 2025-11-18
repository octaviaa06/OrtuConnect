<?php
session_name('SESS_ADMIN');
session_start();

header('Content-Type: application/json; charset=utf-8');

// Cek login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validasi input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid atau kosong']);
    exit;
}

if (empty($input['tanggal']) || empty($input['kelas']) || empty($input['absensi'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$tanggal = $input['tanggal'];
$kelas = $input['kelas'];
$absensi = $input['absensi'];

// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid']);
    exit;
}

// Kirim ke API
$api_url = "http://ortuconnect.atwebpages.com/api/admin/absensi.php";

// Transformasi data sesuai struktur API
$absensiData = [];
foreach ($absensi as $item) {
    $absensiData[] = [
        'id_murid' => $item['id_murid'],
        'status' => $item['status']
    ];
}

$payload = json_encode([
    'tanggal' => $tanggal,
    'kelas' => $kelas,
    'absensi' => $absensiData
]);

// Kirim ke API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if (!$response) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal terhubung ke API'
    ]);
    exit;
}

$apiResponse = json_decode($response, true);

if ($httpCode === 200 && isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
    echo json_encode([
        'status' => 'success',
        'message' => $apiResponse['message'] ?? 'Absensi berhasil disimpan'
    ]);
} else {
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error',
        'message' => $apiResponse['message'] ?? 'Gagal menyimpan absensi'
    ]);
}
?>