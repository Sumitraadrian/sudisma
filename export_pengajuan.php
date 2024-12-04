<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Ambil data pengajuan dari database
$query = "SELECT p.nama_lengkap, p.nim, p.angkatan, p.alasan, p.tanggal_pengajuan, 
          p.status, p.status_wadek, j.nama_jurusan 
          FROM pengajuan p 
          INNER JOIN jurusan j ON p.jurusan_id = j.id 
          ORDER BY j.nama_jurusan, p.tanggal_pengajuan DESC";
$result = $conn->query($query);

if (!$result) {
    die("Error: " . $conn->error);
}

// Header untuk file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=data_pengajuan_dispensasi.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output header tabel
echo "Nama Lengkap\tNIM\tAngkatan\tJurusan\tAlasan\tTanggal Pengajuan\tStatus Kajur\tStatus Wadek\n";

// Output data dari database
while ($row = $result->fetch_assoc()) {
    echo $row['nama_lengkap'] . "\t" .
         $row['nim'] . "\t" .
         $row['angkatan'] . "\t" .
         $row['nama_jurusan'] . "\t" .
         $row['alasan'] . "\t" .
         $row['tanggal_pengajuan'] . "\t" .
         $row['status'] . "\t" .
         $row['status_wadek'] . "\n";
}

$conn->close();
?>
