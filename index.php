<?php
session_start();
include 'db.php';

// Pastikan kajur sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kajur') {
    header('Location: login.php');
    exit();
}

// Ambil jurusan_id dari dosen yang sedang login
$dosen_id = $_SESSION['dosen_id'];
$query_jurusan = "SELECT jurusan_id FROM dosen WHERE id = ?";
$stmt_jurusan = $conn->prepare($query_jurusan);
$stmt_jurusan->bind_param('i', $dosen_id);
$stmt_jurusan->execute();
$result_jurusan = $stmt_jurusan->get_result();
$jurusan_data = $result_jurusan->fetch_assoc();
$jurusan_id = $jurusan_data['jurusan_id'];

// Ambil daftar pengajuan mahasiswa berdasarkan jurusan_id
$query_pengajuan = "SELECT * FROM pengajuan WHERE jurusan_id = ?";
$stmt_pengajuan = $conn->prepare($query_pengajuan);
$stmt_pengajuan->bind_param('i', $jurusan_id);
$stmt_pengajuan->execute();
$result_pengajuan = $stmt_pengajuan->get_result();

// Tampilkan daftar pengajuan
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengajuan Mahasiswa</title>
</head>
<body>
    <h1>Daftar Pengajuan Mahasiswa</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nama Mahasiswa</th>
            <th>Jurusan</th>
            <th>Alasan</th>
            <th>Status</th>
        </tr>

        <?php while ($pengajuan = $result_pengajuan->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $pengajuan['id']; ?></td>
                <td><?php echo $pengajuan['nama_mahasiswa']; ?></td>
                <td><?php echo $pengajuan['jurusan_id']; ?></td>
                <td><?php echo $pengajuan['alasan']; ?></td>
                <td><?php echo $pengajuan['status']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
$stmt_pengajuan->close();
$stmt_jurusan->close();
$conn->close();
?>
