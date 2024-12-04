<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Initialize variables to avoid undefined variable notice
$status = isset($_SESSION['status']) ? $_SESSION['status'] : '';
$status_type = isset($_SESSION['status_type']) ? $_SESSION['status_type'] : '';

// Reset status setelah ditampilkan
unset($_SESSION['status']);
unset($_SESSION['status_type']);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query untuk mengambil data Kajur berdasarkan user_id
    $query = "SELECT dosen.id, dosen.nama_dosen, dosen.email, dosen.image
              FROM users
              JOIN dosen ON users.dosen_id = dosen.id
              WHERE users.id = '$user_id'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_dosen = $row['nama_dosen'];
        $email_dosen = $row['email'];
        $current_image = $row['image']; // Mendapatkan nama file gambar
    } else {
        echo "Data dosen tidak ditemukan!";
        exit;
    }

    // Jika form dikirim, lakukan konfirmasi password
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Proses perubahan password
        if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Cek apakah password saat ini sesuai dengan yang ada di database
            $query_check_password = "SELECT password FROM users WHERE id = '$user_id'";
            $result_check = mysqli_query($conn, $query_check_password);
            $user_data = mysqli_fetch_assoc($result_check);

            if (password_verify($current_password, $user_data['password'])) {
                // Verifikasi password baru
                if ($new_password == $confirm_password) {
                    // Update password di database
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query_update_password = "UPDATE users SET password = '$hashed_new_password' WHERE id = '$user_id'";
                    if (mysqli_query($conn, $query_update_password)) {
                        $status = 'Password berhasil diperbarui!';
                        $status_type = 'success';
                    } else {
                        $status = 'Terjadi kesalahan saat memperbarui password!';
                        $status_type = 'danger';
                    }
                } else {
                    $status = 'Password baru tidak cocok!';
                    $status_type = 'danger';
                }
            } else {
                $status = 'Password saat ini salah!';
                $status_type = 'danger';
            }
        }

        // Pastikan ini bagian upload gambar berfungsi dengan benar
            
    // File upload processing logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_FILES['profile_picture'])) {
            $upload_dir = 'image/';
            $file = $_FILES['profile_picture'];
            $file_name = basename($file['name']);
            $target_file = $upload_dir . $file_name;

            // Verify file size and type
            if ($file['size'] <= 1048576 && in_array($file['type'], ['image/jpeg', 'image/png'])) {
                // Process file upload
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    if (file_exists($target_file)) {
                        // Save the file name in the database
                        $file_name = mysqli_real_escape_string($conn, $file_name);
                        $query_update_image = "UPDATE dosen SET image = '$file_name' WHERE id = (SELECT dosen_id FROM users WHERE id = '$user_id')";
                        if (mysqli_query($conn, $query_update_image)) {
                            $_SESSION['status'] = "File berhasil diunggah dan nama gambar berhasil disimpan ke database!";
                            $_SESSION['status_type'] = 'success';
                        } else {
                            $_SESSION['status'] = "Gagal menyimpan gambar ke database!";
                            $_SESSION['status_type'] = 'danger';
                        }
                    } else {
                        $_SESSION['status'] = "File gagal diunggah.";
                        $_SESSION['status_type'] = 'danger';
                    }
                } else {
                    $_SESSION['status'] = "Gagal mengunggah file.";
                    $_SESSION['status_type'] = 'danger';
                }
            } else {
                $_SESSION['status'] = "File tidak sesuai kriteria (JPG/PNG, max 1MB).";
                $_SESSION['status_type'] = 'danger';
            }
        }
    }


        
    else {
    header("Location: index.php");
    exit;
}
    }
}
// Jika form Informasi Akun dikirim, perbarui data di database
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
        $new_nama_dosen = mysqli_real_escape_string($conn, $_POST['nama']);
        $new_email_dosen = mysqli_real_escape_string($conn, $_POST['email']);

        // Update data nama dan email di database
        $query_update_profile = "UPDATE dosen SET nama_dosen = '$new_nama_dosen', email = '$new_email_dosen' WHERE id = (SELECT dosen_id FROM users WHERE id = '$user_id')";
        if (mysqli_query($conn, $query_update_profile)) {
            $_SESSION['status'] = "Profil berhasil diperbarui!";
            $_SESSION['status_type'] = 'success';
            header("Location: pengaturan_kajur.php"); // Refresh halaman
            exit;
        } else {
            $_SESSION['status'] = "Terjadi kesalahan saat memperbarui profil!";
            $_SESSION['status_type'] = 'danger';
        }
    }
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    
        // Query untuk mengambil nama dari tabel dosen
        $queryKajur = "SELECT dosen.nama_dosen AS namaKajur FROM dosen
                       JOIN users ON dosen.id = users.dosen_id
                       WHERE users.id = ?";
        $stmt = $conn->prepare($queryKajur);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $resultKajur = $stmt->get_result();
    
            if ($resultKajur->num_rows > 0) {
                $row = $resultKajur->fetch_assoc();
                $namaKajur = $row['namaKajur'];
            } else {
                $namaKajur = "Unknown"; // Nama default jika tidak ditemukan
            }
    
            $stmt->close();
        } else {
            echo "Error in query preparation.";
        }
    }

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun Kajur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    
   
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 90px;
        
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .btn-primary {
            margin-top: 30px;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        /* Kustomisasi modal sukses dan gagal */
        .modal-success .modal-content {
            border-color: #28a745;
            background-color: #d4edda;
            color: #155724;
        }
        .modal-danger .modal-content {
            border-color: #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }
        body {
    background-color: #f8f9fa;
}

    
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 8px;
    }
    .profile-picture {
        width: 100px;
        height: 100px;
        background-color: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: #555;
        margin: 20px auto;
    }
    .btn-upload {
        width: 100%;
        margin-top: 10px;
    }
    .form-group label {
        font-weight: normal;
    }
    .nav-tabs .nav-link {
        font-weight: bold;
    }
    body {
    background-color: #f8f9fa;
}

.sidebar {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: #343a40;
        color: white;
        padding-top: 80px;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
}
.sidebar.visible {
    transform: translateX(0);
}
#sidebarToggle {
    z-index: 1050;
    cursor: pointer;
}
.sidebar h5 {
    text-align: center;
    color: white;
    margin-bottom: 20px;
    margin-top: 40px;
}

