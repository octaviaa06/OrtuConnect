<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_guru' => $_POST['nama_guru'],
        'nip' => $_POST['nip'],
        'alamat' => $_POST['alamat'],
        'telepon' => $_POST['telepon'],
        'email' => $_POST['email']
    ];
    $api_url = "http://ortuconnect.atwebpages.com/api/";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    header("Location: DataGuru.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Guru</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="modal">
    <h2>Tambah Guru Baru</h2>
    <form method="post">
        <input type="text" name="nama_guru" placeholder="Nama Lengkap" required>
        <input type="text" name="nip" placeholder="NIP" required>
        <input type="text" name="alamat" placeholder="Alamat Lengkap" required>
        <input type="text" name="telepon" placeholder="Nomor Telepon" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="buttons">
            <a href="DataGuru.php" class="btn-batal">Batal</a>
            <button type="submit" class="btn-konfirmasi">Konfirmasi</button>
        </div>
    </form>
</div>
</body>
</html>
