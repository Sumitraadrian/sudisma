<?php
session_start();
include 'db.php';
require 'vendor/autoload.php';  // Pastikan Anda sudah install PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // Query untuk mengambil data pengajuan berdasarkan rentang tanggal
    $query_pengajuan = "SELECT * FROM pengajuan WHERE tanggal_pengajuan BETWEEN '$start_date' AND '$end_date' ORDER BY tanggal_pengajuan DESC";
    $result_pengajuan = mysqli_query($conn, $query_pengajuan);

    if (mysqli_num_rows($result_pengajuan) > 0) {
        // Membuat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menambahkan header ke file Excel
        $sheet->setCellValue('A1', 'No')
              ->setCellValue('B1', 'Nama Lengkap')
              ->setCellValue('C1', 'NIM')
              ->setCellValue('D1', 'Angkatan')
              ->setCellValue('E1', 'Tanggal Pengajuan')
              ->setCellValue('F1', 'Status');

        // Menggunakan array untuk menyimpan data dan menulis dalam chunk
        $row_num = 2;
        $data = [];

        while ($row = mysqli_fetch_assoc($result_pengajuan)) {
            $data[] = [
                $row_num - 1,                // No
                $row['nama_lengkap'],        // Nama Lengkap
                $row['nim'],                 // NIM
                $row['angkatan'],            // Angkatan
                $row['tanggal_pengajuan'],   // Tanggal Pengajuan
                ucfirst($row['status'])      // Status
            ];
            $row_num++;

            // Tulis data setiap 1000 baris untuk menghindari memory overflow
            if (count($data) >= 1000) {
                $sheet->fromArray($data, null, 'A' . ($row_num - count($data)));
                $data = []; // Reset array data
            }
        }

        // Tulis sisa data yang ada
        if (count($data) > 0) {
            $sheet->fromArray($data, null, 'A' . ($row_num - count($data)));
        }

        // Menyimpan file Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'Pengajuan_' . $start_date . '_to_' . $end_date . '.xlsx';

        // Mengatur header untuk unduhan file Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Menyimpan dan mengirim file Excel
        $writer->save('php://output');
    } else {
        echo "Tidak ada data dalam rentang tanggal yang dipilih.";
    }
} else {
    echo "Silakan pilih rentang tanggal terlebih dahulu.";
}
?>
