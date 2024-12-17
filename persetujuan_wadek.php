<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // User is not logged in
    exit();
}

if ($_SESSION['role'] !== 'wakil_dekan') {
    header('Location: index.php'); // Unauthorized access
    exit();
}
if (isset($_SESSION['nama_wadek'], $_SESSION['nip_wadek'])) {
    $namaWadek = $_SESSION['nama_wadek'];
    $nipWadek = $_SESSION['nip_wadek'];
} else {
    $namaWadek = 'Nama Wadek Tidak Ditemukan'; // Default jika tidak ada
    $nipWadek = 'NIP Tidak Ditemukan';        // Default jika tidak ada
}





require 'lib/fpdf/fpdf.php';

// Fungsi untuk menghasilkan nomor surat
function generateNomorSurat() {
    return 'DISP-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk membuat surat dispensasi
function generateSuratDispensasi($data) {
    $pdf = new FPDF();
    $pdf->AddPage('P', 'A4'); // Menambahkan halaman dengan ukuran kertas A4 (Portrait)

    // Header
    $pdf->Image('image/logouin.png', 10, 10, 20); // Menambahkan logo di kiri atas (sesuaikan path dan ukuran)
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 7, 'KEMENTERIAN AGAMA REPUBLIK INDONESIA', 0, 1, 'C');
    $pdf->Cell(0, 7, 'UNIVERSITAS ISLAM NEGERI', 0, 1, 'C');
    $pdf->Cell(0, 7, 'SUNAN GUNUNG DJATI BANDUNG', 0, 1, 'C');
    $pdf->Cell(0, 7, 'FAKULTAS SAINS DAN TEKNOLOGI', 0, 1, 'C');
    $pdf->SetFont('Times', '', 9);
    $pdf->Cell(0, 5, 'Jalan A.H. Nasution NO.105 Cibiru Bandung 40614 Telp. 022-7800526 Fax. 022-7803936 website: https//uinsgd.ac.id', 0, 1, 'C');
    $pdf->Ln(8);

    // Garis horizontal
    $pdf->SetLineWidth(0.8); // Ketebalan garis
    $pdf->Line(10, 50, 200, 50); // Koordinat garis (x1, y1, x2, y2) - sedikit diturunkan
    $pdf->Ln(12); // Jarak setelah garis horizontal

    // Nomor Surat
    // Nomor Surat
    $pdf->SetFont('Times', '', 11);

    // Nomor Surat (Kiri) dan Tanggal (Kanan)
    $pdf->Cell(120, 6, 'Nomor      : ' . $data['nomor_surat'], 0, 0, 'L'); // Nomor di kiri
    $pdf->SetX(150); // Geser posisi ke kanan untuk tanggal
    $currentDate = date('d F Y'); // Format tanggal: Hari Bulan Tahun
    $pdf->Cell(0, 6, 'Bandung, ' . $currentDate, 0, 1, 'L'); // Tanggal dan lokasi di kanan

    // Lampiran (Kiri)
    $pdf->Cell(0, 6, 'Lampiran  : -', 0, 1);

    // Perihal (Kiri)
    $pdf->Cell(0, 6, 'Perihal      : Permohonan Izin Dispensasi Perkuliahan', 0, 1);

    // Jarak untuk mulai bagian berikutnya
    $pdf->Ln(10);


    // Kepada Yth. dan isi surat dimulai sejajar dengan "Perihal"
    $pdf->SetX(30); // Set posisi X agar sejajar dengan "Perihal"
    $pdf->MultiCell(0, 6, "Kepada Yth.,\n Ibu/Bapak Dosen Mata Kuliah Jurusan" . $data['nama_jurusan'] . "\nDi\nTempat\n\n", 0);
    
    // Isi Surat
    $pdf->SetX(30);
    $pdf->SetFont('Times', '', 11);
    $pdf->MultiCell(0, 5, 
"Assalamu'alaikum Wr. Wb.

Salam silaturahmi kami sampaikan, semoga segala aktivitas yang kita lakukan senantiasa berada dalam ridho dan maghfirah Allah SWT.
Sehubungan dengan kegiatan yang sedang dilaksanakan, atas nama Dekan Fakultas, kami memberikan izin dispensasi kepada mahasiswa berikut:

Nama   : " . $data['nama_lengkap'] . "
NIM    : " . $data['nim'] . "
Prodi  : " . $data['nama_jurusan'] . "
Alasan : " . $data['alasan'] . "

Dengan ini, kami memohon kerja sama Ibu/Bapak untuk memberikan dispensasi kepada mahasiswa tersebut agar tidak mengikuti perkuliahan pada waktu yang bersamaan dengan kegiatan tersebut.

Surat ini disampaikan sebagai bentuk pemberitahuan dan persetujuan resmi dari pihak fakultas. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.

Wassalamu'alaikum Wr. Wb.", 0);

    // Footer - Tanda Tangan
    $pdf->Ln(10); // Menambahkan jarak untuk tanda tangan

    // Mengetahui (di atas Dekan,)
    $pdf->SetX(140); // Set posisi ke kanan
    $pdf->Cell(0, 6, 'Mengetahui', 0, 1, 'L');

    // Dekan (di bawah Mengetahui)
    $pdf->SetX(140); // Geser lebih kanan
    $pdf->Cell(0, 6, 'Dekan,', 0, 1, 'L');

    // Foto Dekan (di bawah Dekan,)
    $pdf->Ln(5); // Menambahkan jarak sebelum gambar
    $pdf->SetX(140); // Posisi gambar sesuai dengan teks
    $pdf->Image('image/tandatangandekan.png', 140, $pdf->GetY()- 10, 60); // Sesuaikan path gambar

    // Nama Dekan (di bawah foto)
    $pdf->Ln(20); // Menambahkan jarak setelah gambar
    $pdf->SetX(140); // Geser lebih kanan
    $pdf->Cell(0, 6, 'Dr. Hasniah Aliah, M.,Si', 0, 1, 'L');

    // NIP Dekan (di bawah nama Dekan)
    $pdf->SetX(140); // Geser lebih kanan
    $pdf->Cell(0, 6, 'NIP: 19780613 200501 2 014', 0, 1, 'L');


    // Simpan File
    $filename = "surat_dispensasi/Surat_Dispensasi_{$data['nama_lengkap']}_{$data['id']}.pdf";
    $pdf->Output('F', $filename);
    return $filename;
}






// Sertakan file PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk mengirim email konfirmasi
function sendApprovalEmail($email, $nama_lengkap, $id_pengajuan, $nim, $alasan, $filePath, $dosen_emails) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Alamat SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'adriansyahsumitra@gmail.com'; // Alamat email Anda
        $mail->Password = 'kivu njcw rcam nkwl'; // Kata sandi email Anda
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 0; // Gunakan level debug 3 atau 4 untuk output lebih terperinci

        // Pengaturan penerima
        $mail->setFrom('adriansyahsumitra@gmail.com', 'Info Dispensasi Saintek');
        $mail->addAddress($email);

        // Menambahkan lampiran file
        $mail->addAttachment($filePath);


        // Konten email
        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Persetujuan Pengajuan Dispensasi";
        $mail->Body = "
        Halo $nama_lengkap,<br><br>
            Pengajuan dispensasi Anda telah disetujui. Berikut adalah detail pengajuan Anda:<br><br>
            <strong>ID Pengajuan:</strong> $id_pengajuan<br>
            <strong>Nama:</strong> $nama_lengkap<br>
            <strong>NIM:</strong> $nim<br>
            <strong>Email:</strong> $email<br>
            <strong>Alasan:</strong> $alasan<br><br>
            Surat dispensasi terlampir pada email ini.<br><br>
            Terima kasih.
        ";

        $mail->send();
        echo "Email berhasil dikirim ke mahasiswa.";
        // Email ke Dosen Mata Kuliah
        foreach ($dosen_emails as $dosen_email) {
            $mail->clearAddresses(); // Bersihkan alamat email sebelumnya
            $mail->addAddress($dosen_email['email']);
            $mail->Subject = "Pemberitahuan Mahasiswa Dispensasi Perkuliahan";
            $mail->Body = "
            Kepada Yth. {$dosen_email['nama']},<br><br>
            Dengan hormat, kami sampaikan bahwa mahasiswa berikut telah mendapatkan izin dispensasi:<br><br>
            <strong>Nama:</strong> $nama_lengkap<br>
            <strong>NIM:</strong> $nim<br>
            <strong>Alasan:</strong> $alasan<br><br>
            Mohon dapat dimaklumi dan bekerja sama dalam proses perkuliahan mahasiswa tersebut.<br>
            Surat dispensasi terlampir pada email ini.<br><br>
            Hormat kami,<br>
            Fakultas Sains dan Teknologi
            ";
            $mail->send();
        }
    } catch (Exception $e) {
        echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
    }
}

