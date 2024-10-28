<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM pengajuan WHERE user_id = '$user_id'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Status Pengajuan Dispensasi</h2>
    <?php while ($row = $result->fetch_assoc()): ?>
        <p>Tanggal Pengajuan: <?= $row['tanggal_pengajuan']; ?></p>
        <p>Alasan: <?= $row['alasan']; ?></p>
        <p>Status: <?= $row['status']; ?></p>
        <hr>
    <?php endwhile; ?>
</body>
</html>
