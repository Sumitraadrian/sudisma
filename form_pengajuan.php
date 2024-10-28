<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $nim = $_POST['nim'];
    $angkatan = $_POST['angkatan'];
    $jurusan = $_POST['jurusan'];
    $alasan = $_POST['alasan'];
    $tanggal_pengajuan = $_POST['tanggal_pengajuan'];
    $email = $_POST['email'];

    if (isset($_FILES['dokumen_lampiran']) && $_FILES['dokumen_lampiran']['error'] === UPLOAD_ERR_OK) {
        $lampiran_nama = $_FILES['dokumen_lampiran']['name'];
        $lampiran_tmp = $_FILES['dokumen_lampiran']['tmp_name'];
        $upload_dir = 'uploads/' . $lampiran_nama;
        move_uploaded_file($lampiran_tmp, $upload_dir);
    } else {
        $lampiran_nama = null;
    }

    // Menyusun query dengan tanggal pengajuan dari form
    $query = "INSERT INTO pengajuan (user_id, nama_lengkap, nim, angkatan, jurusan, alasan, tanggal_pengajuan, email, dokumen_lampiran) 
    VALUES ('$user_id', '$nama_lengkap', '$nim', '$angkatan', '$jurusan', '$alasan', '$tanggal_pengajuan', '$email', '$lampiran_nama')";


    if ($conn->query($query) === TRUE) {
        header('Location: status_pengajuan.php');
        exit();
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Dispensasi</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>SUDISMA</h3>
            <nav>
                <a href="#">Dashboard</a>
                <a href="#">Download</a>
            </nav>
        </div>
        <div class="main-content">
            <div class="form-container">
                <h2>Form Pengajuan Dispensasi</h2>
                <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm(event)">
                    <label for="nama_lengkap">Nama Lengkap Mahasiswa</label>
                    <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
                    
                    <label for="nim">Nomor Induk Mahasiswa (NIM)</label>
                    <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa (NIM)" required>
                    
                    <label for="angkatan">Angkatan</label>
                    <input type="text" name="angkatan" placeholder="Angkatan" required>
                    <label for="jurusan">Jurusan:</label>
                    <select name="jurusan" required>
                        <option value="">Pilih Jurusan</option>
                        <option value="Teknik Informatika">Teknik Informatika</option>
                        <option value="Teknik Elektro">Teknik Elektro</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Fisika">Fisika</option>
                        <option value="Kimia">Kimia</option>
                        <option value="Agroteknologi">Agroteknologi</option>
                        <option value="Matematika">Matematika</option>
                        <!-- Tambahkan pilihan lain sesuai kebutuhan -->
                    </select>
                    
                    <label for="alasan">Alasan Pengajuan</label>
                    <textarea name="alasan" placeholder="Alasan Pengajuan" required></textarea>
                    
                    <label for="tanggal_pengajuan">Tanggal Pengajuan</label>
                    <input type="date" name="tanggal_pengajuan" required>
                    
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                    
                    <label for="dokumen_lampiran">Lampiran Dokumen (optional)</label>
                    <input type="file" name="dokumen_lampiran">
                    
                    <button type="submit">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>
     <!-- Modal Konfirmasi -->
     <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Apakah Data yang kamu Isi sudah benar?</p>
            <button onclick="confirmSubmit()">Ya, Data sudah benar</button>
            <button onclick="closeModal()">Batalkan</button>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
