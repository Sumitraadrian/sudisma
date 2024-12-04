<?php
include 'db.php';

if (isset($_POST['delete_wadek_id'])) {
    $id = $_POST['delete_wadek_id'];

    // Step 1: Ambil data user yang terkait dengan wakil dekan dari tabel users
    $queryGetUserId = "SELECT id FROM users WHERE wakil_dekan_id = ?";
    $stmtGetUserId = $conn->prepare($queryGetUserId);
    $stmtGetUserId->bind_param("i", $id);
    $stmtGetUserId->execute();
    $result = $stmtGetUserId->get_result();

    if ($result->num_rows > 0) {
        // Ambil user id yang terkait dengan wakil dekan
        $userRow = $result->fetch_assoc();
        $user_id = $userRow['id'];

        // Step 2: Hapus data di tabel users yang terkait dengan wakil dekan
        $queryDeleteUser = "DELETE FROM users WHERE id = ?";
        $stmtDeleteUser = $conn->prepare($queryDeleteUser);
        $stmtDeleteUser->bind_param("i", $user_id);
        $stmtDeleteUser->execute();
    }

    // Step 3: Hapus data wakil dekan dari tabel wakil_dekan
    $queryDeleteWadek = "DELETE FROM wakil_dekan WHERE id = ?";
    $stmtDeleteWadek = $conn->prepare($queryDeleteWadek);
    $stmtDeleteWadek->bind_param("i", $id);
    $stmtDeleteWadek->execute();

    // Redirect atau beri notifikasi berhasil
    header("Location: list_dosen.php");
    exit();
}
?>
