<?php
include('../includes/db_connection.php');

// Periksa apakah parameter 'id' ada dalam URL
if (isset($_GET['id'])) {
    // Menangani input untuk mencegah SQL Injection
    $id_package = $_GET['id'];
    
    // Menyusun query dengan prepared statement untuk keamanan
    $query = $conn->prepare("SELECT * FROM package WHERE id_package = ?");
    $query->bind_param("i", $id_package);  // 'i' menunjukkan tipe data integer
    $query->execute();
    $result = $query->get_result();

    // Jika data ditemukan
    if ($result->num_rows > 0) {
        $package = $result->fetch_assoc();
    } else {
        // Jika data tidak ditemukan, tampilkan pesan error
        $package = null;
        echo "Paket wisata tidak ditemukan.";
    }
} else {
    echo "ID paket tidak diberikan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Paket Wisata</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDB8fFRSQVZFTHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #333;
            line-height: 1.6;
        }

        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.5rem;
        }

        .package-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .package-header {
            position: relative;
            height: 400px;
            overflow: hidden;
        }

        .package-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .package-header:hover img {
            transform: scale(1.05);
        }

        .package-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .package-details h2 {
            font-size: 2.5rem;
            color: #2d3436;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .detail-item:hover {
            transform: translateX(10px);
            background: #f1f3f5;
        }

        .detail-item i {
            font-size: 1.2rem;
            margin-right: 1rem;
            color: #0984e3;
        }

        .detail-label {
            font-weight: 600;
            margin-right: 0.5rem;
            color: #2d3436;
        }

        .detail-value {
            color: #636e72;
        }

        .price-tag {
            background: linear-gradient(135deg, #0984e3, #00cec9);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: 700;
            display: inline-block;
            margin: 1.5rem 0;
            box-shadow: 0 4px 15px rgba(9, 132, 227, 0.3);
        }

        .description-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1rem;
        }

        .description-box p {
            color: #636e72;
            line-height: 1.8;
        }

        .register-button {
            display: inline-block;
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.3);
        }

        .register-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 184, 148, 0.4);
        }

        .register-button i {
            margin-left: 0.5rem;
        }

        footer {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .package-content {
                grid-template-columns: 1fr;
            }

            .container {
                margin: 1.5rem auto;
            }

            .package-header {
                height: 300px;
            }

            .detail-item:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Detail Paket Wisata</h1>
</header>

<div class="container">
    <?php if ($package): ?>
    <div class="package-card">
        <div class="package-header">
            <?php if (!empty($package['foto_package'])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($package['foto_package']); ?>" alt="<?php echo htmlspecialchars($package['nama_package']); ?>">
            <?php else: ?>
                <img src="/api/placeholder/1200/400" alt="Default Package Image">
            <?php endif; ?>
        </div>

        <div class="package-content">
            <div class="package-details">
                <h2><?php echo htmlspecialchars($package['nama_package']); ?></h2>
                
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span class="detail-label">Durasi:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($package['durasi_package']); ?> Hari</span>
                </div>

                <div class="detail-item">
                    <i class="fas fa-tag"></i>
                    <span class="detail-label">Kategori:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($package['kategori_package']); ?></span>
                </div>

                <div class="price-tag">
                    <i class="fas fa-tag"></i> Rp <?php echo number_format($package['harga_package'], 0, ',', '.'); ?>
                </div>

                <a href="register_package.php?id=<?php echo $package['id_package']; ?>" class="register-button">
                    Daftar Sekarang <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="package-description">
                <div class="detail-item">
                    <i class="fas fa-info-circle"></i>
                    <span class="detail-label">Deskripsi</span>
                </div>
                <div class="description-box">
                    <p><?php echo htmlspecialchars($package['deskripsi_package']); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="package-card">
        <div class="package-content">
            <p>Data paket tidak tersedia.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2025 Travel Website - All rights reserved</p>
</footer>

</body>
</html>