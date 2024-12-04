<?php
include 'db.php';

// Ambil data Wakil Dekan
$queryWadek = "SELECT id, nama, nip, email FROM wakil_dekan";
$resultWadek = $conn->query($queryWadek);

$outputWadek = '';
$no = 1;
while ($row = $resultWadek->fetch_assoc()) {
    $outputWadek .= "
        <tr>
            <td class='text-center'>{$no}</td>
            <td>" . htmlspecialchars($row['nama']) . "</td>
            <td>" . htmlspecialchars($row['nip']) . "</td>
            <td>" . htmlspecialchars($row['email']) . "</td>
            <td class='action-buttons text-center'>
                <button class='btn btn-info btn-custom' data-bs-toggle='modal' data-bs-target='#editWadekModal'
                    data-id='{$row['id']}' data-nama='" . htmlspecialchars($row['nama']) . "'
                    data-nip='{$row['nip']}' data-email='" . htmlspecialchars($row['email']) . "'>
                    <i class='fas fa-pen'></i>
                </button>
                <button class='btn btn-danger btn-custom' onclick='confirmDeleteWadek({$row['id']})'>
                    <i class='fas fa-trash-alt'></i>
                </button>
            </td>
        </tr>
    ";
    $no++;
}
echo $outputWadek;
?>
