<?php
require '<lib>fpdf/fpdf.php';

function generateSuratDispensasi($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SURAT DISPENSASI', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, "Kepada Yth.,\n\n{$data['nama_lengkap']} (NIM: {$data['nim']})\n\nDengan alasan: {$data['alasan']}\n\nSurat dispensasi ini menyatakan bahwa mahasiswa tersebut di atas diberikan izin khusus untuk dispensasi.\n\nTertanda,\n\nWakil Dekan Fakultas Sains dan Teknologi", 0, 'L');
    $pdf->Output('F', "surat_dispensasi/Surat_Dispensasi_{$data['id']}.pdf");
    return "surat_dispensasi/Surat_Dispensasi_{$data['id']}.pdf";
}

?>
