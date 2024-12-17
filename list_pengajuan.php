<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$query = "SELECT * FROM pengajuan";
$result = $conn->query($query);

// Proses penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $deleteQuery = "DELETE FROM pengajuan WHERE id = $delete_id";
    if ($conn->query($deleteQuery) === TRUE) {
        echo "<script>alert('Data berhasil dihapus!');</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: " . $conn->error . "');</script>";
    }
}

// Query untuk mengambil data pengajuan berdasarkan jurusan
$query = "SELECT p.*, j.nama_jurusan FROM pengajuan p 
          INNER JOIN jurusan j ON p.jurusan_id = j.id
          WHERE p.status = 'disetujui'
          ORDER BY j.nama_jurusan, p.tanggal_pengajuan DESC";
$result = $conn->query($query);

if ($result === false) {
    die("Error: " . $conn->error);
}

// Menyiapkan data pengajuan berdasarkan jurusan
$pengajuanByJurusan = [];
while ($row = $result->fetch_assoc()) {
    $jurusan = $row['nama_jurusan'];
    if (!isset($pengajuanByJurusan[$jurusan])) {
        $pengajuanByJurusan[$jurusan] = [];
    }
    $pengajuanByJurusan[$jurusan][] = $row;
}

// Mengambil nama Wakil Dekan
$userId = $_SESSION['user_id'];
$queryWadek = "SELECT wakil_dekan.nama AS namaWadek FROM wakil_dekan
               JOIN users ON wakil_dekan.id = users.wakil_dekan_id
               WHERE users.id = ?";
$stmt = $conn->prepare($queryWadek);
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultWadek = $stmt->get_result();
$namaWadek = ($resultWadek->num_rows > 0) ? $resultWadek->fetch_assoc()['namaWadek'] : "Unknown";
$stmt->close();

