<?php
// update_payment_status.php
require_once('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_payment = $_POST['id_payment'];
    $payment_status = $_POST['payment_status'];

    // Update status pembayaran di database
    $sql = "UPDATE paymentmethod SET payment_status = ? WHERE id_payment = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $payment_status, $id_payment);

    if ($stmt->execute()) {
        echo "Status berhasil diperbarui";
    } else {
        echo "Terjadi kesalahan";
    }

    $stmt->close();
    $conn->close();
}
?>

