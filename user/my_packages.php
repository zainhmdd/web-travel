<?php
include('../includes/db_connection.php');
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    echo "Anda harus login terlebih dahulu.";
    exit();
}

// Ambil ID user dari session
$id_user = $_SESSION['id_user'];

// Ambil data pendaftaran yang terkait dengan user
$stmt = $conn->prepare("SELECT r.id_registration, p.nama_package, r.status_pendaftaran, r.tanggal_pendaftaran 
                        FROM registration r
                        JOIN package p ON r.id_package = p.id_package
                        WHERE r.id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Packages</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .package {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .status {
            font-weight: bold;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .status.Diterima {
            background-color: #28a745;
        }
        .status.Dibatalkan {
            background-color: #dc3545;
        }
        .status.DalamProses {
            background-color: #ffc107;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <h1 class="text-center">My Packages</h1>
</header>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="package">
                <h3><?php echo htmlspecialchars($row['nama_package']); ?></h3>
                <p><strong>Status Pendaftaran:</strong>
                    <span class="status <?php echo htmlspecialchars($row['status_pendaftaran']); ?>">
                        <?php echo htmlspecialchars($row['status_pendaftaran']); ?>
                    </span>
                </p>
                <p><strong>Tanggal Pendaftaran:</strong> <?php echo htmlspecialchars($row['tanggal_pendaftaran']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Tidak ada pendaftaran untuk paket wisata ini.</p>
    <?php endif; ?>
</div>

<footer>
    <p class="text-center">&copy; 2025 Travel Website</p>
</footer>

</body>
</html>

<?php
$stmt->close();
?>
