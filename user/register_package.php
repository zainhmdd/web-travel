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
$package = null;
$error_message = "";

// Cek apakah ada ID paket yang dikirimkan melalui URL
if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $id_package = $_GET['id'];

    // Cek apakah paket valid di database
    $stmt = $conn->prepare("SELECT * FROM package WHERE id_package = ?");
    $stmt->bind_param("i", $id_package);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Paket ditemukan
        $package = $result->fetch_assoc();
    } else {
        // Jika paket tidak ditemukan
        $error_message = "Paket dengan ID tersebut tidak ditemukan. Pastikan ID paket valid.";
    }
} else {
    // Jika parameter ID tidak valid atau tidak dikirimkan
    $error_message = "ID paket tidak valid atau tidak diterima. Harap coba lagi.";
}

// Jika paket ditemukan, proses form
if ($package !== null) {
    // Proses form submission
    if (isset($_POST['submit'])) {
        // Ambil form data tambahan jika diperlukan (misalnya, catatan atau preferensi)
        $additional_note = $_POST['additional_note'];

        // Daftarkan user ke paket
        $stmt = $conn->prepare("INSERT INTO registration (id_user, id_package, status_pendaftaran, tanggal_pendaftaran, additional_note) VALUES (?, ?, 'Dalam Proses', NOW(), ?)");
        $stmt->bind_param("iis", $id_user, $id_package, $additional_note);
        $stmt->execute();
        $stmt->close();

        // Arahkan ke halaman konfirmasi
        header("Location: confirmation.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Paket Wisata</title>
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
            color: #333;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1541410965313-d53b3c16ef17?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDh8fHRyYXZlbHxlbnwwfHwwfHx8MA%3D%3D') center/cover;
            opacity: 0.3;
            z-index: 0;
        }

        .header h2 {
            font-size: 2.5em;
            margin: 0;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .content {
            padding: 40px;
        }

        .package-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .package-info .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .package-info .info-item:hover {
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
            min-width: 100px;
        }

        .info-item .value {
            color: #666;
            flex: 1;
        }

        form {
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 500;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            resize: vertical;
            min-height: 150px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .submit-btn {
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
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .submit-btn i {
            font-size: 20px;
        }

        .go-back {
            text-align: center;
            margin-top: 30px;
        }

        .go-back a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .go-back a:hover {
            color: #2980b9;
            transform: translateX(-5px);
        }

        .error-message {
            background-color: #fee;
            color: #e74c3c;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #e74c3c;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: white;
            margin-top: 40px;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h2 {
                font-size: 2em;
            }
            
            .content {
                padding: 20px;
            }
            
            .package-info {
                padding: 20px;
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
        <?php elseif ($package): ?>
            <div class="header">
                <h2>Pendaftaran Paket: <?php echo htmlspecialchars($package['nama_package']); ?></h2>
            </div>

            <div class="content">
                <div class="package-info">
                    <div class="info-item">
                        <i class="fas fa-tag"></i>
                        <span class="label">Harga:</span>
                        <span class="value"><?php echo htmlspecialchars($package['harga_package']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span class="label">Durasi:</span>
                        <span class="value"><?php echo htmlspecialchars($package['durasi_package']); ?> hari</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-info-circle"></i>
                        <span class="label">Deskripsi:</span>
                        <span class="value"><?php echo htmlspecialchars($package['deskripsi_package']); ?></span>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="additional_note">
                            <i class="fas fa-pencil-alt"></i> Catatan Khusus (Opsional):
                        </label>
                        <textarea 
                            name="additional_note" 
                            id="additional_note" 
                            placeholder="Masukkan preferensi atau permintaan khusus Anda untuk perjalanan ini..."></textarea>
                    </div>

                    <button type="submit" name="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Daftar Sekarang
                    </button>
                </form>

                <div class="go-back">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 Travoury. Semua Hak Dilindungi.</p>
    </footer>
</body>
</html>