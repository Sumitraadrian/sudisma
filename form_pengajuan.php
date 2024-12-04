<?php
session_start();
include 'db.php';

// Sertakan file PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ambil data pengguna yang sedang login
$user_id = $_SESSION['user_id'];
$query_user = "SELECT username, email, nama FROM users WHERE id = '$user_id'";
$result_user = $conn->query($query_user);

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $nim = $user['username']; // NIM menggunakan username
    $email = $user['email'];
    $nama_lengkap = $user['nama']; // Menambahkan variabel nama_lengkap
} else {
    echo "Pengguna tidak ditemukan.";
    exit();
}

// Query untuk mengambil data jurusan
$query_jurusan = "SELECT id, nama_jurusan FROM jurusan";
$result_jurusan = $conn->query($query_jurusan);

if (!$result_jurusan) {
    echo "Error: " . $conn->error;
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data dari form
    $angkatan = $_POST['angkatan'];
    $jurusan_id = $_POST['jurusan_id'];
    $alasan = $_POST['alasan'];
    $tanggal_pengajuan = $_POST['tanggal_pengajuan'];

    // Pastikan NIM dan email yang sudah diambil dari database digunakan
    $nim = $user['username'];
    $email = $user['email'];
    $nama_lengkap = $user['nama'];

    // Handle file upload
    if (isset($_FILES['dokumen_lampiran']) && $_FILES['dokumen_lampiran']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf'];
        $file_type = mime_content_type($_FILES['dokumen_lampiran']['tmp_name']);
        $max_size = 2 * 1024 * 1024; // Maksimal 2 MB

        if (in_array($file_type, $allowed_types) && $_FILES['dokumen_lampiran']['size'] <= $max_size) {
            $lampiran_nama = uniqid() . '_' . basename($_FILES['dokumen_lampiran']['name']);
            $upload_dir = 'uploads/' . $lampiran_nama;
            
            if (move_uploaded_file($_FILES['dokumen_lampiran']['tmp_name'], $upload_dir)) {
                // File berhasil diunggah
                // Lanjutkan dengan memasukkan data ke database
            } else {
                echo "File gagal diunggah. Silakan coba lagi.";
            }
        } else {
            $error = "Format atau ukuran file tidak sesuai!";
        }
    } else {
        $lampiran_nama = null;
    }

    // Query untuk mendapatkan email kajur berdasarkan jurusan
    $query_kajur = "SELECT d.email FROM dosen d 
                JOIN jurusan j ON d.id = j.ketua_jurusan_id 
                WHERE j.id = ?";
    $stmt_kajur = $conn->prepare($query_kajur);

    if ($stmt_kajur) {
        $stmt_kajur->bind_param("i", $jurusan_id); // Menggunakan jurusan_id yang dipilih
        if ($stmt_kajur->execute()) {
            $result_kajur = $stmt_kajur->get_result();

            if ($result_kajur->num_rows > 0) {
                $kajur = $result_kajur->fetch_assoc();
                $email_kajur = $kajur['email'];
            } else {
                echo "Kajur tidak ditemukan untuk jurusan ini.";
                exit();
            }
        } else {
            echo "Query gagal dijalankan: " . $stmt_kajur->error;
            exit();
        }
    } else {
        echo "Query gagal dijalankan: " . $conn->error;
        exit();
    }

    // Query untuk memasukkan data ke dalam tabel pengajuan
    $query = "INSERT INTO pengajuan 
              (user_id, nama_lengkap, nim, angkatan, email, alasan, tanggal_pengajuan, dokumen_lampiran, jurusan_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Gunakan prepared statement untuk menghindari SQL Injection
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("isssssssi", $user_id, $nama_lengkap, $nim, $angkatan, $email, $alasan, $tanggal_pengajuan, $lampiran_nama, $jurusan_id);

        if ($stmt->execute()) {
            // Pengajuan berhasil, kirim email notifikasi ke kajur

            
                $pengajuan_id = $stmt->insert_id; // ID dari pengajuan yang baru disimpan
    
                // Simpan data dosen matkul
                // Validasi form dosen
                if (isset($_POST['namaDosen']) && isset($_POST['mataKuliah']) && isset($_POST['emailDosen'])) {
                    $namaDosen = $_POST['namaDosen'];
                    $mataKuliah = $_POST['mataKuliah'];
                    $emailDosen = $_POST['emailDosen'];
                   

                    // Pastikan semua data dosen terisi
                    for ($i = 0; $i < count($namaDosen); $i++) {
                        if (empty($namaDosen[$i]) || empty($mataKuliah[$i]) || empty($emailDosen[$i])) {
                            echo "<script>$('#fieldIncompleteModal').modal('show');</script>";
                            exit();
                        }
                    }
                } else {
                    echo "<script>$('#dataNotFilledModal').modal('show');</script>";
                    exit();
                }

    
                $query_dosen_matkul = "INSERT INTO dosen_matkul (pengajuan_id, nama_dosen, mata_kuliah, email_dosen)
                                       VALUES (?, ?, ?, ?)";
    
                $stmt_dosen_matkul = $conn->prepare($query_dosen_matkul);
    
                if ($stmt_dosen_matkul) {
                    for ($i = 0; $i < count($namaDosen); $i++) {
                        $stmt_dosen_matkul->bind_param(
                            "isss",
                            $pengajuan_id,
                            $namaDosen[$i],
                            $mataKuliah[$i],
                            $emailDosen[$i],
                         
                        );
    
                        if (!$stmt_dosen_matkul->execute()) {
                            echo "Error: " . $stmt_dosen_matkul->error;
                            exit();
                        }
                    }
    
                    echo "Data pengajuan dan dosen matkul berhasil disimpan.";
                } else {
                    echo "Error: " . $conn->error;
                    exit();
                }

            $mail = new PHPMailer(true);
            try {
                // Konfigurasi server SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Alamat SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'adriansyahsumitra@gmail.com'; // Alamat email Anda
                $mail->Password = 'kivu njcw rcam nkwl'; // Kata sandi email Anda
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Penerima email (kajur)
                $mail->setFrom('adriansyahsumitra@gmail.com', 'Admin Dispensasi'); // Dari email admin
                $mail->addAddress($email_kajur); // Ke email kajur

                // Isi email
                $mail->isHTML(true);
                $mail->Subject = "Pengajuan Dispensasi Baru dari Mahasiswa";
                $mail->Body    = "
                    <html>
                    <head>
                        <title>Pengajuan Dispensasi Baru</title>
                    </head>
                    <body>
                        <p>Yth. Bapak/Ibu Kajur,</p>
                        <p>Mahasiswa dengan NIM: $nim telah mengajukan dispensasi dengan alasan: $alasan.</p>
                        <p>Harap melakukan pengecekan dan tindakan lebih lanjut.</p>
                        <p>Terima kasih.</p>
                    </body>
                    </html>
                ";

                // Kirim email
                $mail->send();
                echo 'Notifikasi berhasil dikirim ke Kajur';

                // Redirect ke halaman status pengajuan atau konfirmasi
                header('Location: status_pengajuan.php');
                exit();

            } catch (Exception $e) {
                echo "Gagal mengirim email. Mailer Error: {$mail->ErrorInfo}";
            }

        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Query gagal dijalankan untuk insert pengajuan.";
    }

    $stmt->close();
    $conn->close();
    
}


?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Dispensasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .custom-bg { background-color: #5393F3; }

        /* Sidebar Styling */
        .sidebar { 
            background-color: #f8f9fa; 
            height: 100vh; 
            width: 250px; 
            position: fixed; 
            padding-top: 60px; 
            top: 0; 
            left: -250px; /* Initially hide sidebar off-screen */
            transition: left 0.3s ease;
            z-index: 1040; /* Ensure it sits on top of other elements */
        }

        .sidebar.show {
            left: 0; /* Move sidebar into view */
        }

        /* Main Content Styling */
        .content-wrapper {
            margin-left: 0; /* Default margin for mobile */
            padding-top: 60px;
            transition: margin-left 0.3s ease; 
        }

        /* Adjust content margin when sidebar is visible on larger screens */
        @media (min-width: 768px) {
            .content-wrapper.shifted {
                margin-left: 250px;
            }
        }

        /* Navbar Styling */
        .navbar { 
            background-color: #ffff; 
            color: black; 
            z-index: 1050; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            position: fixed; 
            width: 100%; 
            top: 0; 
            left: 0; 
        }

        .navbar-toggler { 
            border: none; 
        }

        body {
            font-family: Arial, sans-serif;
           
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-container {
            
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            position: relative;
        }
        .form-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .form-title h4 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .remove-btn {
            color: red;
            font-size: 20px;
            cursor: pointer;
        }
        .add-dosen-btn {
            display: inline-flex;
            align-items: center;
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .add-dosen-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid d-flex align-items-center">
        <!-- Button Sidebar Toggle dan Nama Aplikasi di Satu Elemen -->
        <button class="btn me-2 d-flex align-items-center" id="sidebarToggle" style="background-color: transparent; border: none;">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            <span class="navbar-brand text-dark ms-2">SUDISMA</span>
        </button>
    </div>
</nav>


    <!-- Sidebar -->
    <div class="sidebar bg-light p-3 shadow-lg" id="sidebar">
        <h4 class="text-center">SUDISMA</h4>
        <div style="height: 40px;"></div>
        <small class="text-muted ms-2">Menu</small>
        <nav class="nav flex-column mt-2">
            <a class="nav-link active d-flex align-items-center text-dark" href="dashboard_mahasiswa.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a class="nav-link d-flex align-items-center text-dark" href="status_pengajuan.php"><i class="bi bi-file-earmark-text me-2"></i> Status Pengajuan</a>
            <a class="nav-link d-flex align-items-center text-dark" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper custom-bg d-flex justify-content-center align-items-center" id="contentWrapper">
        <div class="form-container col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Form Pengajuan Dispensasi</h2>
                    <form id="dispensasiForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap Mahasiswa</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?php echo $user['nama']; ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="nim" class="form-label">Nomor Induk Mahasiswa (NIM)</label>
                            <input type="text" name="nim" class="form-control" value="<?php echo $nim; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="angkatan" class="form-label">Angkatan</label>
                            <input type="text" name="angkatan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="jurusan_id" class="form-label">Jurusan</label>
                            <select name="jurusan_id" class="form-select" required>
                                <option value="">Pilih Jurusan</option>
                                <?php
                                if ($result_jurusan->num_rows > 0) {
                                    while ($row = $result_jurusan->fetch_assoc()) {
                                        echo '<option value="' . $row['id'] . '">' . $row['nama_jurusan'] . '</option>';
                                    }
                                } else {
                                    echo '<option value="">Tidak ada jurusan tersedia</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                        <div class="form-title">
                            <h4>Dosen Mata Kuliah</h4>
                            <button type="button" class="add-dosen-btn" onclick="addDosenForm()">
                                <i class="fas fa-plus"></i> Tambah Dosen
                            </button>
                        </div>
                        <div id="dosenFormsContainer"></div>
                        <div class="mb-3">
                            <label for="alasan" class="form-label">Alasan Pengajuan</label>
                            <textarea name="alasan" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_pengajuan" class="form-label">Tanggal Pengajuan</label>
                            <input type="date" name="tanggal_pengajuan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Mahasiswa</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="dokumen_lampiran" class="form-label">Lampiran Dokumen</label>
                            <div class="form-text">Format PDF maksimal 2 MB</div>
                            <input type="file" name="dokumen_lampiran" class="form-control">
                            
                        </div>

                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" >Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

   <!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5>Apakah Data yang kamu Isi sudah benar?</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" id="confirmButton">Ya, Data sudah benar</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Warning -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5>Mohon lengkapi semua data yang diperlukan!</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-warning" id="closeWarningModal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Semua Field Dosen Mata Kuliah Harus Diisi -->
<div class="modal fade" id="fieldIncompleteModal" tabindex="-1" aria-labelledby="fieldIncompleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5>Semua field dosen mata kuliah harus diisi!</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Data Dosen Mata Kuliah Belum Diisi -->
<div class="modal fade" id="dataNotFilledModal" tabindex="-1" aria-labelledby="dataNotFilledModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5>Data dosen mata kuliah belum diisi!</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningModalLabel">Peringatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Harap klik tombol "Tambah Dosen" untuk memilih dosen terlebih dahulu sebelum mengirimkan form.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Bootstrap CSS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("sidebarToggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");
        const contentWrapper = document.getElementById("contentWrapper");

        sidebar.classList.toggle("show");
        contentWrapper.classList.toggle("shifted");
    });

    $(document).ready(function() {
    // Tangani pengiriman form dengan mencegah default behavior
    $("dispensasiForm").submit(function(e) {
        e.preventDefault(); // Mencegah form terkirim ke server

        // Cek kondisi sebelum menampilkan modal
        var formCompleted = true;  // Misalnya, cek apakah semua form terisi
        if (!formCompleted) {
            $('#fieldIncompleteModal').modal('show');
        } else {
            // Jika form lengkap, lanjutkan dengan pengiriman data (misalnya AJAX)
            // atau lakukan aksi lain yang diinginkan
            this.submit();  // Kirim form jika sudah lengkap
        }
    });
});

    function validateForm() {
        var dosenForms = document.querySelectorAll('.dosen-form');
        var isFormValid = true;

        // Cek apakah ada form dosen yang kosong
        dosenForms.forEach(function (form) {
            var namaDosen = form.querySelector('input[name="namaDosen[]"]').value;
            var mataKuliah = form.querySelector('input[name="mataKuliah[]"]').value;
            var emailDosen = form.querySelector('input[name="emailDosen[]"]').value;
           

            if (!namaDosen || !mataKuliah || !emailDosen) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            alert("Semua field Dosen Mata Kuliah harus diisi.");
            return false; // Mencegah form dikirim
        }

        return true; // Izinkan pengiriman form jika valid
    }

    document.getElementById('dispensasiForm').onsubmit = function(event) {
    if (!validateForm()) {
        event.preventDefault(); // Mencegah pengiriman form jika validasi gagal
        $('#fieldIncompleteModal').modal('show');
    } else {
        // Tampilkan modal konfirmasi
        $('#confirmModal').modal('show');
    }
};



    document.querySelector(".btn-primary.w-100").addEventListener("click", function(event) {
    event.preventDefault(); // Mencegah pengiriman form secara langsung

    // Tampilkan Modal Konfirmasi
    $('#confirmModal').modal('show');
});

// Submit form on confirm button click
document.getElementById("confirmButton").addEventListener("click", function() {
    // Ambil elemen form dan input yang wajib diisi
    const form = document.getElementById("dispensasiForm");
    const angkatan = form.querySelector("[name='angkatan']");
    const jurusan_id = form.querySelector("[name='jurusan_id']");
    const alasan = form.querySelector("[name='alasan']");
    const tanggal_pengajuan = form.querySelector("[name='tanggal_pengajuan']");
    const dokumen_lampiran = form.querySelector("[name='dokumen_lampiran']");

    if (!isDosenSelected) {
        $('#confirmModal').modal('hide'); // Menyembunyikan Modal Konfirmasi
        $('#warningModal').modal('show'); // Menampilkan Modal Peringatan
    }else if (!form.checkValidity()) {
        // Tampilkan Modal Warning
        $('#confirmModal').modal('hide'); // Menyembunyikan Modal Konfirmasi
        $('#warningModal').modal('show'); // Menampilkan Modal Warning
    } else{
        // Jika semua form sudah terisi, submit form
        form.submit();
    }
});

// Tutup modal warning dan fokus ke field yang kosong
document.querySelector("#warningModal .btn-warning").addEventListener("click", function() {
    // Tutup modal warning
    $('#warningModal').modal('hide');
    
    // Hapus overlay background
    const modalBackdrop = document.querySelector('.modal-backdrop');
    if (modalBackdrop) {
        modalBackdrop.remove();
    }

    // Fokuskan ke elemen pertama pada form yang belum terisi
    const form = document.getElementById("dispensasiForm");
    const firstEmptyField = form.querySelector("input:invalid, select:invalid, textarea:invalid");
    
    if (firstEmptyField) {
        firstEmptyField.focus();
    }
});






    let dosenCounter = 0;
    let isDosenSelected = false;

function addDosenForm() {
    isDosenSelected = true;
    console.log("Tombol 'Tambah Dosen' sudah diklik.");

    dosenCounter++;
    const container = document.getElementById('dosenFormsContainer');
    const dosenForm = document.createElement('div');
    dosenForm.className = 'form-container';
    dosenForm.dataset.order = dosenCounter; // Menyimpan nomor unik untuk referensi
    dosenForm.innerHTML = `
        <div class="form-title">
            <h4>Form Dosen ${dosenCounter}</h4>
            <span class="remove-btn" onclick="removeDosenForm(${dosenCounter})">&times;</span>
        </div>
        <div class="form-group">
            <label for="namaDosen${dosenCounter}">Nama Dosen</label>
            <input type="text" id="namaDosen${dosenCounter}" name="namaDosen[]" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="mataKuliah${dosenCounter}">Mata Kuliah</label>
            <input type="text" id="mataKuliah${dosenCounter}" name="mataKuliah[]" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="emailDosen${dosenCounter}">Email Dosen</label>
            <input type="email" id="emailDosen${dosenCounter}" name="emailDosen[]" class="form-control" required>
        </div>
        
    `;
    container.appendChild(dosenForm);
    reorderDosenForms();
}

function removeDosenForm(id) {
    const container = document.getElementById('dosenFormsContainer');
    const dosenForm = Array.from(container.children).find((form) => form.dataset.order == id);
    if (dosenForm) {
        container.removeChild(dosenForm);
    }
    reorderDosenForms();
}

function reorderDosenForms() {
    const container = document.getElementById('dosenFormsContainer');
    const forms = Array.from(container.children);
    forms.forEach((form, index) => {
        const currentNumber = index + 1; // Hitung ulang urutan
        const title = form.querySelector('.form-title h4');
        const removeBtn = form.querySelector('.remove-btn');
        const id = form.dataset.order;

        // Update teks judul dan fungsi hapus
        title.textContent = `Form Dosen ${currentNumber}`;
        removeBtn.setAttribute('onclick', `removeDosenForm(${id})`);
    });
}


    </script>
</body>
</html>
