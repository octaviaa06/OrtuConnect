<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id_guru'] ?? '';
  $nama = $_POST['nama_guru'] ?? '';
  $nip = $_POST['nip'] ?? '';
  $alamat = $_POST['alamat'] ?? '';
  $telepon = $_POST['telepon'] ?? '';
  $email = $_POST['email'] ?? '';

  $api_url = "https://ortuconnect.atwebpages.com/api/admin/data_guru.php";
  $data = [
    'action' => 'update',
    'id_guru' => $id,
    'nama_guru' => $nama,
    'nip' => $nip,
    'alamat' => $alamat,
    'telepon' => $telepon,
    'email' => $email
  ];

  $ch = curl_init($api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  $response = curl_exec($ch);
  curl_close($ch);

  echo $response ?: "Data guru berhasil diperbarui!";
}
?>
