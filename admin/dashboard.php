<?php
session_start();

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "travel";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$sql = "SELECT * FROM package";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --background-color: #f1f5f9;
            --card-color: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .navbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 1.8rem;
        }

        .menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .menu i {
            font-size: 1.2rem;
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .dashboard-content {
            background-color: var(--card-color);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h2 {
            font-size: 1.8rem;
            color: var(--text-primary);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        th {
            background-color: var(--background-color);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: linear-gradient(135deg, #2193b0, #6dd5ed);
        }

        td {
            padding: 1rem;
            background-color: white;
        }

        tr td:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }

        tr td:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }

        .package-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-edit {
            background-color: #10b981;
        }

        .btn-edit:hover {
            background-color: #059669;
        }

        .btn-delete {
            background-color: #ef4444;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }

        .price {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 1rem;
            }

            .menu {
                flex-direction: column;
                width: 100%;
            }

            .menu a {
                width: 100%;
                justify-content: center;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-compass"></i>
                Admin Dashboard
            </a>
            
            <div class="menu">
                <a href="manage_transaction.php">
                    <i class="fas fa-exchange-alt"></i>
                    Transaksi
                </a>
                <a href="manage_payments.php">
                    <i class="fas fa-credit-card"></i>
                    Pembayaran
                </a>
                <a href="registration_admin.php">
                    <i class="fas fa-user-plus"></i>
                    Pendaftaran
                </a>
                <a href="add_package.php">
                    <i class="fas fa-plus-circle"></i>
                    Tambah Paket
                </a>
                <a href="../index.html">
                    <i class="fas fa-home"></i>
                    Homepage
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-content">
            <div class="header">
                <h2>Kelola Paket Wisata</h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Paket</th>
                            <th>Harga</th>
                            <th>Durasi</th>
                            <th>Kategori</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_package']); ?></td>
                                    <td class="price">Rp <?php echo number_format($row['harga_package'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['durasi_package']); ?></td>
                                    <td>
                                        <span class="category">
                                            <?php echo htmlspecialchars($row['kategori_package']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <img src="../assets/images/<?php echo htmlspecialchars($row['foto_package']); ?>" 
                                             alt="Foto Paket" 
                                             class="package-image">
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_package.php?id=<?php echo $row['id_package']; ?>" 
                                               class="btn btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Ubah
                                            </a>
                                            <a href="delete_package.php?id=<?php echo $row['id_package']; ?>" 
                                               class="btn btn-delete"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus paket ini?')">
                                                <i class="fas fa-trash-alt"></i>
                                                Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Tidak ada paket yang tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Travel Website | Admin Dashboard</p>
    </footer>

</body>
</html>

<?php $conn->close(); ?>