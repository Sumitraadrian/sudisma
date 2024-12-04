<?php
include 'db.php';



$query =  "SELECT dosen.id, dosen.nama_dosen, dosen.nip, dosen.email, jurusan.nama_jurusan 
FROM dosen 
LEFT JOIN jurusan ON jurusan.ketua_jurusan_id = dosen.id";
$result = $conn->query($query);

$output = '';
$no = 1;

while ($row = $result->fetch_assoc()) {
    $output .= '
        <tr>
            <td class="text-center">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['nama_dosen']) . '</td>
            <td>' . htmlspecialchars($row['nip']) . '</td>
            <td>' . htmlspecialchars($row['email']) . '</td>
            <td>' . htmlspecialchars($row['nama_jurusan'] ?? 'Tidak Diketahui') . '</td>
            <td class="action-buttons text-center">
                <button class="btn btn-info btn-custom" data-bs-toggle="modal" 
                    data-bs-target="#editDosenModal" 
                    data-id="' . $row['id'] . '"
                    data-nama="' . htmlspecialchars($row['nama_dosen']) . '"
                    data-nip="' . htmlspecialchars($row['nip']) . '"
                    data-email="' . htmlspecialchars($row['email']) . '"
                    data-jurusan="' . htmlspecialchars($row['nama_jurusan']) . '">
                    <i class="fas fa-pen"></i>
                </button>
                <button class="btn btn-danger btn-custom" onclick="confirmDelete(' . $row['id'] . ')">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    ';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nama_dosen = $_POST['nama_dosen'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nama_jurusan = $_POST['nama_jurusan'];

    // Validasi data input
    if (empty($id) || empty($nama_dosen) || empty($nip) || empty($email)) {
        echo "Semua data wajib diisi!";
        exit();
    }

    // Update data dosen
    $queryUpdate = "UPDATE dosen SET nama_dosen = ?, nip = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($queryUpdate);
    $stmt->bind_param("sssi", $nama_dosen, $nip, $email, $id);
    if (!$stmt->execute()) {
        echo "Gagal memperbarui data dosen: " . $stmt->error;
        exit();
    }

    // Update jurusan jika ada nama jurusan
    if (!empty($nama_jurusan)) {
        $queryJurusan = "UPDATE jurusan SET nama_jurusan = ? WHERE ketua_jurusan_id = ?";
        $stmtJurusan = $conn->prepare($queryJurusan);
        $stmtJurusan->bind_param("si", $nama_jurusan, $id);
        if (!$stmtJurusan->execute()) {
            echo "Gagal memperbarui data jurusan: " . $stmtJurusan->error;
            exit();
        }
    }

    // Update ke tabel users
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $queryUpdateUser = "UPDATE users SET password = ?, email = ? WHERE dosen_id = ?";
        $stmtUser = $conn->prepare($queryUpdateUser);
        $stmtUser->bind_param("ssi", $hashedPassword, $email, $id);
    } else {
        $queryUpdateUser = "UPDATE users SET email = ? WHERE dosen_id = ?";
        $stmtUser = $conn->prepare($queryUpdateUser);
        $stmtUser->bind_param("si", $email, $id);
    }

    if (!$stmtUser->execute()) {
        echo "Gagal memperbarui data pengguna: " . $stmtUser->error;
        exit();
    }

    echo "Data berhasil diperbarui!";
    exit();
}




echo $output;
?>
