<?php
session_start();
include('../includes/db_connection.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

// Pastikan ada parameter 'id' dalam URL
if (isset($_GET['id'])) {
    // Ambil ID transaksi dari URL
    $id_transaction = $_GET['id'];

    // Cek apakah transaksi ada dalam database
    $query = "SELECT * FROM transaction WHERE id_transaction = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_transaction);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Mulai transaksi untuk memastikan konsistensi data
        $conn->begin_transaction();

        try {
            // Hapus data yang terkait di tabel paymentmethod
            $deletePaymentMethodQuery = "DELETE FROM paymentmethod WHERE id_transaction = ?";
            $deletePaymentMethodStmt = $conn->prepare($deletePaymentMethodQuery);
            $deletePaymentMethodStmt->bind_param('i', $id_transaction);
            $deletePaymentMethodStmt->execute();

            // Hapus transaksi dari tabel transaction
            $deleteTransactionQuery = "DELETE FROM transaction WHERE id_transaction = ?";
            $deleteTransactionStmt = $conn->prepare($deleteTransactionQuery);
            $deleteTransactionStmt->bind_param('i', $id_transaction);
            $deleteTransactionStmt->execute();

            // Commit transaksi jika semua berhasil
            $conn->commit();

            $_SESSION['success'] = 'Transaksi berhasil dihapus!';
        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan
            $conn->rollback();
            $_SESSION['error'] = 'Gagal menghapus transaksi. Error: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Transaksi tidak ditemukan.';
    }
} else {
    $_SESSION['error'] = 'ID transaksi tidak valid.';
}

// Redirect kembali ke halaman kelola transaksi
header('Location: manage_transaction.php');
exit();
?>