.sidebar a {
    color: white;
    display: block;
    padding: 10px 20px;
    text-decoration: none;
    font-size: 16px;
}

.sidebar a:hover {
    background-color: #495057;
}

.sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar.collapsed ~ .dashboard-header {
    margin-left: 0;
    width: 100%;
}

.sidebar.collapsed ~ .main-content {
    margin-left: 0;
    width: 100%;
}

.content-wrapper {
    margin-left: 250px;
    padding-top: 60px;
    transition: margin-left 0.3s ease;
}

.content-wrapper.expanded {
    margin-left: 0;
}

.dashboard-header {
    width: calc(100% - 250px);
    padding: 120px;
    border-radius: 0;
    background-color: #4472c4;
    margin-left: 0px;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    margin-left: 250px;
    justify-content: space-between;
    display: flex;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
    margin-top: 150px;
    min-height: calc(100vh - 56px);
}

.welcome-card {
    background-color: #ffffff;
    padding: 0px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: -240px;
    display: flex;
    margin-right: 90px;
    justify-content: space-between;
    position: relative;
    left: 0;
    z-index: 1;
    width: calc(100% - 40px);
    transition: all 0.3s ease;
}

.welcome-card div {
    display: flex;
    flex-direction: column;
}

.welcome-card h4 {
    margin: 0;
    margin-top: 40px;
    margin-left: 40px;
    font-size: 30px;
    font-weight: bold;
}

.welcome-card p {
    margin: 5px 0 0 0;
    font-size: 20px;
    margin-left: 40px;
    color: #555;
}

.welcome-card img {
    width: 180px;
    height: 180px;
    object-fit: cover;
    margin-right: 40px;
}

