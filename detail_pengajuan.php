<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'db.php';

// Sertakan file PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk mengirim email konfirmasi
function sendApprovalEmail($email, $nama_lengkap) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Alamat SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'adriansyahsumitra@gmail.com'; // Alamat email Anda
        $mail->Password = ''; // Kata sandi email Anda
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 3; // Gunakan level debug 3 atau 4 untuk output lebih terperinci


        // Pengaturan penerima
        $mail->setFrom('adriansyahsumitra@gmail.com', 'Admin');
        $mail->addAddress($email);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Persetujuan Pengajuan Dispensasi";
        $mail->Body = "Halo $nama_lengkap,<br><br>Pengajuan dispensasi Anda telah disetujui. Anda dapat mengunduh surat dispensasi di akun Anda.<br><br>Terima kasih.";

        $mail->send();
        echo "Email berhasil dikirim.";
    } catch (Exception $e) {
        echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
    }
}

// Cek apakah tombol "Setuju" atau "Tolak" diklik
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $status = isset($_POST['approve']) ? 'disetujui' : 'ditolak';
    $id = $_POST['id'];
    
    // Update status di database
    $query = "UPDATE pengajuan SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Jika disetujui, kirimkan email konfirmasi
    if ($status == 'disetujui') {
        $email = $_POST['email'];
        $nama_lengkap = $_POST['nama_lengkap'];
        sendApprovalEmail($email, $nama_lengkap);
    }

    // Redirect ulang halaman untuk menghindari pengiriman ulang form
    header("Location: detail_pengajuan.php?id=$id");
    exit();
}

// Pastikan ID pengajuan ada di URL dan valid
if (!isset($_GET['id'])) {
    echo "ID pengajuan tidak ditemukan.";
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM pengajuan WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pengajuan = $result->fetch_assoc();

if (!$pengajuan) {
    echo "Pengajuan tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pengajuan</title>
    <link rel="stylesheet" href="css/detail.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h3>List Data Dispensasi</h3>
            <p><strong>Nama:</strong> <?= htmlspecialchars($pengajuan['nama_lengkap']); ?></p>
            <p><strong>NIM:</strong> <?= htmlspecialchars($pengajuan['nim']); ?></p>
            <p><strong>Angkatan:</strong> <?= htmlspecialchars($pengajuan['angkatan']); ?></p>
            <p><strong>Tanggal Pengajuan:</strong> <?= htmlspecialchars($pengajuan['tanggal_pengajuan']); ?></p>
            <p><strong>Alasan:</strong> <?= htmlspecialchars($pengajuan['alasan']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($pengajuan['email']); ?></p>
            <p><strong>Lampiran Dokumen:</strong> <?= $pengajuan['dokumen_lampiran'] ? '<a href="uploads/'.$pengajuan['dokumen_lampiran'].'">Lihat Dokumen</a>' : 'Tidak ada'; ?></p>
            <p><strong>Status:</strong> <span class="status <?= $pengajuan['status'] == 'disetujui' ? 'approved' : ($pengajuan['status'] == 'ditolak' ? 'rejected' : 'pending'); ?>">
                <?= htmlspecialchars($pengajuan['status']); ?>
            </span></p>
            
            <!-- Tampilkan surat dispensasi jika disetujui -->
            <?php if ($pengajuan['status'] == 'disetujui'): ?>
                <p><strong>Surat Dispensasi:</strong> <a href="surat_dispensasi.php?id=<?= $pengajuan['id']; ?>">Unduh Surat Dispensasi</a></p>
            <?php endif; ?>

            <!-- Form untuk Setuju / Tolak -->
            <form method="post">
                <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
                <input type="hidden" name="email" value="<?= $pengajuan['email']; ?>">
                <input type="hidden" name="nama_lengkap" value="<?= $pengajuan['nama_lengkap']; ?>">
                <div class="action-buttons">
                    <button type="submit" name="approve" class="btn approve">Setuju</button>
                    <button type="submit" name="reject" class="btn reject">Tolak</button>
                </div>
            </form>
        </div>
    </div>
    <a href="dashboard_admin.php" class="back-button">Kembali ke Dashboard</a>
</body>
</html>
