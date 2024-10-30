<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - SUDISMA</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">SUDISMA</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="form_pengajuan.php">Ajukan Dispensasi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="status_pengajuan.php">Cek Status Pengajuan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-12 mb-4 text-center">
                <h2 class="display-4">Selamat Datang, Mahasiswa!</h2>
                <p class="lead">Selamat datang di dashboard pengajuan surat dispensasi.</p>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ajukan Dispensasi</h5>
                        <p class="card-text">Ajukan surat dispensasi untuk kebutuhan Anda dengan mudah.</p>
                        <a href="form_pengajuan.php" class="btn btn-primary">Ajukan Sekarang</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title">Cek Status Pengajuan</h5>
                        <p class="card-text">Lihat status pengajuan surat dispensasi Anda.</p>
                        <a href="status_pengajuan.php" class="btn btn-info">Lihat Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <p class="mb-0">© 2024 SUDISMA - Surat Dispensasi Mahasiswa. All Rights Reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
