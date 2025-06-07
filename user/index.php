<?php
include('../includes/db_connection.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travoury - Paket Wisata</title>
    <script src="../assets/js/script.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background: 
                        url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.7));
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-sizing: border-box;
        }

        .navbar .logo {
            color: #f39c12;
            font-size: 28px;
            font-weight: 700;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .navbar .nav-right a {
            background-color: #f39c12;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 25px;
            margin-left: 15px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 2px solid transparent;
        }

        .navbar .nav-right a:hover {
            background-color: transparent;
            border-color: #f39c12;
            color: #f39c12;
        }

        /* Hero Section with Search */
        .hero-section {
            height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-top: 60px;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3));
            text-align: center;
        }

        .hero-section h1 {
            color: white;
            font-size: 3em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* Search Bar */
        .search-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .search-container form {
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.95);
            padding: 5px;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .search-container input {
            flex: 1;
            padding: 15px 25px;
            font-size: 16px;
            border: none;
            background: transparent;
            outline: none;
            color: #333;
        }

        .search-container button {
            padding: 15px 30px;
            font-size: 18px;
            background-color: #f39c12;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-container button:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 2.5em;
            color: #f39c12;
            margin: 30px 0;
            position: relative;
            padding-bottom: 15px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: #f39c12;
        }

        /* Card Layout */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover img {
            transform: scale(1.05);
        }

        .card-body {
            padding: 20px;
        }

        .card-body h3 {
            font-size: 1.4em;
            color: #35424a;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .card-body p {
            font-size: 0.95em;
            color: #666;
            margin: 8px 0;
            line-height: 1.6;
        }

        .card-body .price {
            font-size: 1.5em;
            font-weight: 700;
            color: #f39c12;
            margin: 15px 0;
        }

        .btn-detail {
            display: inline-block;
            background-color: #35424a;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-align: center;
            width: auto;
            margin-top: 15px;
        }

        .btn-detail:hover {
            background-color: #f39c12;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: linear-gradient(to right, #35424a, #2c3e50);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                height: 50vh;
                padding-top: 80px;
            }

            .hero-section h1 {
                font-size: 2em;
            }

            .navbar {
                padding: 10px 15px;
            }

            .navbar .logo {
                font-size: 24px;
            }

            .navbar .nav-right a {
                padding: 8px 15px;
                font-size: 14px;
            }

            .card-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <a href="index.php" class="logo">Travoury</a>
        <div class="nav-right">
            <a href="../index.html">Homepage</a>
            <a href="transaction_history.php">Riwayat Transaksi</a>
            <a href="logout_user.php">Logout</a>
        </div>
    </div>

    <!-- Hero Section with Search -->
    <div class="hero-section">
        <h1>Temukan Petualangan Anda</h1>
        <!-- Search Bar -->
        <div class="search-container">
            <form action="index.php" method="get">
                <input type="text" name="search" placeholder="Cari paket wisata impian Anda..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h2>Paket Wisata Pilihan</h2>

        <!-- Card Layout -->
        <div class="card-container">
            <?php
            if ($search) {
                $query = "SELECT * FROM package WHERE nama_package LIKE ? OR kategori_package LIKE ?";
                $stmt = $conn->prepare($query);
                $searchParam = "%" . $search . "%";
                $stmt->bind_param("ss", $searchParam, $searchParam);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<img src='../assets/images/" . (empty($row['foto_package']) ? 'default.jpg' : $row['foto_package']) . "' alt='Foto Paket Wisata'>";
                        echo "<div class='card-body'>";
                        echo "<h3>" . htmlspecialchars($row['nama_package']) . "</h3>";
                        echo "<p><strong>Kategori:</strong> " . htmlspecialchars($row['kategori_package']) . "</p>";
                        echo "<p><strong>Durasi:</strong> " . htmlspecialchars($row['durasi_package']) . " Hari</p>";
                        echo "<p class='price'>Rp " . number_format($row['harga_package'], 0, ',', '.') . "</p>";
                        echo "<a href='view_package.php?id=" . $row['id_package'] . "' class='btn-detail'>Lihat Detail</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div style='text-align: center; width: 100%; padding: 20px;'>";
                    echo "<p style='font-size: 1.2em; color: #666;'>Maaf, paket wisata tidak ditemukan.</p>";
                    echo "</div>";
                }
            } else {
                $query = "SELECT * FROM package";
                $result = $conn->query($query);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='card'>";
                        echo "<img src='../assets/images/" . (empty($row['foto_package']) ? 'default.jpg' : $row['foto_package']) . "' alt='Foto Paket Wisata'>";
                        echo "<div class='card-body'>";
                        echo "<h3>" . htmlspecialchars($row['nama_package']) . "</h3>";
                        echo "<p><strong>Kategori:</strong> " . htmlspecialchars($row['kategori_package']) . "</p>";
                        echo "<p><strong>Durasi:</strong> " . htmlspecialchars($row['durasi_package']) . " Hari</p>";
                        echo "<p class='price'>Rp " . number_format($row['harga_package'], 0, ',', '.') . "</p>";
                        echo "<a href='view_package.php?id=" . $row['id_package'] . "' class='btn-detail'>Lihat Detail</a>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Travoury - Your Travel Partner</p>
    </footer>
</body>
</html>