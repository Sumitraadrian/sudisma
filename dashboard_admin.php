<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$query = "SELECT * FROM pengajuan";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="dashboard_admin.php">Daftar Pengajuan</a>
        <a href="list_angkatan.php">List Angkatan</a>
        <a href="list_dosen.php">List Dosen Penyetuju</a>
        <a href="list_tanggal.php">List Tanggal Pengajuan</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h2>Dashboard Admin - Daftar Pengajuan</h2>
        <table border="1">
            <tr>
                <th>Nama Lengkap</th>
                <th>NIM</th>
                <th>Angkatan</th>
                <th>Alasan</th>
                <th>Tanggal Pengajuan</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                <td><?= htmlspecialchars($row['nim']); ?></td>
                <td><?= htmlspecialchars($row['angkatan']); ?></td>
                <td><?= htmlspecialchars($row['alasan']); ?></td>
                <td><?= htmlspecialchars($row['tanggal_pengajuan']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                <td><a href="detail_pengajuan.php?id=<?= urlencode($row['id']); ?>">Detail</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
