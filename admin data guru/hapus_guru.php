<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id_guru'] ?? '';

  $api_url = "https://ortuconnect.atwebpages.com/api/admin/data_guru.php";
  $data = [
    'action' => 'delete',
    'id_guru' => $id
  ];

  $ch = curl_init($api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  $response = curl_exec($ch);
  curl_close($ch);

  echo $response ?: "Data guru berhasil dihapus!";
}
?>
