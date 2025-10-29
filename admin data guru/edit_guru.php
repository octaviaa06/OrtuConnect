<?php
$id = $_GET['id'];
$api_get = "http://ortuconnect.atwebpages.com/api/get_guru_by_id.php?id=" . $id;
$data = json_decode(file_get_contents($api_get), true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_data = [
        'id' => $id,
        'nama_guru' => $_POST['nama_guru'],
        'nip' => $_POST['nip'],
        'alamat' => $_POST['alamat'],
        'telepon' => $_POST['telepon'],
        'email' => $_POST['email']
    ];
    $api_url = "http://ortuconnect.atwebpages.com/api/";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $update_data);
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
<title>Edit Guru</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="modal">
    <h2>Edit Data Guru</h2>
    <form method="post">
        <input type="text" name="nama_guru" value="<?= $data['nama_guru'] ?>" required>
        <input type="text" name="nip" value="<?= $data['nip'] ?>" required>
        <input type="text" name="alamat" value="<?= $data['alamat'] ?>" required>
        <input type="text" name="telepon" value="<?= $data['telepon'] ?>" required>
        <input type="email" name="email" value="<?= $data['email'] ?>" required>
        <div class="buttons">
            <a href="DataGuru.php" class="btn-batal">Batal</a>
            <button type="submit" class="btn-konfirmasi">Simpan</button>
        </div>
    </form>
</div>
</body>
</html>
