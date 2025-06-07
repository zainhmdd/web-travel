<?php
session_start();
include('../includes/db_connection.php');

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

// Proses penambahan transaksi
if (isset($_POST['submit'])) {
    $id_user = $_POST['id_user'];
    $id_package = $_POST['id_package'];
    $status_pembayaran = $_POST['status_pembayaran'];
    $total_harga = $_POST['total_harga'];

    // Validasi input
    if (empty($id_user) || empty($id_package) || empty($status_pembayaran) || empty($total_harga)) {
        $_SESSION['error'] = 'Semua data harus diisi!';
    } else {
        try {
            // Verify if package exists using mysqli
            $check_package = "SELECT id_package FROM package WHERE id_package = '$id_package'";
            $result = mysqli_query($conn, $check_package);
            
            if (!$result || mysqli_num_rows($result) === 0) {
                $_SESSION['error'] = 'ID Paket tidak valid!';
            } else {
                // Insert data transaksi ke database using mysqli
                $query = "INSERT INTO transaction (id_user, id_package, status_pembayaran, total_harga, tanggal_transaksi) 
                         VALUES ('$id_user', '$id_package', '$status_pembayaran', '$total_harga', NOW())";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['success'] = 'Transaksi berhasil ditambahkan!';
                    header('Location: manage_transaction.php');
                    exit();
                } else {
                    throw new Exception("Error: " . mysqli_error($conn));
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Gagal menambahkan transaksi: ' . $e->getMessage();
            error_log("Database Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f7fe;
            color: #2d3748;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        h2 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.8rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #86efac;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        button[type="submit"] {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem auto 0;
        }

        button[type="submit"]:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(99,102,241,0.2);
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }

            header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            h1 {
                font-size: 1.5rem;
            }

            .alert {
                padding: 0.8rem;
            }
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }

        .input-icon input {
            padding-left: 2.5rem;
        }
    </style>
</head>
<body>

<header>
    <h1><i class="fas fa-money-bill-wave"></i> Tambah Transaksi</h1>
    <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        Logout
    </a>
</header>

<div class="container">
    <h2><i class="fas fa-plus-circle"></i> Tambah Transaksi Baru</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="id_user"><i class="fas fa-user"></i> ID Pengguna</label>
            <div class="input-icon">
                <i class="fas fa-user"></i>
                <input type="number" name="id_user" id="id_user" required placeholder="Masukkan ID pengguna">
            </div>
        </div>

        <div class="form-group">
            <label for="id_package"><i class="fas fa-box"></i> ID Paket</label>
            <div class="input-icon">
                <i class="fas fa-box"></i>
                <input type="number" name="id_package" id="id_package" required placeholder="Masukkan ID paket">
            </div>
        </div>

        <div class="form-group">
            <label for="total_harga"><i class="fas fa-coins"></i> Total Harga</label>
            <div class="input-icon">
                <i class="fas fa-coins"></i>
                <input type="number" name="total_harga" id="total_harga" required placeholder="Masukkan total harga">
            </div>
        </div>

        <div class="form-group">
            <label for="status_pembayaran"><i class="fas fa-check-circle"></i> Status Pembayaran</label>
            <select name="status_pembayaran" id="status_pembayaran" required>
                <option value="">Pilih Status</option>
                <option value="Belum Dibayar">Belum Dibayar</option>
                <option value="Sudah Dibayar">Sudah Dibayar</option>
            </select>
        </div>

        <button type="submit" name="submit">
            <i class="fas fa-plus"></i>
            Tambah Transaksi
        </button>
    </form>
</div>

</body>
</html>