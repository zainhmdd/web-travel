<?php
include('../includes/db_connection.php');

// Cek apakah ada parameter 'id' dalam URL
if (isset($_GET['id'])) {
    $id_package = $_GET['id'];

    // Persiapkan query untuk menghapus data dari tabel paymentmethod
    $stmt = $conn->prepare("DELETE FROM package WHERE id_package = ?");
    $stmt->bind_param("i", $id_package);

    // Eksekusi query untuk menghapus data
    if ($stmt->execute()) {
        // Set pesan sukses dan arahkan kembali ke halaman manage_payments.php
        $_SESSION['success'] = 'PACKAGE berhasil dihapus!';
        header("Location: dashboard.php");
        exit();
    } else {
        // Set pesan error jika terjadi kesalahan
        $_SESSION['error'] = 'Terjadi kesalahan saat menghapus pembayaran: ' . $stmt->error;
        header("Location: dashboard.php");
        exit();
    }

    // Menutup statement
    $stmt->close();
} else {
    // Jika ID tidak valid, set pesan error
    $_SESSION['error'] = 'ID package tidak valid.';
    header("Location: dashboard.php");
    exit();
}
?>
