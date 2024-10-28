<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Dashboard Mahasiswa</h2>
    <a href="form_pengajuan.php">Ajukan Dispensasi</a> | <a href="status_pengajuan.php">Cek Status Pengajuan</a>
</body>
</html>
