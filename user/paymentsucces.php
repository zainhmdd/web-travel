<?php
include('../includes/db_connection.php');
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: Login_user.php");
    exit();
}

// Ambil parameter id_transaction dari URL
$id_transaction = $_GET['id_transaction'] ?? null;

if ($id_transaction) {
    // Ambil data transaksi berdasarkan id_transaction
    $stmt = $conn->prepare("SELECT *, DATE_FORMAT(departure_date, '%d %M %Y') as formatted_departure_date FROM paymentmethod WHERE id_transaction = ?");
    $stmt->bind_param("i", $id_transaction);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
    } else {
        echo "Pembayaran tidak ditemukan.";
        exit();
    }
} else {
    echo "ID Transaksi tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, rgba(41, 128, 185, 0.9), rgba(46, 204, 113, 0.9)),
                        url('https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDB8fFRSQVZFTHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-icon {
            font-size: 80px;
            color: #2ecc71;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        h2 {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 700;
            animation: slideDown 0.5s ease-out;
        }

        .success-message {
            font-size: 18px;
            color: #34495e;
            margin-bottom: 30px;
            line-height: 1.6;
            animation: fadeIn 0.5s ease-out 0.3s both;
        }

        .transaction-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 16px;
            color: #2c3e50;
            border: 2px dashed #e0e0e0;
            animation: fadeIn 0.5s ease-out 0.6s both;
        }

        .transaction-details p {
            margin: 10px 0;
            font-family: 'Poppins', sans-serif;
        }

        .departure-date {
            color: #2980b9;
            font-weight: 600;
            font-size: 18px;
            margin-top: 10px;
        }

        .back-btn {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 16px 40px;
            font-size: 18px;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.5s ease-out 0.9s both;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .back-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
        }

        .back-btn:hover::after {
            animation: shimmer 1.5s infinite;
        }

        .decoration {
            position: absolute;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            border-radius: 50%;
            opacity: 0.1;
        }

        .decoration:nth-child(1) {
            width: 100px;
            height: 100px;
            top: -50px;
            left: -50px;
        }

        .decoration:nth-child(2) {
            width: 150px;
            height: 150px;
            bottom: -75px;
            right: -75px;
        }

        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="decoration"></div>
        <div class="decoration"></div>
        
        <i class="fas fa-check-circle success-icon"></i>
        <h2>Pembayaran Berhasil!</h2>

        <?php if (isset($payment)): ?>
            <p class="success-message">Selamat! Pembayaran Anda telah berhasil dikonfirmasi.</p>
            <div class="transaction-details">
                <p><strong>ID Transaksi:</strong> <?php echo htmlspecialchars($id_transaction); ?></p>
                <?php if ($payment['departure_date']): ?>
                    <p class="departure-date">
                        <i class="fas fa-plane-departure"></i>
                        Tanggal Keberangkatan: <?php echo htmlspecialchars($payment['formatted_departure_date']); ?>
                    </p>
                <?php endif; ?>
            </div>
            <p class="success-message">Terima kasih telah memilih layanan kami. Kami sangat menghargai kepercayaan Anda.</p>
        <?php endif; ?>

        <a href="index.php" class="back-btn">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
    </div>
</body>
</html>