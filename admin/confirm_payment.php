<?php
session_start();
require_once('../includes/db_connection.php');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Function to safely redirect
function safeRedirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit();
    }
}

// Function to validate transaction ID
function validateTransactionId($id) {
    return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
}

try {
    // Check admin authentication
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        safeRedirect('login.php');
    }

    // Verify CSRF token if you have it implemented
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     throw new Exception('Invalid security token');
    // }

    // Check if ID exists and is valid
    if (!isset($_GET['id']) || !validateTransactionId($_GET['id'])) {
        throw new Exception('ID transaksi tidak valid');
    }

    $id_transaction = (int)$_GET['id'];

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE transaction SET 
                           status_pembayaran = 'Sudah Bayar',
                           updated_at = CURRENT_TIMESTAMP
                           WHERE id_transaction = ?");

    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan query: ' . $conn->error);
    }

    $stmt->bind_param('i', $id_transaction);

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengeksekusi query: ' . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception('Tidak ada transaksi yang diupdate');
    }

    // Log the successful payment confirmation
    $log_stmt = $conn->prepare("INSERT INTO activity_log (admin_id, action, description) 
                               VALUES (?, 'payment_confirmation', ?)");
    
    if ($log_stmt) {
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $description = "Konfirmasi pembayaran untuk transaksi ID: " . $id_transaction;
        $log_stmt->bind_param('is', $admin_id, $description);
        $log_stmt->execute();
        $log_stmt->close();
    }

    // Set success response
    $response['success'] = true;
    $response['message'] = 'Pembayaran berhasil dikonfirmasi';
    $response['redirect'] = 'manage_transactions.php';

    // Close the main statement
    $stmt->close();

} catch (Exception $e) {
    // Set error response
    $response['message'] = $e->getMessage();
    error_log('Payment Confirmation Error: ' . $e->getMessage());
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}

// Handle the response
if ($response['success']) {
    // Store success message in session for display after redirect
    $_SESSION['success_message'] = $response['message'];
    safeRedirect($response['redirect']);
} else {
    // Store error message in session
    $_SESSION['error_message'] = $response['message'];
    safeRedirect('manage_transactions.php');
}
?>