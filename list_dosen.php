<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Mengambil data Ketua Jurusan dan Wakil Dekan
$queryKajur = "
    SELECT d.id, d.nama_dosen, d.nip, d.email, 
           COALESCE(j.nama_jurusan, 'Belum Ada Jurusan') AS nama_jurusan
    FROM dosen d
    LEFT JOIN jurusan j ON d.id = j.ketua_jurusan_id
    WHERE d.id IN (
        SELECT dosen_id FROM users WHERE role = 'kajur'
    )
";

$queryWadek = "SELECT nama, email, nip, id FROM wakil_dekan";
$resultKajur = $conn->query($queryKajur);
$resultWadek = $conn->query($queryWadek);

// Mengambil data semua jurusan
$queryJurusan = "SELECT id, nama_jurusan FROM jurusan";
$resultJurusan = $conn->query($queryJurusan);

// Proses simpan data
// Proses simpan data
// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_dosen = $_POST['nama_dosen'];
    $email = $_POST['email'];
    $nip = $_POST['nip'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $nama_jurusan = isset($_POST['nama_jurusan']) ? $_POST['nama_jurusan'] : null;

    // Debugging untuk memastikan role terisi dengan benar
    var_dump($role); // Ini akan menunjukkan nilai role yang diterima

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Menyimpan data Dosen
    $queryInsertDosen = "INSERT INTO dosen (nama_dosen, email, nip) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($queryInsertDosen);
    $stmt->bind_param("sss", $nama_dosen, $email, $nip);
    $stmt->execute();

    $dosen_id = $stmt->insert_id; // Mendapatkan ID dosen yang baru disimpan

    // Jika role adalah 'kajur' dan nama jurusan ada
    if ($role === 'kajur' && !empty($nama_jurusan)) {
        // Cek apakah jurusan sudah ada
        $queryCheckJurusan = "SELECT id FROM jurusan WHERE nama_jurusan = ?";
        $stmtCheck = $conn->prepare($queryCheckJurusan);
        $stmtCheck->bind_param("s", $nama_jurusan);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows == 0) {
            // Jurusan belum ada, tambahkan jurusan baru
            $queryInsertJurusan = "INSERT INTO jurusan (nama_jurusan, ketua_jurusan_id) VALUES (?, ?)";
            $stmtInsertJurusan = $conn->prepare($queryInsertJurusan);
            $stmtInsertJurusan->bind_param("si", $nama_jurusan, $dosen_id);
            $stmtInsertJurusan->execute();
        } else {
            // Jurusan sudah ada, update ketua_jurusan_id
            $row = $resultCheck->fetch_assoc();
            $jurusan_id = $row['id'];
            $queryUpdateJurusan = "UPDATE jurusan SET ketua_jurusan_id = ? WHERE id = ?";
            $stmtUpdateJurusan = $conn->prepare($queryUpdateJurusan);
            $stmtUpdateJurusan->bind_param("ii", $dosen_id, $jurusan_id);
            $stmtUpdateJurusan->execute();
        }
         // Simpan data kajur ke tabel users
         $queryInsertUser = "INSERT INTO users (username, password, role, email, dosen_id) VALUES (?, ?, ?, ?, ?)";
         $stmtUser = $conn->prepare($queryInsertUser);
         $stmtUser->bind_param("ssssi", $nip, $hashedPassword, $role, $email, $dosen_id);
         $stmtUser->execute();

    } elseif ($role === 'wakil_dekan') {
        // Menyimpan data Wakil Dekan ke tabel wakil_dekan
        $queryInsertWadek = "INSERT INTO wakil_dekan (nama, email, nip) VALUES (?, ?, ?)";
        $stmtWadek = $conn->prepare($queryInsertWadek);
        $stmtWadek->bind_param("sss", $nama_dosen, $email, $nip);
        $stmtWadek->execute();
        
        // Mendapatkan ID wakil dekan yang baru saja disimpan
        $wakil_dekan_id = $stmtWadek->insert_id;
        $role = 'wakil_dekan';
        
        // Update tabel users dengan menambahkan wakil_dekan_id
        $queryInsertUser = "INSERT INTO users (username, password, role, email, dosen_id, wakil_dekan_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtUser = $conn->prepare($queryInsertUser);
        $stmtUser->bind_param("ssssii", $nip, $hashedPassword, $role, $email, $dosen_id, $wakil_dekan_id); // Menambahkan wakil_dekan_id
        $stmtUser->execute();
    } else {
        // Simpan data user untuk role lain (kajur, mahasiswa, etc.)
        $queryInsertUser = "INSERT INTO users (username, password, role, email, dosen_id) VALUES (?, ?, ?, ?, ?)";
        $stmtUser = $conn->prepare($queryInsertUser);
        $stmtUser->bind_param("ssssi", $nip, $hashedPassword, $role, $email, $dosen_id);
        $stmtUser->execute();
    }

    // Redirect atau tampilkan pesan berhasil
    header('Location: list_dosen.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];

    // Update Wakil Dekan
    $queryUpdateWadek = "UPDATE wakil_dekan SET nama = ?, nip = ?, email = ? WHERE id = ?";
    $stmtUpdateWadek = $conn->prepare($queryUpdateWadek);
    $stmtUpdateWadek->bind_param("sssi", $nama, $nip, $email, $id);
    $stmtUpdateWadek->execute();

    // Update users table if necessary (e.g. updating role)
    $queryUpdateUser = "UPDATE users SET email = ? WHERE wakil_dekan_id = ?";
    $stmtUpdateUser = $conn->prepare($queryUpdateUser);
    $stmtUpdateUser->bind_param("si", $email, $id);
    $stmtUpdateUser->execute();
    
    header('Location: list_dosen.php');
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
    <link rel="icon" type="image/png" href="image/logoweb.png">

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
                <div class="header-title">List Dosen</div>
                <button type="button" class="btn btn-primary btn-export" data-toggle="modal" data-target="#tambahModal">
                        Tambah
                    </button>
                <div class="table-responsive">
                

                    <div class="header-title">Ketua Jurusan (Kajur)</div>
                        <table id="kajurTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Dosen</th>
                                    <th>NIP</th>
                                    <th>Email</th>
                                    <th>Jurusan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php while ($row = $resultKajur->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama_dosen']); ?></td>
                                        <td><?= $row['nip'] ? htmlspecialchars($row['nip']) : 'N/A'; ?></td>
                                        <td><?= htmlspecialchars($row['email']); ?></td>
                                        <td><?= $row['nama_jurusan'] ? htmlspecialchars($row['nama_jurusan']) : 'Belum Ada Jurusan'; ?></td>
                                        <td class="action-buttons text-center">
                                        <button class="btn btn-info btn-custom" data-toggle="modal" data-target="#editDosenModal"
    data-id="<?= $row['id']; ?>"
    data-nama_dosen="<?= htmlspecialchars($row['nama_dosen']); ?>"
    data-nip="<?= htmlspecialchars($row['nip']); ?>"
    data-email="<?= htmlspecialchars($row['email']); ?>"
    data-nama_jurusan="<?= htmlspecialchars($row['nama_jurusan']); ?>">
    <i class="fas fa-pen"></i>
</button>




                                        </td>

                                    </tr>
                                <?php endwhile; ?>
                                </tbody>

                        </table>

                        <div class="header-title">Wakil Dekan (Wadek)</div>
                        <table id="wadekTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Dosen</th>
                                    <th>NIP</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; ?>
                        <?php while ($row = $resultWadek->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= $row['nip'] ? htmlspecialchars($row['nip']) : 'N/A'; ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td class="action-buttons text-center">
                                <button class="btn btn-info btn-custom" data-toggle="modal" data-target="#editWadekModal" 
        data-id="<?= $row['id']; ?>" 
        data-nama="<?= htmlspecialchars($row['nama']); ?>" 
        data-nip="<?= htmlspecialchars($row['nip']); ?>" 
        data-email="<?= htmlspecialchars($row['email']); ?>">
    <i class="fas fa-pen"></i> Edit
</button>

    <button class="btn btn-danger btn-custom" onclick="confirmDeleteWadek(<?= $row['id']; ?>)">
        <i class="fas fa-trash-alt"></i>
    </button>
</td>

                            </tr>
                        <?php endwhile; ?>
                            </tbody>
                        </table>

                </div>
            </div>
        </div>
     <!-- Modal Tambah Dosen -->
     <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahModalLabel">Tambah Dosen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="list_dosen.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_dosen">Nama Dosen</label>
                        <input type="text" class="form-control" id="nama_dosen" name="nama_dosen" required>
                    </div>
                    <div class="form-group">
                        <label for="nip">NIP</label>
                        <input type="text" class="form-control" id="nip" name="nip" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="" disabled selected>Pilih Role</option>    
                            <option value="kajur">Ketua Jurusan</option>
                            <option value="wakil_dekan">Wakil Dekan</option>
                        </select>
                    </div>
                    <div class="form-group" id="jurusan-container" style="display: none;">
                        <label for="nama_jurusan">Nama Jurusan</label>
                        <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" placeholder="Masukkan Nama Jurusan" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Tambah Dosen</button>
                </div>
            </form>
        </div>
    </div>
</div>
   <!-- Modal Edit Dosen -->
   <div class="modal fade" id="editDosenModal" tabindex="-1" role="dialog" aria-labelledby="editDosenModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDosenModalLabel">Edit Dosen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>
            <form id="editDosenForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_nama_dosen">Nama Dosen</label>
                        <input type="text" class="form-control" id="edit_nama_dosen" name="nama_dosen" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nip">NIP</label>
                        <input type="text" class="form-control" id="edit_nip" name="nip" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_jurusan">Jurusan</label>
                        <input type="text" class="form-control" id="edit_nama_jurusan" name="nama_jurusan">
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal Edit Wakil Dekan -->
<!-- Modal Edit Wakil Dekan -->
<div class="modal fade" id="editWadekModal" tabindex="-1" role="dialog" aria-labelledby="editWadekModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWadekModalLabel">Edit Wakil Dekan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editWadekForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_wadek_id" name="id">
                    <div class="form-group">
                        <label for="edit_wadek_nama">Nama</label>
                        <input type="text" class="form-control" id="edit_wadek_nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_wadek_nip">NIP</label>
                        <input type="text" class="form-control" id="edit_wadek_nip" name="nip" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_wadek_email">Email</label>
                        <input type="email" class="form-control" id="edit_wadek_email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
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
          // JavaScript untuk menampilkan dropdown jurusan ketika role "Ketua Jurusan" dipilih
          $(document).ready(function() {
        // Handle role change event to show/hide the 'nama_jurusan' field
        $('#role').on('change', function() {
            var role = $(this).val();
            var jurusanContainer = $('#jurusan-container');
            var namaJurusanInput = $('#nama_jurusan');

            if (role === 'kajur') {
                // Show the 'nama_jurusan' field and make it required
                jurusanContainer.show();
                namaJurusanInput.attr('required', 'required');
            } else {
                // Hide the 'nama_jurusan' field and remove 'required' attribute
                jurusanContainer.hide();
                namaJurusanInput.removeAttr('required');
            }
        });
    });

$(document).ready(function () {
    // Submit form using AJAX
    $("#tambahDosenForm").submit(function (e) {
        e.preventDefault(); // Mencegah submit form biasa

        // Kirim data form melalui AJAX
        $.ajax({
            url: "list_dosen.php", // Action dari form
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                // Jika berhasil, kosongkan form
                $("#tambahDosenForm")[0].reset();
                $("#tambahModal").modal("hide"); // Tutup modal

                // Reload data tabel (refresh data dosen)
                loadDosenData();
            },
            error: function () {
                alert("Terjadi kesalahan, data tidak dapat ditambahkan.");
            },
        });
    });

    // Fungsi untuk memuat data dosen
    function loadDosenData() {
        $.ajax({
            url: "get_dosen_data.php", // Endpoint baru untuk mendapatkan data dosen
            type: "GET",
            success: function (response) {
                $("#kajurTable tbody").html(response); // Update tabel dengan data baru
            },
            error: function () {
                alert("Terjadi kesalahan saat memuat data dosen.");
            },
        });
    }

    // Muat data awal saat halaman dimuat
    loadDosenData();
});

$(document).on('click', '.btn-custom', function () {
    console.log(nama_dosen, nip, email, nama_jurusan);
    var button = $(this);
    var id = $(this).data('id');
    var nama_dosen = $(this).data('nama');
    var nip = $(this).data('nip');
    var email = $(this).data('email');
    var nama_jurusan = $(this).data('jurusan');
    var password = $(this).data('password');

    // Mengisi form edit dengan data dari tombol

    if (nama_dosen&&nip&& email&&nama_jurusan){
        $('#edit_id').val(id);
    $('#edit_nama_dosen').val(nama_dosen);
    $('#edit_nip').val(nip);
    $('#edit_email').val(email);
    $('#edit_nama_jurusan').val(nama_jurusan);
    $('#edit_password').val(password);
   

    // Tampilkan modal
    $('#editDosenModal').modal('show');
    }else {
        
    }
        
});




$('#editDosenForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: 'get_dosen_data.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            alert(response);
            $('#editDosenModal').modal('hide');
            loadDosenData(); // Refresh tabel dosen
        },
        error: function () {
            alert("Terjadi kesalahan, data tidak dapat diperbarui.");
        }
    });
});
/** 
// Reset modal fields when modal is closed
$('#editModal').on('hidden.bs.modal', function () {
    $('#edit_id').val('');
    $('#edit_nama_dosen').val('');
    $('#edit_nip').val('');
    $('#edit_email').val('');
    $('#edit_password').val('');
    $('#edit_nama_jurusan').val('');
});
**/
function loadDosenData() {
    $.ajax({
        url: 'get_dosen_data.php',
        type: 'GET',
        success: function (response) {
            $('#kajurTable tbody').html(response);
        },
        error: function () {
            alert("Terjadi kesalahan saat memuat data dosen.");
        }
    });
}
function confirmDelete(id) {
    if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
        // Lakukan penghapusan data melalui AJAX
        $.ajax({
            url: "delete_dosen.php", // Endpoint untuk menghapus data
            type: "POST",
            data: { id: id },
            success: function (response) {
                alert("Data berhasil dihapus.");
                
                // Menutup modal jika modal edit sedang terbuka
                if ($('#editDosenModal').is(':visible')) {
                    $('#editDosenModal').modal('hide'); // Menutup modal edit jika terbuka
                }
                
                // Reset form modal edit
                resetEditDosenForm();
                
                // Reload halaman untuk memperbarui tabel
                loadDosenDatahapus();
            },
            error: function () {
                alert("Terjadi kesalahan saat menghapus data.");
            },
        });
    }
}

