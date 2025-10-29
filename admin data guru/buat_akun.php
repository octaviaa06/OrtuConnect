<?php
$id = $_GET['id'];
$api_url = "http://ortuconnect.atwebpages.com/api/" . $id;
file_get_contents($api_url);
header("Location: DataGuru.php");
exit;
?>
