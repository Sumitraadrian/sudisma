<?php
// Tentukan direktori tempat menyimpan backup
$backup_dir = 'backups';

// Cek apakah folder backup sudah ada, jika belum buat folder
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true); // Membuat folder dengan izin 0755
}

// Nama file backup
$backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';

// Perintah mysqldump untuk backup database
$command = "mysqldump -u root -p'' dispen > $backup_file";

// Eksekusi perintah
system($command);

// Tampilkan pesan sukses
echo "Backup berhasil dibuat: $backup_file";
?>