function loadDosenDatahapus() {
    $.ajax({
        url: 'get_dosen_data.php', // Endpoint untuk mendapatkan data dosen terbaru
        type: 'GET',
        success: function(response) {
            $("#kajurTable tbody").html(response); // Memperbarui konten tabel
        },
        error: function() {
            alert("Terjadi kesalahan saat memuat data dosen.");
        }
    });
}

function resetEditDosenForm() {
    // Reset semua field pada form modal edit
    $('#edit_id').val('');
    $('#edit_nama_dosen').val('');
    $('#edit_nip').val('');
    $('#edit_email').val('');
    $('#edit_password').val('');
    $('#edit_nama_jurusan').val('');
}

// Script untuk mengisi data modal edit Wakil Dekan (Wadek)
$('#editWadekModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Tombol yang diklik
    var id = button.data('id');
    var nama = button.data('nama');
    var nip = button.data('nip');
    var email = button.data('email');

    // Isi field form dengan data yang dipilih
    var modal = $(this);
    modal.find('#edit_wadek_id').val(id);
    modal.find('#edit_wadek_nama').val(nama);
    modal.find('#edit_wadek_nip').val(nip);
    modal.find('#edit_wadek_email').val(email);
});

// Submit form edit Wadek
$('#editWadekForm').on('submit', function (e) {
    e.preventDefault(); // Mencegah form disubmit secara normal

    // Kirim data form melalui AJAX
    $.ajax({
        url: 'update_wadek.php', // Endpoint PHP untuk mengupdate data
        type: 'POST',
        data: $(this).serialize(), // Kirim data dari form
        success: function (response) {
            alert(response); // Tampilkan pesan sukses
            $('#editWadekModal').modal('hide'); // Tutup modal setelah update
            loadWadekData(); // Refresh data Wadek di halaman
        },
        error: function () {
            alert("Terjadi kesalahan saat memperbarui data.");
        }
    });
});

