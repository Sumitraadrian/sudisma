<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $dosen_id = $_POST['id'];

    // Cek apakah dosen merupakan Ketua Jurusan (kajur)
    $queryCheckJurusan = "SELECT id FROM jurusan WHERE ketua_jurusan_id = ?";
    $stmt = $conn->prepare($queryCheckJurusan);
    $stmt->bind_param("i", $dosen_id);
    $stmt->execute();
    $resultCheck = $stmt->get_result();

    if ($resultCheck->num_rows > 0) {
        // Jika dosen adalah Ketua Jurusan, hapus jurusan terlebih dahulu
        $queryDeleteJurusan = "DELETE FROM jurusan WHERE ketua_jurusan_id = ?";
        $stmtDeleteJurusan = $conn->prepare($queryDeleteJurusan);
        $stmtDeleteJurusan->bind_param("i", $dosen_id);
        $stmtDeleteJurusan->execute();
    }

    // Hapus data dosen dari tabel `users` yang terkait dengan dosen
    $queryDeleteUser = "DELETE FROM users WHERE dosen_id = ?";
    $stmtDeleteUser = $conn->prepare($queryDeleteUser);
    $stmtDeleteUser->bind_param("i", $dosen_id);
    $stmtDeleteUser->execute();

    // Menghapus dosen dari tabel `dosen`
    $queryDeleteDosen = "DELETE FROM dosen WHERE id = ?";
    $stmtDeleteDosen = $conn->prepare($queryDeleteDosen);
    $stmtDeleteDosen->bind_param("i", $dosen_id);

    if ($stmtDeleteDosen->execute()) {
        echo "Dosen berhasil dihapus";
    } else {
        echo "Error deleting dosen: " . $stmtDeleteDosen->error;
    }
}
?>
