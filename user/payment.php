<?php
include('../includes/db_connection.php');
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: Login_user.php");
    exit();
}

function validateFile($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return "Tipe file tidak diizinkan. Hanya JPG, PNG, dan PDF yang diperbolehkan.";
    }
    if ($file['size'] > $max_size) {
        return "Ukuran file terlalu besar. Maksimal 5MB.";
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_status = $_POST['payment_status'] ?? '';
    $payment_amount = $_POST['payment_amount'] ?? '';
    $bukti_transaksi = $_FILES['bukti_transaksi'] ?? null;
    $error = null;

    // Hanya validasi bukti transaksi jika status pembayaran adalah Lunas
    if ($payment_status === 'Lunas') {
        if (empty($bukti_transaksi) || $bukti_transaksi['error'] === 4) { // 4 means no file uploaded
            $error = "Bukti transaksi wajib diupload untuk pembayaran Lunas.";
        } else {
            $file_validation = validateFile($bukti_transaksi);
            if ($file_validation !== true) {
                $error = $file_validation;
            }
        }
    }

    if (!$error && !empty($payment_status) && !empty($payment_amount)) {
        $upload_path = null;
        
        // Proses upload file jika ada dan status Lunas
        if ($payment_status === 'Lunas' && !empty($bukti_transaksi)) {
            $upload_dir = '../uploads/bukti_transaksi/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($bukti_transaksi['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;

            if (!move_uploaded_file($bukti_transaksi['tmp_name'], $upload_path)) {
                $error = "Gagal mengupload file.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("SELECT id_transaction FROM transaction WHERE id_user = ? LIMIT 1");
            if ($stmt === false) {
                die('Query preparation failed: ' . $conn->error);
            }

            $stmt->bind_param("i", $_SESSION['id_user']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id_transaction);
            $stmt->fetch();

            if ($id_transaction) {
                $stmt = $conn->prepare("INSERT INTO paymentmethod (id_transaction, payment_status, payment_amount, payment_date, bukti_transaksi) VALUES (?, ?, ?, NOW(), ?)");
                if ($stmt === false) {
                    die('Query preparation failed: ' . $conn->error);
                }

                $relative_path = $upload_path ? 'uploads/bukti_transaksi/' . $unique_filename : null;
                $stmt->bind_param("isds", $id_transaction, $payment_status, $payment_amount, $relative_path);

                if ($stmt->execute()) {
                    header("Location: paymentsucces.php?id_transaction=" . $id_transaction);
                    exit();
                } else {
                    $error = "Gagal memperbarui data pembayaran.";
                }
                $stmt->close();
            } else {
                $error = "ID transaksi tidak ditemukan.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran</title>
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
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDB8fFRSQVZFTHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
            filter: blur(5px);
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h2 {
            font-size: 28px;
            color: #1a237e;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #1a237e, #3949ab);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #1a237e;
            margin-bottom: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
            color: #333;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(145deg, #f0f0f0, #ffffff);
            border: 2px dashed #1a237e;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            transform: translateY(-2px);
        }

        .file-upload-input {
            display: none;
        }

        .file-icon {
            font-size: 2rem;
            color: #1a237e;
            margin-bottom: 10px;
        }

        .file-name {
            margin-top: 8px;
            font-size: 0.9rem;
            color: #666;
            text-align: center;
            word-break: break-word;
        }

        .confirm-btn {
            width: 100%;
            padding: 15px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #1a237e, #3949ab);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(26, 35, 126, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .confirm-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(26, 35, 126, 0.3);
            background: linear-gradient(135deg, #3949ab, #1a237e);
        }

        .confirm-btn:active {
            transform: translateY(0);
        }

        .error-message {
            color: #fff;
            background-color: rgba(220, 53, 69, 0.9);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .upload-section {
            display: none;
        }

        .upload-section.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #1a237e;
            pointer-events: none;
        }

        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Konfirmasi Pembayaran</h2>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="payment_amount">
                    <i class="fas fa-money-bill"></i>
                    Jumlah yang Dibayar
                </label>
                <div class="input-icon">
                    <input type="number" 
                           id="payment_amount" 
                           name="payment_amount" 
                           placeholder="Masukkan jumlah pembayaran"
                           required>
                    <i class="fas fa-rupiah-sign"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="payment_status">
                    <i class="fas fa-check-circle"></i>
                    Status Pembayaran
                </label>
                <div class="input-icon">
                    <select name="payment_status" id="payment_status" required onchange="toggleUploadSection()">
                        <option value="">Pilih status pembayaran</option>
                        <option value="Lunas">Lunas</option>
                        <option value="Menunggu">Menunggu</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <div id="uploadSection" class="form-group upload-section">
                <label for="bukti_transaksi">
                    <i class="fas fa-file-upload"></i>
                    Bukti Transaksi
                </label>
                <div class="file-upload">
                    <input type="file" 
                           id="bukti_transaksi" 
                           name="bukti_transaksi" 
                           class="file-upload-input"
                           accept=".jpg,.jpeg,.png,.pdf"
                           onchange="updateFileName(this)">
                    <label for="bukti_transaksi" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt file-icon"></i>
                        <span>Klik untuk upload file</span>
                        <div class="file-name"></div>
                    </label>
                </div>
            </div>

            <button type="submit" class="confirm-btn">
                <i class="fas fa-check-circle"></i>
                Konfirmasi Pembayaran
            </button>
        </form>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0]?.name;
            const fileNameDiv = input.parentElement.querySelector('.file-name');
            fileNameDiv.textContent = fileName || '';
        }

        function toggleUploadSection() {
            const status = document.getElementById('payment_status').value;
            const uploadSection = document.getElementById('uploadSection');
            const fileInput = document.getElementById('bukti_transaksi');
            
            if (status === 'Lunas') {
                uploadSection.classList.add('active');
                fileInput.required = true;
            } else {
                uploadSection.classList.remove('active');
                fileInput.required = false;
                fileInput.value = '';
                const fileNameDiv = uploadSection.querySelector('.file-name');
                fileNameDiv.textContent = '';
            }
        }
    </script>
</body>
</html>