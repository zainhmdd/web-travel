<?php
session_start();
include('../includes/db_connection.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

// Ambil id transaksi dari URL
if (isset($_GET['id'])) {
    $id_transaction = $_GET['id'];

    // Ambil data transaksi berdasarkan ID
    $query = "SELECT t.id_transaction, t.status_pembayaran 
              FROM transaction t 
              WHERE t.id_transaction = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_transaction);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = 'Transaksi tidak ditemukan!';
        header('Location: manage_transaction.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'ID transaksi tidak ditemukan!';
    header('Location: manage_transaction.php');
    exit();
}

// Proses pembaruan status transaksi
if (isset($_POST['submit'])) {
    $status_pembayaran = $_POST['status_pembayaran'];

    // Update status transaksi
    $query = "UPDATE transaction SET status_pembayaran = ? WHERE id_transaction = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status_pembayaran, $id_transaction);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Status transaksi berhasil diperbarui.';
        header('Location: manage_transaction.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal memperbarui status transaksi.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f7fe;
            color: #2d3748;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 1.5rem 2rem;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #86efac;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .status-unpaid {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-paid {
            background: #dcfce7;
            color: #16a34a;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        button:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            header {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <h1>
            <i class="fas fa-edit"></i>
            Edit Status Transaksi
        </h1>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</header>

<div class="container">
    <h2>
        <i class="fas fa-sync-alt"></i>
        Update Status Transaksi #<?php echo $transaction['id_transaction']; ?>
    </h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="status-badge <?php echo $transaction['status_pembayaran'] == 'Sudah Dibayar' ? 'status-paid' : 'status-unpaid'; ?>">
        <i class="fas <?php echo $transaction['status_pembayaran'] == 'Sudah Dibayar' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
        Status Saat Ini: <?php echo $transaction['status_pembayaran']; ?>
    </div>

    <form method="POST" action="edit_transaction.php?id=<?php echo $transaction['id_transaction']; ?>">
        <div class="form-group">
            <label for="status_pembayaran">
                <i class="fas fa-money-bill-wave"></i>
                Status Pembayaran
            </label>
            <select name="status_pembayaran" id="status_pembayaran" required>
                <option value="Belum Dibayar" <?php echo $transaction['status_pembayaran'] == 'Belum Dibayar' ? 'selected' : ''; ?>>Belum Dibayar</option>
                <option value="Sudah Dibayar" <?php echo $transaction['status_pembayaran'] == 'Sudah Dibayar' ? 'selected' : ''; ?>>Sudah Dibayar</option>
            </select>
        </div>

        <button type="submit" name="submit">
            <i class="fas fa-save"></i>
            Update Status
        </button>
    </form>
</div>

</body>
</html>