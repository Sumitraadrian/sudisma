<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'mahasiswa') {
                header('Location: dashboard_mahasiswa.php');
            } else {
                header('Location: dashboard_admin.php');
            }
            exit();
        } else {
            echo "Password salah!";
        }
    } else {
        echo "Pengguna tidak ditemukan!";
    }
}
?>

<<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Surat Dispensasi Mahasiswa</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100">
            <!-- Title Section -->
            <div class="col-md-6 d-flex flex-column align-items-start text-white mb-4 mb-md-0">
                <h1 class="display-4 font-weight-bold">S U D I S M A</h1>
                <p class="lead">Surat Dispensasi Mahasiswa</p>
                
                <!-- Image under title -->
                <img src="image/image.png" alt="Illustration of a student working at a desk" class="img-fluid mt-3 rounded shadow-sm custom-img-shift">
            </div>
            
            <!-- Login Form Section -->
            <div class="col-md-6">
                <div class="card p-4 shadow-lg">
                    <h2 class="card-title mb-3">Selamat Datang di Aplikasi SUDISMA!</h2>
                    <p class="text-muted">Silakan login untuk masuk ke aplikasi</p>

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="username" class="text-dark">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="text-dark">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label text-dark" for="remember">Remember Me</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
