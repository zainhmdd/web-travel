<?php
include('../includes/db_connection.php');  // Pastikan koneksi ke database sudah benar

// Query untuk mengambil data transaksi
$query = "SELECT t.id_transaction, u.nama_user, p.nama_package, t.total_harga, t.status_pembayaran, t.tanggal_transaksi
          FROM transaction t
          JOIN package p ON t.id_package = p.id_package
          JOIN user u ON t.id_user = u.id_user";
$result = $conn->query($query);

// Nama file CSV yang akan diunduh
$filename = "laporan_transaksi_" . date("Y-m-d_H-i-s") . ".csv";

// Set header untuk mengunduh file CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Buka output untuk menulis file CSV
$output = fopen('php://output', 'w');

// Menulis header CSV
fputcsv($output, ['ID Transaksi', 'Nama User', 'Nama Paket', 'Jumlah Pembayaran', 'Status Pembayaran', 'Tanggal Transaksi']);

// Menulis data transaksi ke CSV
while ($row = $result->fetch_assoc()) {
    // Tulis setiap baris data transaksi ke file CSV
    fputcsv($output, [
        $row['id_transaction'], 
        $row['nama_user'], 
        $row['nama_package'], 
        number_format($row['total_harga'], 0, ',', '.'), // Formatkan angka dengan tanda koma
        $row['status_pembayaran'], 
        date('d/m/Y', strtotime($row['tanggal_transaksi']))
    ]);
}

// Tutup file output
fclose($output);

// Tutup koneksi ke database
$conn->close();
exit();
?>
