<?php
include('../includes/db_connection.php');
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: Login_user.php");  // Arahkan user untuk login jika belum login
    exit();
}

// Ambil ID user dari session
$id_user = $_SESSION['id_user'];

// Variabel untuk menampilkan pesan error atau success
$registration = null;
$error_message = "";

// Ambil data pendaftaran berdasarkan ID user
$stmt = $conn->prepare("SELECT r.id_registration, r.status_pendaftaran, r.tanggal_pendaftaran, p.nama_package
                        FROM registration r
                        JOIN package p ON r.id_package = p.id_package
                        WHERE r.id_user = ? ORDER BY r.tanggal_pendaftaran DESC LIMIT 1");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Ambil data pendaftaran terbaru
    $registration = $result->fetch_assoc();
} else {
    // Jika tidak ada pendaftaran ditemukan
    $error_message = "Anda belum mendaftar untuk paket wisata manapun.";
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran Paket Wisata - Travoury</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)),
                url('https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDB8fFRSQVZFTHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 40px;
            margin: 20px;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .success-icon i {
            color: white;
            font-size: 40px;
        }

        h2 {
            color: #2c3e50;
            font-size: 2.2em;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .status-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateX(10px);
        }

        .info-item i {
            font-size: 24px;
            margin-right: 15px;
            color: #3498db;
            width: 40px;
            text-align: center;
        }

        .info-item .label {
            font-weight: 600;
            color: #2c3e50;
            margin-right: 10px;
            min-width: 180px;
        }

        .info-item .value {
            color: #666;
            flex: 1;
            text-align: right;
        }

        .payment-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .payment-btn:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .payment-btn i {
            font-size: 20px;
        }

        .go-back {
            margin-top: 30px;
            text-align: center;
        }

        .go-back a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .go-back a:hover {
            transform: translateX(-5px);
            background: white;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }

        .error-message {
            background-color: #fee;
            color: #e74c3c;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            font-weight: 500;
            border-left: 4px solid #e74c3c;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        footer {
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
            width: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 1.8em;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
            }

            .info-item .label {
                margin-bottom: 5px;
                min-width: auto;
            }

            .info-item .value {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error_message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php elseif ($registration): ?>
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h2>Konfirmasi Pendaftaran</h2>

            <div class="status-info">
                <div class="info-item">
                    <i class="fas fa-suitcase"></i>
                    <span class="label">Paket Wisata:</span>
                    <span class="value"><?php echo htmlspecialchars($registration['nama_package']); ?></span>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span class="label">Tanggal Pendaftaran:</span>
                    <span class="value"><?php echo htmlspecialchars($registration['tanggal_pendaftaran']); ?></span>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-info-circle"></i>
                    <span class="label">Status Pendaftaran:</span>
                    <span class="value"><?php echo htmlspecialchars($registration['status_pendaftaran']); ?></span>
                </div>
            </div>

            <?php if ($registration['status_pendaftaran'] === 'Dalam Proses'): ?>
                <form action="payment.php" method="POST">
                    <input type="hidden" name="id_registration" value="<?php echo htmlspecialchars($registration['id_registration']); ?>">
                    <button type="submit" name="submit_payment" class="payment-btn">
                        <i class="fas fa-credit-card"></i>
                        Lakukan Pembayaran
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="go-back">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Travoury. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>