// Cek apakah tombol "Setuju" diklik
if (isset($_POST['approve'])) {
    $status_wadek = 'disetujui final';
    $id = $_POST['id'];
    
    // Update status Wakil Dekan di database
    $query = "UPDATE pengajuan SET status_wadek = ?, tanggal_acc_wakil_dekan = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status_wadek, $id);
    $stmt->execute();

    // Ambil data pengajuan untuk email
    $query = "SELECT * FROM pengajuan WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pengajuan = $result->fetch_assoc();

    // Ambil nama dosen dan email dari tabel dosen_matkul
    $queryDosen = "SELECT nama_dosen, email_dosen FROM dosen_matkul WHERE pengajuan_id = ?";
    $stmt = $conn->prepare($queryDosen);
    $stmt->bind_param("i", $id); // Menggunakan ID pengajuan
    $stmt->execute();
    $resultDosen = $stmt->get_result();
    $dosen_emails = [];

    while ($row = $resultDosen->fetch_assoc()) {
        if (!empty($row['email_dosen'])) { // Validasi email
            $dosen_emails[] = [
                'nama' => $row['nama_dosen'],
                'email' => $row['email_dosen']
            ];
        }
    }
    // Ambil data jurusan (prodi) dari tabel `jurusan`
    $queryJurusan = "SELECT nama_jurusan FROM jurusan WHERE id = ?";
    $stmt = $conn->prepare($queryJurusan);
    $stmt->bind_param("i", $pengajuan['jurusan_id']); // Menggunakan jurusan_id dari pengajuan
    $stmt->execute();
    $resultJurusan = $stmt->get_result();
    $rowJurusan = $resultJurusan->fetch_assoc();
    $nama_jurusan = $rowJurusan['nama_jurusan'];

     // Ambil data Wakil Dekan
     $queryWadek = "SELECT nama, nip FROM wakil_dekan WHERE id = ?";
     $stmt = $conn->prepare($queryWadek);
     $stmt->bind_param("i", $pengajuan['wakil_dekan_id']);
     $stmt->execute();
     $resultWadek = $stmt->get_result();
     $rowWadek = $resultWadek->fetch_assoc();
 
     // Ambil data Ketua Jurusan
     $queryKajur = "SELECT nama_dosen, nip FROM dosen WHERE id = (SELECT ketua_jurusan_id FROM jurusan WHERE id = ?)";
     $stmt = $conn->prepare($queryKajur);
     $stmt->bind_param("i", $pengajuan['jurusan_id']);
     $stmt->execute();
     $resultKajur = $stmt->get_result();
     $rowKajur = $resultKajur->fetch_assoc();

    if ($pengajuan) {
        // Generate Nomor Surat
        $nomor_surat = generateNomorSurat();

       // Generate surat dispensasi
       $filePath = generateSuratDispensasi([
        'nomor_surat' => $nomor_surat ,
        'kepada' => $kepada,
        'nama_lengkap' => $pengajuan['nama_lengkap'],
        'nim' => $pengajuan['nim'],
        'nama_jurusan' => $nama_jurusan,
        'id' => $pengajuan['id'],
        'nama_wadek' => $namaWadek,
        'nip_wadek' => $nipWadek,
        'kajur' => $rowKajur['nama_dosen'],
        'nip_kajur' => $rowKajur['nip'],
        'alasan' => $pengajuan['alasan'],
    ]);

    // Kirim email konfirmasi ke mahasiswa
    sendApprovalEmail($pengajuan['email'], $pengajuan['nama_lengkap'], $pengajuan['id'], $pengajuan['nim'], $pengajuan['alasan'], $filePath, $dosen_emails);
    }

    // Redirect ulang halaman untuk menghindari pengiriman ulang form
    header("Location: persetujuan_wadek.php?id=$id");
    exit();
}
// Cek apakah tombol "Tolak" diklik
if (isset($_POST['reject'])) {
    $status_wadek = 'ditolak';
    $id = $_POST['id'];

    // Update status Wadek di database
    $query = "UPDATE pengajuan SET status_wadek = ?, tanggal_acc_wakil_dekan = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status_wadek, $id);
    if ($stmt->execute()) {
        // Berhasil update, redirect untuk menghindari refresh ulang form
        header("Location: persetujuan_wadek.php?id=$id");
        exit();
    } else {
        echo "Terjadi kesalahan saat menyimpan ke database.";
    }
}
// Assuming that $_SESSION['user_id'] contains the ID of the logged-in user
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Query to get the name of the Wakil Dekan
    $queryWadek = "SELECT wakil_dekan.nama AS namaWadek, wakil_dekan.nip AS nipWadek FROM wakil_dekan
                   JOIN users ON wakil_dekan.id = users.wakil_dekan_id
                   WHERE users.id = ?";
    $stmt = $conn->prepare($queryWadek);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultWadek = $stmt->get_result();

    if ($resultWadek->num_rows > 0) {
        $row = $resultWadek->fetch_assoc();
        $namaWadek = $row['namaWadek'];
        $nipWadek = $row['nipWadek'];
        $_SESSION['nama_wadek'] = $row['namaWadek'];
        $_SESSION['nip_wadek'] = $row['nipWadek'];
    } else {
        $namaWadek = "Unknown"; // Default name if not found
        $nipWadek = "Unknown";
    }

    $stmt->close();
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Dispensasi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logoweb.png">
    <style>
        body {
            background-color: #a3c1e0;
        }
        .sidebar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            z-index: 1000;
        }
        .sidebar.visible {
            transform: translateX(0);
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1050; 
        }
        
       
        .status-belum-diproses {
            background-color: orange;
        }
        .status-disetujui {
            background-color: green;
        }
        .status-ditolak {
            background-color: red;
        }
       
        .btn.approve {
            background-color: green;
            color: white;
        }
        .btn.reject {
            background-color: red;
            color: white;
        }
        .card {
            max-width: 580px;
            margin: 30px auto; /* Memberikan lebih banyak ruang di atas dan bawah */
            padding: 25px;
            background: white;
            border-radius: 10px; /* Sudut melengkung */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Bayangan lembut */
            border: 1px solid #ddd; /* Garis halus di sekitar */
            font-family: 'Roboto', sans-serif; /* Font profesional */
        }
        .card-title {
            font-size: 1.5em; /* Ukuran font judul */
            font-weight: bold; /* Cetak tebal untuk judul */
            color: #333; /* Warna teks judul */
            text-align: center;
        }

        .card-body {
            line-height: 1.6; /* Meningkatkan spasi antar baris */
            color: #555; /* Warna teks isi */
        }
        .back-button {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        /* Gaya untuk ikon lampiran */
.fas.fa-file-alt {
    color: #343a40; /* Warna ikon dokumen */
    cursor: pointer;
}

.status-badge {
    display: inline-block;  /* Ubah dari inline ke inline-block */
    padding: 3px 8px;  /* Padding lebih besar agar lebih terlihat */
    font-size: 1em;
    border-radius: 20px;
    color: white;
    font-weight: bold;
    margin: 0;
    font-size: 0.9em;
    margin-right: 1900px;
    white-space: nowrap;
    width: auto !important;  /* Menyesuaikan lebar dengan konten status */
    display: inline-block !important; /* Agar tetap inline tetapi bisa diubah ukuran dan warna */
}

.status-disetujui {
    background-color: #267739; /* Warna hijau lebih muda */
}

.status-ditolak {
    background-color: #b5364f; /* Warna merah lebih cerah */
}

.status-belum-diproses {
    background-color: #cc7a00; /* Warna oranye lebih cerah */
}

.container {
            max-width: 900px; /* Adjust the max width for larger screens */
            margin: 0 auto;
            padding: 70px;
        }


/* Gaya tombol aksi */
.btn-success {
    background-color: #28a745;
    border: none;
    border-radius: 20px; /* Tombol melengkung */
    padding: 8px 20px;
    font-size: 0.9em;
}

.btn-danger {
    background-color: #dc3545;
    border: none;
    border-radius: 20px; /* Tombol melengkung */
    padding: 8px 20px;
    font-size: 0.9em;
}
/* CSS untuk mengatur layout data dispensasi */
.data-list {
    display: flex;
    flex-direction: column;
    gap: 10px; /* Memberikan jarak antara setiap item */
}

.data-list p {
    display: flex;
    align-items: center; /* Menyelaraskan vertikal */
    justify-content: space-between;
    margin: 0;
    border-bottom: 1px solid #eee; /* Garis pembatas antar item */
    padding-bottom: 8px;
    margin-bottom: 10px;
}
.data-list p:last-child {
    border-bottom: none; /* Menghilangkan garis di item terakhir */
}

.data-list p strong {
    flex: 0 0 42%; /* Menentukan lebar label di sisi kiri */
}

.data-list p span, .data-list p a {
    width: 60%; /* Menentukan lebar nilai di sisi kanan */
}
 /* Responsiveness */
 @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
            #sidebarToggle {
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <button class="btn me-3" id="sidebarToggle" style="background-color: transparent; border: none;">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand text-black" href="#">SUDISMA</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <!-- Tambahkan menu lain di sini jika diperlukan -->
                </ul>
            </div>
        </div>
    </nav>


    <!-- Sidebar -->
    <div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar">
        <h4 class="text-center">SUDISMA</h4>
        
        <small class="text-muted ms-2" style="margin-top: 70px;">Menu</small>
        <nav class="nav flex-column mt-2">
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_wadek.php' ? 'active' : '' ?>" href="dashboard_wadek.php" style="color: <?= $currentPage == 'dashboard_wadek.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengajuan_wadek.php' ? 'active' : '' ?>" href="pengajuan_wadek.php" style="color: <?= $currentPage == 'pengajuan_wadek.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Dispensasi
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'dataTolak_wadek.php' ? 'active' : '' ?>" href="dataTolak_wadek.php" style="color: <?= $currentPage == 'dataTolak_wadek.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Data Ditolak
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'riwayat_wadek.php' ? 'active' : '' ?>" href="riwayat_wadek.php" style="color: <?= $currentPage == 'riwayat_wadek.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Riwayat Pengajuan
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengaturan_wadek.php' ? 'active' : '' ?>" href="pengaturan_wadek.php" style="color: <?= $currentPage == 'pengaturan_wadek.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Pengaturan Akun
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="logout.php" style="color: black;">
                <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
            </a>
        </nav>

        <!-- Menampilkan nama Wakil Dekan di bagian bawah sidebar -->
        <div class="mt-auto text-left p-3" style="background-color: #ffffff; color: black;">
            <small>Logged in as: <br><strong><?php echo $namaWadek; ?></strong></small>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="card shadow-sm border-0">
                <h3 class="card-title text-center mb-3">List Data Dispensasi</h3>
                <div class="card-body">
                    <div class="data-list">
                        <p>
                            <strong>Nama:</strong> 
                            <span><?= htmlspecialchars($pengajuan['nama_lengkap']); ?></span>
                        </p>
                        <p>
                            <strong>NIM:</strong> 
                            <span><?= htmlspecialchars($pengajuan['nim']); ?></span>
                        </p>
                        <p>
                            <strong>Angkatan:</strong> 
                            <span><?= htmlspecialchars($pengajuan['angkatan']); ?></span>
                        </p>
                        <p>
                            <strong>Tanggal Awal Pengajuan:</strong> 
                            <span><?= htmlspecialchars($pengajuan['tanggal_pengajuan']); ?></span>
                        </p>
                        <p>
                            <strong>Tanggal Akhir Pengajuan:</strong> 
                            <span><?= htmlspecialchars($pengajuan['akhir_pengajuan']); ?></span>
                        </p>
                        <p>
                            <strong>Alasan:</strong> 
                            <span><?= htmlspecialchars($pengajuan['alasan']); ?></span>
                        </p>
                        <p>
                            <strong>Email:</strong> 
                            <span><?= htmlspecialchars($pengajuan['email']); ?></span>
                        </p>
                        <p>
                            <strong>Lampiran Dokumen:</strong>
                            <span>
                                <?php if (!empty($pengajuan['dokumen_lampiran'])): ?>
                                    <a href="uploads/<?= $pengajuan['dokumen_lampiran']; ?>" target="_blank" style="color: black;">
                                        <i class="bi bi-file-earmark-text" style="font-size: 1.5rem;"></i>
                                    </a>
                                <?php else: ?>
                                    Tidak ada
                                <?php endif; ?>
                            </span>
                        </p>
                        <p>
                            <strong>Status Kajur:</strong>
                            <span>
                                <span class="status-badge 
                                    <?= $pengajuan['status'] == 'disetujui' ? 'status-disetujui' : 
                                        ($pengajuan['status'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diproses'); ?>">
                                    <?= ($pengajuan['status'] == 'disetujui') ? 'Disetujui' : 
                                        (($pengajuan['status'] == 'ditolak') ? 'Ditolak' : 'Belum Diproses'); ?>
                                </span>
                            </span>
                        </p>
                        <p>
                            <strong>Status Wadek:</strong>
                            <span>
                                <span class="status-badge 
                                    <?= $pengajuan['status_wadek'] == 'disetujui final' ? 'status-disetujui' : 
                                        ($pengajuan['status_wadek'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diproses'); ?>">
                                    <?= ($pengajuan['status_wadek'] == 'disetujui final') ? 'Disetujui' : 
                                        (($pengajuan['status_wadek'] == 'ditolak') ? 'Ditolak' : 'Belum Diproses'); ?>
                                </span>
                            </span>
                        </p>
                        <p>
                            <strong>Aksi:</strong>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
                                <input type="hidden" name="email" value="<?= $pengajuan['email']; ?>">
                                <input type="hidden" name="nama_lengkap" value="<?= $pengajuan['nama_lengkap']; ?>">
                                <input type="hidden" name="nim" value="<?= $pengajuan['nim']; ?>">
                                <input type="hidden" name="alasan" value="<?= $pengajuan['alasan']; ?>">
                                <div class="d-flex gap-2">
                                    <form method="POST" action="generate_surat.php">
                                        <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
                                        <button type="submit" name="approve" class="btn btn-success">Setuju</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
                                        <button type="submit" name="reject" class="btn btn-danger">Tolak</button>
                                    </form>
                                </div>
                            </form>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
         document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('visible');
        });
        document.querySelectorAll('.btn-success').forEach(function(button) {
            button.addEventListener('click', function(event) {
                var id = button.getAttribute('id').replace('approveBtn', '');
                var rejectButton = document.getElementById('rejectBtn' + id);
                if (rejectButton) {
                    rejectButton.style.display = 'none'; // Hide the "Tolak" button
                }
            });
        });
    </script>
</body>
</html>
