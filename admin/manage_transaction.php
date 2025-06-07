<?php
session_start();
include('../includes/db_connection.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

// Ambil data transaksi dari database
$query = "SELECT t.id_transaction, u.nama_user, p.nama_package, t.total_harga, t.status_pembayaran, t.tanggal_transaksi
          FROM transaction t
          JOIN package p ON t.id_package = p.id_package
          JOIN user u ON t.id_user = u.id_user";

$result = $conn->query($query);

// Periksa apakah query berhasil
if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            color: #1a1a1a;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            padding: 2rem;
            position: relative;
            box-shadow: 0 4px 20px rgba(107, 118, 255, 0.2);
        }

        header h1 {
            color: white;
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            text-align: center;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-success { background: linear-gradient(135deg, #43a047, #66bb6a); }
        .alert-danger { background: linear-gradient(135deg, #e53935, #ef5350); }

        .btn-primary {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(107, 118, 255, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(107, 118, 255, 0.3);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        th {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 1.2rem 1rem;
            font-weight: 500;
            text-align: left;
            font-size: 0.95rem;
        }

        td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-lunas {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff3e0;
            color: #ef6c00;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ff6b6b, #dc4b4b);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #43a047, #66bb6a);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            margin: 2rem auto;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .price-column {
            font-family: 'Roboto Mono', monospace;
            font-weight: 500;
            color: #2c3e50;
        }

        footer {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        .export-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.8rem;
            color: #2c3e50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Kelola Transaksi</h1>
    <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</header>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="export-section">
        <h2 class="section-title">Daftar Transaksi</h2>
        <a href="add_transaction.php" class="btn-primary">
        <i class="fas fa-plus"></i> Tambah Transaksi</a>
        <a href="export_csv.php" class="btn-primary">
            <i class="fas fa-file-export"></i> Ekspor ke CSV
        </a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Paket</th>
            <th>User</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($row['id_transaction']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_package']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_user']); ?></td>
                    <td class="price-column">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="status-badge <?php echo $row['status_pembayaran'] === 'Lunas' ? 'status-lunas' : 'status-pending'; ?>">
                            <?php echo htmlspecialchars($row['status_pembayaran']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['tanggal_transaksi']))); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit_transaction.php?id=<?php echo $row['id_transaction']; ?>" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_transaction.php?id=<?php echo $row['id_transaction']; ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data transaksi.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<center>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</center>

<footer>
    <p>&copy; 2025 Travel Website - All rights reserved</p>
</footer>

</body>
</html>