// Sanitasi nama jurusan
function sanitizeJurusan($jurusan) {
    return preg_replace('/[^a-zA-Z0-9_]/', '_', $jurusan);
}
?>

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Dispensasi</title>
    <link rel="icon" type="image/png" href="image/logoweb.png?v=1">
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
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
            left: -250px; /* Sidebar tersembunyi di kiri */
            width: 250px; /* Lebar sidebar */
            transition: left 0.3s ease; /* Animasi ketika sidebar muncul dari kiri */
            z-index: 1000;
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sidebar h4 {
            text-align: center;
            color: white ;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: black;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar.visible {
        left: 0; /* Sidebar muncul dari kiri */
    }
        
        .main-content {
            
            margin-left: 250px; /* Beri ruang agar konten tidak tertutup sidebar */
        padding: 20px;
        transition: margin-left 0.3s ease;
        z-index: 1;
        padding-top: 60px; 
        }
        .status-badge {
            padding: 3px 8px;
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
            padding: 3px 5px;
            font-size: 0.85em;
            border-radius: 3px;
        }
        .table-container {
            padding: 20px;
            background-color: #ffffff;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 0; /* Pastikan tidak ada margin di atas tabel */
        }
        .table thead th {
            background-color: #a3c1e0;
            color: black;
            font-size: 0.9em;
        }
        .table-container h2 {
    font-size: 1.5em; /* Size of the title */
    font-weight: bold;
    margin-bottom: 20px;
    text-align: left;
    color: #333;
}
        .table th, .table td {
            font-size: 0.85em;
            padding: 8px;
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                left: -100%; /* Sidebar tersembunyi di luar layar */
                top: 0;
            }
            .sidebar.visible {
            left: 0; /* Sidebar muncul dari atas pada layar kecil */
            }
            .main-content {
                margin-left: 0;
                padding-top: 60px; /* Beri ruang di atas untuk navbar */
            }
            .sidebar a {
                font-size: 14px;
                padding: 8px 16px;
            }
            .table th, .table td {
                font-size: 0.75em;
                padding: 6px;
            }
            .table-container {
                padding: 10px;
                margin-top: 0;
            }
        }
        @media (max-width: 576px) {
            .sidebar {
                position: fixed;
            width: 100%;
            height: 100%;
            left: -100%;
            top: 0;
            }
            .main-content {
                padding-top: 60px; /* Pastikan ada ruang untuk navbar */
                margin-left: 0; 
            }
            .table th, .table td {
                font-size: 0.7em;
                padding: 5px;
            }
            .table-container {
                margin-top: 0;
                padding: 10px; /* Sesuaikan padding untuk layar kecil */
            }
            .header-title{
                font-size: small;
            }
            .table-container .h3{
                font-size: small;
            }
        }
        .header-title {
    font-size: 1.5em;  /* Menambah ukuran font judul */
    font-weight: bold;  /* Membuat teks lebih tebal */
    margin-bottom: 20px;  /* Memberi ruang di bawah judul */
    text-align: left;  /* Menjaga teks tetap di tengah */
    color: #333;  /* Warna teks yang sedikit gelap untuk kontras */
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

    </style>
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
<div class="table-container mt-5">
    <h2 class="mb-4">Riwayat Pengajuan Dispensasi Berdasarkan Jurusan</h2>
    <a href="export_pengajuan.php" class="btn btn-success">
            <i class="fa fa-file-excel"></i> Export to Excel
        </a>
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs" id="jurusanTabs" role="tablist">
        <?php foreach ($pengajuanByJurusan as $jurusan => $pengajuans): 
            $jurusan_sanitized = sanitizeJurusan($jurusan);
        ?>
        <li class="nav-item" role="presentation">
            <button 
                class="nav-link <?php echo $jurusan === array_key_first($pengajuanByJurusan) ? 'active' : ''; ?>" 
                id="tab-<?= $jurusan_sanitized; ?>" 
                data-bs-toggle="tab" 
                data-bs-target="#content-<?= $jurusan_sanitized; ?>" 
                type="button" 
                role="tab" 
                aria-controls="content-<?= $jurusan_sanitized; ?>" 
                aria-selected="true">
                <?= htmlspecialchars($jurusan); ?>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content mt-3" id="jurusanTabsContent">
        <?php foreach ($pengajuanByJurusan as $jurusan => $pengajuans): 
            $jurusan_sanitized = sanitizeJurusan($jurusan);
        ?>
        <div 
            class="tab-pane fade <?php echo $jurusan === array_key_first($pengajuanByJurusan) ? 'show active' : ''; ?>" 
            id="content-<?= $jurusan_sanitized; ?>" 
            role="tabpanel" 
            aria-labelledby="tab-<?= $jurusan_sanitized; ?>">
            <!-- Tabel Jurusan -->
            <div class="table-responsive">
                <table id="dispensasiTable_<?= $jurusan_sanitized; ?>" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                            <th>NIM</th>
                            <th>Angkatan</th>
                            <th>Alasan</th>
                            <th>Tanggal Awal Pengajuan</th>
                            <th>Tanggal Akhir Pengajuan</th>
                            <th>Status Kajur</th>
                            <th>Status Wadek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($pengajuans as $row): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?= htmlspecialchars($row['nim']); ?></td>
                            <td><?= htmlspecialchars($row['angkatan']); ?></td>
                            <td><?= htmlspecialchars($row['alasan']); ?></td>
                            <td><?= htmlspecialchars($row['tanggal_pengajuan']); ?></td>
                            <td><?= htmlspecialchars($row['akhir_pengajuan']); ?></td>
                            <td class="text-center">
                                <?php if ($row['status'] == 'pending'): ?>
                                    <span class="status-badge status-belum-diproses">Belum diproses</span>
                                <?php elseif ($row['status'] == 'disetujui'): ?>
                                    <span class="status-badge status-diterima">Diterima</span>
                                <?php elseif ($row['status'] == 'ditolak'): ?>
                                    <span class="status-badge status-ditolak">Ditolak</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($row['status_wadek'] == 'disetujui final'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php elseif ($row['status_wadek'] == 'ditolak'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dispensasiTable').DataTable({
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
                { "orderable": false, "targets": 7 }
            ]
        });
    });

    function confirmDelete(id) {
            if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
                window.location.href = "list_pengajuan.php?delete_id=" + id;
            }
        }
        
    $(document).ready(function () {
    $('table[id^="dispensasiTable_"]').each(function () {
        $(this).DataTable({
            "paging": true,
            responsive: true,
            "pagingType": "simple_numbers",
            "ordering": true, // Mengaktifkan pengurutan
            "info": false, // Menonaktifkan informasi di bawah tabel
            "columnDefs": [
                {
                    "targets": 0, // Target kolom nomor urut (indeks 0)
                    "orderable": false, // Nonaktifkan pengurutan pada kolom ini
                    "render": function (data, type, row, meta) {
                        return meta.row + 1; // Menampilkan nomor urut berdasarkan baris
                    }
                }
            ]
        });
    });
});

document.getElementById("sidebarToggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");

        if (window.innerWidth <= 768) {
            // Mode mobile: toggle dari atas
            sidebar.classList.toggle("visible");
        } else {
            // Mode desktop: toggle dari kiri
            sidebar.classList.toggle("visible");
        }
    });
</script>

</body>
</html>
