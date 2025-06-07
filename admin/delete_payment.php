<?php
include('../includes/db_connection.php');

// Cek apakah ada parameter 'id' dalam URL
if (isset($_GET['id'])) {
    $id_payment = $_GET['id'];

    // Persiapkan query untuk menghapus data dari tabel paymentmethod
    $stmt = $conn->prepare("DELETE FROM paymentmethod WHERE id_payment = ?");
    $stmt->bind_param("i", $id_payment);

    // Eksekusi query untuk menghapus data
    if ($stmt->execute()) {
        // Set pesan sukses dan arahkan kembali ke halaman manage_payments.php
        $_SESSION['success'] = 'Pembayaran berhasil dihapus!';
        header("Location: manage_payments.php");
        exit();
    } else {
        // Set pesan error jika terjadi kesalahan
        $_SESSION['error'] = 'Terjadi kesalahan saat menghapus pembayaran: ' . $stmt->error;
        header("Location: manage_payments.php");
        exit();
    }

    // Menutup statement
    $stmt->close();
} else {
    // Jika ID tidak valid, set pesan error
    $_SESSION['error'] = 'ID pembayaran tidak valid.';
    header("Location: manage_payments.php");
    exit();
}
?>
