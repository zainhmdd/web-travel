<?php
session_start();
include('../includes/db_connection.php');

if (!isset($_SESSION['id_user'])) {
    header("Location: Login_user.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$query = "
    SELECT t.id_transaction, p.nama_package, t.status_pembayaran, t.total_harga, t.tanggal_transaksi 
    FROM transaction t 
    JOIN package p ON t.id_package = p.id_package 
    WHERE t.id_user = ? 
    ORDER BY t.tanggal_transaksi DESC
";

if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query failed: " . $conn->error);
}

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_GET['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="riwayat_transaksi.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID Transaksi', 'Nama Paket', 'Status Pembayaran', 'Total Harga', 'Tanggal Transaksi'));
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['id_transaction'],
                $row['nama_package'],
                $row['status_pembayaran'],
                'Rp ' . number_format($row['total_harga'], 0, ',', '.'),
                date("d-m-Y H:i", strtotime($row['tanggal_transaksi']))
            ));
        }
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Travoury</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #22c55e;
            --background-color: #f8fafc;
            --card-background: rgba(255, 255, 255, 0.95);
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            color: var(--accent-color);
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-home {
            background-color: transparent;
            color: white;
            border: 1px solid white;
        }

        .btn-home:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn-logout {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-logout:hover {
            background-color: var(--secondary-color);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            flex-grow: 1;
        }

        .page-header {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--success-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }

        .transaction-card {
            background: var(--card-background);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transaction-table th {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            padding: 1rem;
            text-align: left;
        }

        .transaction-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-secondary);
        }

        .transaction-table tr:last-child td {
            border-bottom: none;
        }

        .transaction-table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success-color);
        }

        .price {
            font-weight: 600;
            color: var(--text-primary);
        }

        .date {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0.5rem;
            }

            .transaction-card {
                overflow-x: auto;
            }

            .transaction-table th,
            .transaction-table td {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">
            <i class="fas fa-plane-departure"></i>
            Travoury
        </a>
        <div class="nav-buttons">
            <a href="index.php" class="nav-btn btn-home">
                <i class="fas fa-home"></i> Beranda
            </a>
            <a href="logout_user.php" class="nav-btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Riwayat Transaksi</h1>
            <a href="transaction_history.php?download_csv=true" class="btn-download">
                <i class="fas fa-download"></i>
                Download Bukti Transaksi
            </a>
        </div>

        <div class="transaction-card">
            <?php if ($result->num_rows > 0): ?>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Nama Paket</th>
                            <th>Status</th>
                            <th>Total Harga</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($row['id_transaction']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_package']); ?></td>
                                <td>
                                    <span class="status-badge status-success">
                                        <?php echo htmlspecialchars($row['status_pembayaran']); ?>
                                    </span>
                                </td>
                                <td class="price">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td class="date"><?php echo date("d M Y H:i", strtotime($row['tanggal_transaksi'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-receipt" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <p>Anda belum memiliki transaksi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Travoury - Your Travel Partner</p>
    </footer>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>