.info-card {
    color: white;
    padding: 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-grow: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.info-card-primary {
    background-color: #4a90e2;
}

.info-card-warning {
    background-color: #f5a623;
}

.navbar {
    
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#current-date {
    width: 250px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-left: 10px;
    background-color: white;
    color: black;
    border: none;
    border-radius: 5px;
    gap: 8px;
}

#current-date i {
    font-size: 18px;
    color: black;
}

.dashboard-header h3 {
    margin: 0;
    font-size: 40px;
    font-weight: bold;
}

.dashboard-header small {
    display: block;
    font-size: 17px;
    color: #f8f9fa;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
            height: auto;
            top: 0;
            left: 0;
            transform: translateY(-100%);
            position: absolute;
            z-index: 1000; /* Agar berada di atas header */
            transition: transform 0.3s ease;
    }

    .sidebar.visible {
            transform: translateY(0);
        }
        .content-wrapper {
        display: flex;
        justify-content: center; /* Pusatkan konten */
        padding-top: 60px; /* Sesuaikan padding jika perlu */
        margin-left: 20px; /* Hapus margin kiri */
        margin-right: 20px; /* Hapus margin kanan */
    }

    .container {
        max-width: 100%; /* Membuat container lebih responsif */
        padding: 20px; /* Sesuaikan padding jika perlu */
    }
    
    
}

@media (max-width: 480px) {
    .sidebar {
        width: 100%;
            height: auto;
            top: 0;
            left: 0;
            transform: translateY(-100%);
            position: absolute;
            z-index: 1000; /* Agar berada di atas header */
            transition: transform 0.3s ease;
    }

    .sidebar a {
        font-size: 14px;
    }

    .dashboard-header h3 {
        font-size: 20px;
        margin-left:automatic;
    }

    .welcome-card h4 {
        font-size: 20px;
        margin-left: 10px;
    }

    .info-card {
        font-size: 14px;
    }

    .navbar {
        padding: 5px;
    }
}
.modal-body img {
    max-width: 100%;
    max-height: 80vh; /* Membatasi tinggi gambar agar tidak melebihi layar */
    object-fit: contain;
}


</style>

    </style>
</head>
<body>
<!-- Navbar -->
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
<div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar" style="height: 100vh;">
        <h4 class="text-center">SUDISMA</h4>
        
        <small class="text-muted ms-2" style="margin-top: 70px;">Menu</small>
        <nav class="nav flex-column mt-2">
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_kajur.php' ? 'active' : '' ?>" href="dashboard_kajur.php" style="color: <?= $currentPage == 'dashboard_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengajuan_kajur.php' ? 'active' : '' ?>" href="pengajuan_kajur.php" style="color: <?= $currentPage == 'pengajuan_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Dispensasi
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'angkatan_kajur.php' ? 'active' : '' ?>" href="angkatan_kajur.php" style="color: <?= $currentPage == 'angkatan_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Data Ditolak
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'riwayat_kajur.php' ? 'active' : '' ?>" href="riwayat_kajur.php" style="color: <?= $currentPage == 'riwayat_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-archive" style="margin-right: 15px;"></i> Riwayat Pengajuan
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengaturan_kajur.php' ? 'active' : '' ?>" href="pengaturan_kajur.php" style="color: <?= $currentPage == 'pengaturan_kajur.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-gear" style="margin-right: 15px;"></i> Pengaturan Akun
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'logout.php' ? 'active' : '' ?>" href="logout.php" style="color: <?= $currentPage == 'logout.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
            </a>
        </nav>

        <!-- Menampilkan nama Kajur di bagian paling bawah sidebar -->
        <div class="mt-auto text-left p-3" style="background-color: #ffffff; color: black;">
            <small>Logged in as: <br><strong><?php echo $namaKajur; ?></strong></small>
        </div>
    </div>

