<?php

session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php"); 
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
$initial = strtoupper(substr($username, 0, 1));

?>