<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Ambil data mahasiswa
$queryMahasiswa = "SELECT id, nama, username, email, created_at FROM users WHERE role = 'mahasiswa'";
$resultMahasiswa = $conn->query($queryMahasiswa);

// Proses tambah data mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $queryInsert = "INSERT INTO users (nama, username, password, role, email) VALUES (?, ?, ?, 'mahasiswa', ?)";
    $stmt = $conn->prepare($queryInsert);
    $stmt->bind_param("ssss", $nama, $username, $password, $email);
    $stmt->execute();

    header('Location: list_user.php');
    exit();
}

// Proses edit data mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $queryUpdate = "UPDATE users SET nama = ?, username = ?, email = ? WHERE id = ? AND role = 'mahasiswa'";
    $stmt = $conn->prepare($queryUpdate);
    $stmt->bind_param("sssi", $nama, $username, $email, $id);
    $stmt->execute();

    header('Location: list_user.php');
    exit();
}

// Proses hapus data mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'];

    $queryDelete = "DELETE FROM users WHERE id = ? AND role = 'mahasiswa'";
    $stmt = $conn->prepare($queryDelete);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header('Location: list_user.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Dosen</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>


    <style>
        body {
            background-color: #f8f9fa;
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
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sidebar h5 {
            text-align: center;
            color: white;
            margin-bottom: 20px;
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
        .dashboard-header {
            width: calc(100% - 250px); /* Set to adjust with sidebar width */
            padding: 120px;
            border-radius: 0;
            background-color: #4472c4;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            margin-left: 250px; /* Offset by sidebar width */
            justify-content: space-between;
            display: flex;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 150px; /* Adjust for dashboard header */
            min-height: calc(100vh - 56px); 
        }
        .status-badge {
            padding: 3px 8px; /* Mengurangi padding badge status */
            font-size: 0.8em;
        }
        .status-belum-diproses {
            background-color: orange;
            color: white;
        }
        .status-diterima {
            background-color: green;
            color: white;
        }
        .status-ditolak {
            background-color: red;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-custom {
            padding: 3px 5px; /* Mengecilkan ukuran tombol aksi */
            font-size: 0.85em;
            border-radius: 3px;
        }
        .table-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #a3c1e0;
            color: black !important;;
            font-size: 0.9em; /* Menyesuaikan ukuran font header tabel */
        }
        .table th, .table td {
            font-size: 0.85em; /* Mengecilkan ukuran font */
            padding: 8px; /* Mengurangi padding untuk membuat tabel lebih ringkas */
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .main-content {
            margin-left: 20px;
            padding: 20px;
            margin-top: 20px; /* Adjust for dashboard header */
            min-height: calc(100vh - 56px); 
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .content-wrapper {
            margin-left: 250px;
            padding-top: 60px;
            transition: margin-left 0.3s ease;
        }
        .content-wrapper.expanded {
            margin-left: 0;
        }

        .header-title {
            font-size: 1.5em;
            color: #007bff;
            font-weight: bold;
            text-align: left;
            margin-bottom: 15px;
        }
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

    <div class="main-content" id="content">
        <div class="container mt-5">
            <div class="table-container">
                <div class="header-title">Kelola Mahasiswa</div>
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah Mahasiswa</button>
                <div class="table-responsive">
                

                    <div class="header-title">Mahasiswa</div>
                        <table id="kajurTable" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Dibuat Pada</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php while ($row = $resultMahasiswa->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['username']); ?></td>
                                        <td><?= htmlspecialchars($row['email']); ?></td>
                                        <td><?= htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <!-- Tombol Edit -->
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editModal"
                                                data-id="<?= $row['id']; ?>"
                                                data-nama="<?= htmlspecialchars($row['nama']); ?>"
                                                data-username="<?= htmlspecialchars($row['username']); ?>"
                                                data-email="<?= htmlspecialchars($row['email']); ?>">
                                                Edit
                                            </button>


                                            <!-- Tombol Hapus -->
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?');">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                </div>
            </div>
        </div>
     <!-- Modal Tambah Mahasiswa -->
     <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Tambah Mahasiswa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Mahasiswa -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Mahasiswa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_nama">Nama</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
                         
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      
        <!-- Include Bootstrap JS -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#dosenTable').DataTable({
                    "pagingType": "simple_numbers",
                    "lengthMenu": [10, 25, 50, 100],
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "paginate": {
                            "previous": "Previous",
                            "next": "Next"
                        }
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": 0 } // Disable sorting for the first column (no.)
                    ]
                });
            });

            document.getElementById("sidebarToggle").addEventListener("click", function() {
                document.getElementById("sidebar").classList.toggle("collapsed");
                document.getElementById("content").classList.toggle("expanded");
            });
         
    // Menangkap data dari tombol Edit dan memasukkan ke modal
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Tombol yang ditekan
        var id = button.data('id');
        var nama = button.data('nama');
        var username = button.data('username');
        var email = button.data('email');

        var modal = $(this);
        modal.find('#edit_id').val(id);
        modal.find('#edit_nama').val(nama);
        modal.find('#edit_username').val(username);
        modal.find('#edit_email').val(email);
    });

   
</script>

        
    </body>
</html>
