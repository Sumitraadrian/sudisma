<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$query = "SELECT nama_dosen, email, nip FROM dosen";
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
        <h2>List Dosen Penyetuju Dispensasi</h2>
        <table border="1">
            <tr>
                <th>Nama Dosen</th>
                <th>NIP</th>
                <th>Email</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['nama_dosen']; ?></td>
                <td><?= $row['nip'] ? $row['nip'] : 'N/A'; ?></td>
                <td><?= $row['email']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
