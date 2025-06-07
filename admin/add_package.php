<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../includes/db_connection.php');

$success_message = '';
$error_message = '';

if (isset($_POST['submit'])) {
    try {
        // Get form data and validate
        if (empty($_POST['nama_package']) || empty($_POST['harga_package']) || 
            empty($_POST['deskripsi_package']) || empty($_POST['durasi_package']) || 
            empty($_POST['kategori_package'])) {
            throw new Exception("Semua field harus diisi!");
        }

        $nama_package = trim($_POST['nama_package']);
        $harga_package = floatval($_POST['harga_package']);
        $deskripsi_package = trim($_POST['deskripsi_package']);
        $durasi_package = trim($_POST['durasi_package']);
        $kategori_package = trim($_POST['kategori_package']);
        
        // File upload handling
        if (!isset($_FILES['foto_package']) || $_FILES['foto_package']['error'] !== 0) {
            throw new Exception("Harap pilih file foto!");
        }

        $file = $_FILES['foto_package'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Hanya file JPG, JPEG, dan PNG yang diperbolehkan!");
        }

        // Create upload directory
        $upload_dir = '../assets/images/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Gagal membuat direktori upload!");
            }
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        // Upload file
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception("Gagal mengupload file!");
        }

        // SQL query to insert data into the database
        $sql = "INSERT INTO package (
            nama_package, 
            harga_package, 
            deskripsi_package, 
            durasi_package, 
            kategori_package,
            foto_package
        ) VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare statement error: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("sdssss", 
            $nama_package,
            $harga_package,
            $deskripsi_package,
            $durasi_package,
            $kategori_package,
            $filename // Ensure the filename is passed here
        );

        if ($stmt->execute()) {
            $success_message = "Paket wisata berhasil ditambahkan!";
            // Log success
            error_log("Data berhasil dimasukkan ke database. Insert ID: " . $stmt->insert_id);
            // Clear form data
            $_POST = array();
        } else {
            throw new Exception("Gagal menambahkan data: " . $stmt->error);
        }

        $stmt->close();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Error in add_package.php: " . $error_message);
        
        // If file was uploaded but database insert failed, delete the file
        if (isset($target_path) && file_exists($target_path)) {
            unlink($target_path);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Paket Wisata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --error-color: #ef4444;
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

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .back-button {
            position: absolute;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Container Styles */
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background-color: var(--card-color);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            padding: 2rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--error-color);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-label {
            display: block;
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 0.5rem;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
            background-color: #f1f5f9;
        }

        .file-input-label i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        input[type="file"] {
            display: none;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .submit-btn i {
            margin-right: 0.5rem;
        }

        /* Footer Styles */
        footer {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                padding: 1.5rem;
            }

            .back-button {
                position: static;
                transform: none;
                margin-bottom: 1rem;
                display: inline-flex;
            }

            .container {
                padding: 0 1rem;
            }

            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Dashboard
        </a>
        <h1>Tambah Paket Wisata</h1>
        <p>Isi formulir di bawah untuk menambahkan paket wisata baru</p>
    </header>

    <div class="container">
        <div class="card">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_package">Nama Paket</label>
                    <input type="text" class="form-control" id="nama_package" name="nama_package" 
                           value="<?php echo isset($_POST['nama_package']) ? htmlspecialchars($_POST['nama_package']) : ''; ?>" 
                           placeholder="Masukkan nama paket wisata" required>
                </div>

                <div class="form-group">
                    <label for="harga_package">Harga Paket (Rp)</label>
                    <input type="number" class="form-control" id="harga_package" name="harga_package" 
                           value="<?php echo isset($_POST['harga_package']) ? htmlspecialchars($_POST['harga_package']) : ''; ?>" 
                           placeholder="Masukkan harga paket" required min="0" step="0.01">
                </div>

                <div class="form-group">
                    <label for="deskripsi_package">Deskripsi Paket</label>
                    <textarea class="form-control" id="deskripsi_package" name="deskripsi_package" 
                              placeholder="Masukkan deskripsi lengkap paket wisata" required><?php 
                        echo isset($_POST['deskripsi_package']) ? htmlspecialchars($_POST['deskripsi_package']) : ''; 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label for="durasi_package">Durasi Paket</label>
                    <input type="text" class="form-control" id="durasi_package" name="durasi_package" 
                           value="<?php echo isset($_POST['durasi_package']) ? htmlspecialchars($_POST['durasi_package']) : ''; ?>" 
                           placeholder="Contoh: 3 Hari 2 Malam" required>
                </div>

                <div class="form-group">
                    <label for="kategori_package">Kategori Paket</label>
                    <input type="text" class="form-control" id="kategori_package" name="kategori_package" 
                           value="<?php echo isset($_POST['kategori_package']) ? htmlspecialchars($_POST['kategori_package']) : ''; ?>" 
                           placeholder="Contoh: Wisata Alam, Wisata Budaya" required>
                </div>

                <div class="form-group">
                    <label for="foto_package">Foto Paket</label>
                    <div class="file-input-wrapper">
                        <label class="file-input-label" for="foto_package">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Pilih foto paket wisata
                        </label>
                        <input type="file" id="foto_package" name="foto_package" accept="image/*" required>
                    </div>
                </div>

                <button type="submit" name="submit" class="submit-btn">
                    <i class="fas fa-plus-circle"></i>
                    Tambah Paket
                </button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Travel Website. All Rights Reserved.</p>
    </footer>

</body>
</html>