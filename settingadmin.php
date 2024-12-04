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
     

        
    else {
    header("Location: index.php");
    exit;
}
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
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>

    
   
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
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_admin.php' ? 'active' : '' ?>" href="dashboard_admin.php" style="color: <?= $currentPage == 'dashboard_admin.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengajuanadmin.php' ? 'active' : '' ?>" href="pengajuanadmin.php" style="color: <?= $currentPage == 'pengajuanadmin.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Dispensasi
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_pengajuan.php' ? 'active' : '' ?>" href="list_pengajuan.php" style="color: <?= $currentPage == 'list_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Riwayat Pengajuan
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_dosen.php' ? 'active' : '' ?>" href="list_dosen.php" style="color: <?= $currentPage == 'list_dosen.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-archive" style="margin-right: 15px;"></i> Data User Dosen
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_user.php' ? 'active' : '' ?>" href="list_user.php" style="color: <?= $currentPage == 'list_user.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-box" style="margin-right: 15px;"></i> Data User Mahasiswa
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'settingadmin.php' ? 'active' : '' ?>" href="settingadmin.php" style="color: <?= $currentPage == 'settingadmin.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-gear" style="margin-right: 15px;"></i> Pengaturan Akun
            </a>
            <a class="nav-link d-flex align-items-center <?= $currentPage == 'logout.php' ? 'active' : '' ?>" href="logout.php" style="color: <?= $currentPage == 'logout.php' ? '#007bff' : 'black'; ?>;">
                <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
            </a>
        </nav>

       
    </div>

<div class="content-wrapper">
<div class="container padding-top 40px">
        <h2>Pengaturan Akun Ketua Jurusan</h2>
        <ul class="nav nav-tabs" id="pengaturanTabs" role="tablist">
       

            <li class="nav-item">
                <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Ubah Password</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="backup-tab" data-toggle="tab" href="#backup" role="tab" aria-controls="backup" aria-selected="false">Backup Data</a>
            </li>
            
        </ul>

        <div class="tab-content mt-4" id="pengaturanTabsContent">
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

            <!-- Tab Backup Data -->
            <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                <div class="card p-3 mt-3">
                    <h5>Backup Data</h5>
                    <p>Untuk menjaga keamanan data, Anda dapat melakukan backup database aplikasi secara manual. Backup akan menyimpan salinan data yang ada untuk pemulihan di masa depan.</p>
                    <form method="post" action="backup_database.php">
                        <button type="submit" class="btn btn-danger">Backup Data Sekarang</button>
                    </form>
                </div>
            </div>

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