<div class="content-wrapper">
<div class="container padding-top 40px">
        <h2>Pengaturan Akun Ketua Jurusan</h2>
        <ul class="nav nav-tabs" id="pengaturanTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">Profil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Ubah Password</a>
            </li>
            
        </ul>

        <div class="tab-content mt-4" id="pengaturanTabsContent">
            <!-- Tab Profil -->
            <div class="tab-pane fade show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
                <div class="row">
                    <!-- Card Profile Picture -->
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h5 class="text-center">Profile Picture</h5>
                          
                            <form method="post" enctype="multipart/form-data">
                            <div class="profile-picture" data-bs-toggle="modal" data-bs-target="#profileModal" style="cursor: pointer;">
                                <img id="profile-img" src="<?= $current_image ? 'image/' . $current_image : 'default_profile.png' ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                                </div>
                                <input type="file" name="profile_picture" id="profilePictureInput" class="btn-upload" onchange="previewProfileImage(event)">
                                <button type="submit" class="btn btn-primary btn-upload"><i class="bi bi-upload"></i> Unggah File</button>
                            </form>
                        </div>
                    </div>

                    <!-- Card Informasi Akun (disebelah kanan) -->
                    <!-- Card Informasi Akun -->
                    <div class="col-md-8">
                        <div class="card p-3">
                            <h5>Informasi Akun</h5>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama_dosen); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email_dosen); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Perbarui Profil</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tab Ubah Password -->
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <div class="card p-3 mt-3">
                    <h5>Ubah Password</h5>
                    <form method="post">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </form>
                </div>
            </div>

            <!-- Tab Setup Aplikasi -->
            
        </div>
    </div>

</div>
    
<!-- Modal for Upload Status (Success or Error) -->
<div class="modal fade" id="uploadStatusModal" tabindex="-1" aria-labelledby="uploadStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadStatusModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalMessage">
                <?php 
                    // Display status message from session
                    if ($status != '') {
                        echo "<p class='text-" . ($status_type == 'success' ? 'success' : 'danger') . "'>" . $status . "</p>";
                    }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- Modal untuk menampilkan gambar profil -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img id="modal-profile-img" src="<?= $current_image ? 'image/' . $current_image : 'default_profile.png' ?>" alt="Profile Picture" class="img-fluid">
      </div>
    </div>
  </div>
</div>
<script src="lib/script.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Include JS and Bootstrap for modal functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("sidebarToggle").addEventListener("click", function() {
    const sidebar = document.getElementById("sidebar");
    
    // Toggle class "visible" untuk menampilkan sidebar dari atas pada layar kecil
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("visible");
    } else {
        // Mode biasa tetap gunakan toggle class "collapsed"
        sidebar.classList.toggle("collapsed");
    }

});
    // Menangani form konfirmasi modal
    $('#confirmButton').on('click', function () {
        $('#form-password').submit();
        $('#confirmModal').modal('hide');
    });

    // Show the modal after the page loads, if the session status is set
    <?php if (isset($_SESSION['status'])): ?>
        $(document).ready(function() {
            $('#statusModal').modal('show');
        });
    <?php endif; ?>

    function previewProfileImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            // Menampilkan gambar yang diupload di dalam lingkaran
            document.getElementById('profile-img').src = e.target.result;
        }

        if (file) {
            reader.readAsDataURL(file); // Membaca file gambar sebagai URL
        }
    }
    //Check if session status and type exist and show modal with corresponding message
    <?php if ($status != ''): ?>
        var myModal = new bootstrap.Modal(document.getElementById('uploadStatusModal'), {
            keyboard: false
        });
        myModal.show();

        // Hide the modal after 3 seconds
        setTimeout(function() {
            myModal.hide();
        }, 3000);
    <?php endif; ?>

    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebarToggle');

        // Pastikan sidebar toggle berjalan
        toggleButton.addEventListener('click', () => {
            console.log("Toggle clicked");
            sidebar.classList.toggle('visible');
        });

});

// Pastikan modal muncul dan gambar ditampilkan dengan benar
document.getElementById('profile-img').addEventListener('click', function() {
    var profileImage = this.src;
    document.getElementById('modal-profile-img').src = profileImage;
});

document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.dataset.target);
            const icon = this.querySelector('i');

            if (target.type === 'password') {
                target.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

</script>

</body>
</html>
