<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (
    empty($input['tanggal']) ||
    empty($input['kelas']) ||
    !isset($input['absensi']) ||
    !is_array($input['absensi'])
) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap!"]);
    exit;
}

// Forward ke API
$payload = [
    "tanggal" => $input['tanggal'],
    "kelas" => $input['kelas'],
    "absensi" => $input['absensi']
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://ortuconnect.pbltifnganjuk.com/api/admin/absensi.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(["status" => "error", "message" => "Gagal terhubung ke API: " . $error]);
    exit;
}

echo $response;
?>