// Fungsi untuk memuat data Wadek terbaru
function loadWadekData() {
    $.ajax({
        url: 'get_wadek_data.php', // Endpoint untuk mendapatkan data Wadek terbaru
        type: 'GET',
        success: function (response) {
            $('#wadekTable tbody').html(response); // Memperbarui tabel Wadek
        },
        error: function () {
            alert("Terjadi kesalahan saat memuat data Wadek.");
        }
    });
}


function confirmDeleteWadek(id) {
    if (confirm("Apakah Anda yakin ingin menghapus data wakil dekan ini?")) {
        // Kirim id ke server untuk menghapus data
        $.ajax({
            url: "delete_wadek.php", // File untuk proses penghapusan
            type: "POST",
            data: { delete_wadek_id: id },
            success: function(response) {
                // Reload halaman untuk memperbarui tabel
                location.reload();
            },
            error: function() {
                alert("Terjadi kesalahan saat menghapus data.");
            }
        });
    }
}

$(document).ready(function () {
    // Tombol Close di modal
    $('.close').on('click', function () {
        $('#editDosenModal').modal('hide');
    });

    // Tombol Close di footer modal
    $('.btn-secondary').on('click', function () {
        $('#editDosenModal').modal('hide');
    });
});

   
</script>

        
    </body>
</html>
