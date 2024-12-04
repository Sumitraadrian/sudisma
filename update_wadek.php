<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];

    if (!empty($id)) {
        // Update data Wakil Dekan
        $query = "UPDATE wakil_dekan SET nama = ?, nip = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $nama, $nip, $email, $id);
    } else {
        // Tambah data Wakil Dekan
        $query = "INSERT INTO wakil_dekan (nama, nip, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $nama, $nip, $email);
    }

    if ($stmt->execute()) {
        echo "Data berhasil disimpan!";
    } else {
        echo "Terjadi kesalahan: " . $stmt->error;
    }
}
?>
