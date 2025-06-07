<?php
session_start();
require_once('../includes/db_connection.php');

if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

// Validate package ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID paket tidak valid.';
    header('Location: dashboard.php');
    exit();
}

$id_package = (int)$_GET['id'];

// Fetch package data
try {
    $stmt = $conn->prepare("SELECT * FROM package WHERE id_package = ?");
    $stmt->bind_param("i", $id_package);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Paket wisata tidak ditemukan.');
    }

    $package = $result->fetch_assoc();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('status' => 'error', 'message' => '');
    
    try {
        $nama_package = $_POST['nama_package'];
        $harga_package = str_replace(',', '', $_POST['harga_package']); // Remove any commas
        $deskripsi_package = $_POST['deskripsi_package'];
        $durasi_package = $_POST['durasi_package'];
        $kategori_package = $_POST['kategori_package'];
        
        // Validation
        if (empty($nama_package)) {
            throw new Exception("Nama paket harus diisi!");
        }
        if (!is_numeric($harga_package)) {
            throw new Exception("Harga harus berupa angka!");
        }
        
        // Handle file upload
        $foto_package = $package['foto_package']; // Default to existing photo
        
        if (!empty($_FILES['foto_package']['name'])) {
            $uploadDir = "../assets/images/";
            $fileExtension = strtolower(pathinfo($_FILES['foto_package']['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                throw new Exception("Hanya file JPG, JPEG & PNG yang diperbolehkan!");
            }
            
            // Validate file size (5MB max)
            if ($_FILES['foto_package']['size'] > 5000000) {
                throw new Exception("File terlalu besar! Maksimal 5MB");
            }
            
            // Generate unique filename
            $foto_package = uniqid() . '.' . $fileExtension;
            $targetFile = $uploadDir . $foto_package;
            
            // Upload file
            if (!move_uploaded_file($_FILES['foto_package']['tmp_name'], $targetFile)) {
                throw new Exception("Gagal mengupload file!");
            }
            
            // Delete old file if exists
            if ($package['foto_package'] && file_exists($uploadDir . $package['foto_package'])) {
                unlink($uploadDir . $package['foto_package']);
            }
        }
        
        // Update database
        $stmt = $conn->prepare("UPDATE package SET 
            nama_package = ?,
            harga_package = ?,
            deskripsi_package = ?,
            durasi_package = ?,
            kategori_package = ?,
            foto_package = ?
            WHERE id_package = ?");
            
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("ssssssi", 
            $nama_package,
            $harga_package,
            $deskripsi_package,
            $durasi_package,
            $kategori_package,
            $foto_package,
            $id_package
        );
        
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Paket wisata berhasil diperbarui!';
        } else {
            throw new Exception("Gagal memperbarui data: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paket Wisata - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        /* Custom styles */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem;
            border-radius: 0.5rem;
            color: white;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: 1000;
        }

        .toast.success {
            background-color: #48bb78;
        }

        .toast.error {
            background-color: #f56565;
        }

        .toast.show {
            opacity: 1;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #4a5568;
        }

        .file-input {
            border: 2px dashed #e2e8f0;
            padding: 2rem;
            text-align: center;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #3498db;
        }

        .file-input i {
            font-size: 2rem;
            color: #a0aec0;
            margin-bottom: 1rem;
        }

        .current-image {
            margin-top: 1rem;
            padding: 0.75rem;
            background: #f7fafc;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .full {
            grid-column: 1 / -1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="loading">
        <div class="loading-spinner"></div>
    </div>

    <div id="toast" class="toast"></div>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 px-6 py-4">
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-edit"></i>
                    Edit Paket Wisata
                </h2>
                <p class="text-blue-100">Perbarui informasi paket wisata Anda</p>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_package">
                            <i class="fas fa-suitcase text-blue-500"></i>
                            Nama Paket
                        </label>
                        <input type="text" 
                               name="nama_package" 
                               id="nama_package" 
                               value="<?php echo htmlspecialchars($package['nama_package']); ?>" 
                               required
                               class="focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="form-group">
                        <label for="harga_package">
                            <i class="fas fa-tag text-blue-500"></i>
                            Harga Paket
                        </label>
                        <input type="number" 
                               name="harga_package" 
                               id="harga_package" 
                               value="<?php echo htmlspecialchars($package['harga_package']); ?>" 
                               required
                               class="focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="form-group">
                        <label for="durasi_package">
                            <i class="fas fa-clock text-blue-500"></i>
                            Durasi
                        </label>
                        <input type="text" 
                               name="durasi_package" 
                               id="durasi_package" 
                               value="<?php echo htmlspecialchars($package['durasi_package']); ?>" 
                               required
                               class="focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="form-group">
                        <label for="kategori_package">
                            <i class="fas fa-list text-blue-500"></i>
                            Kategori
                        </label>
                        <input type="text" 
                               name="kategori_package" 
                               id="kategori_package" 
                               value="<?php echo htmlspecialchars($package['kategori_package']); ?>" 
                               required
                               class="focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="form-group full">
                        <label for="deskripsi_package">
                            <i class="fas fa-align-left text-blue-500"></i>
                            Deskripsi
                        </label>
                        <textarea name="deskripsi_package" 
                                  id="deskripsi_package" 
                                  required
                                  class="focus:ring-2 focus:ring-blue-500"
                                  rows="4"><?php echo htmlspecialchars($package['deskripsi_package']); ?></textarea>
                    </div>

                    <div class="form-group full">
                        <label>
                            <i class="fas fa-image text-blue-500"></i>
                            Foto Paket
                        </label>
                        <div class="file-input hover:border-blue-500 transition-colors">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p class="text-gray-500">Klik atau seret file ke sini</p>
                            <input type="file" 
                                   name="foto_package" 
                                   id="foto_package" 
                                   accept="image/*"
                                   class="hidden">
                        </div>
                        <div class="current-image">
                            <i class="fas fa-image text-blue-500"></i>
                            <span>Foto saat ini: <?php echo htmlspecialchars($package['foto_package']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        const form = $('#editForm');
        const toast = $('#toast');
        const loading = $('.loading');
        let isSubmitting = false;

        function showLoading() {
            loading.css('display', 'flex');
            $('.btn-submit').prop('disabled', true);
        }

        function hideLoading() {
            loading.hide();
            $('.btn-submit').prop('disabled', false);
            isSubmitting = false;
        }

        function showToast(message, type = 'success') {
            toast.text(message)
                 .removeClass()
                 .addClass(`toast ${type} show`);
            
            setTimeout(() => {
                toast.removeClass('show');
            }, 3000);
        }

        function validateForm() {
            const required = {
                'nama_package': 'Nama Paket',
                'harga_package': 'Harga Paket',
                'deskripsi_package': 'Deskripsi',
                'durasi_package': 'Durasi',
                'kategori_package': 'Kategori'
            };
            
            for (const [field, label] of Object.entries(required)) {
                const value = $(`#${field}`).val().trim();
                if (!value) {
                    showToast(`${label} harus diisi!`, 'error');
                    $(`#${field}`).focus();
                    return false;
                }
            }

            const harga = $('#harga_package').val();
            if (isNaN(harga) || parseInt(harga) < 0) {
                showToast('Harga harus berupa angka positif!', 'error');
                $('#harga_package').focus();
                return false;
            }

            return true;
        }

        form.on('submit', function(e) {
            e.preventDefault();
            
            if (isSubmitting || !validateForm()) {
                return false;
            }

            isSubmitting = true;
            showLoading();

            const formData = new FormData(this);
            
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            response = {
                                status: 'error',
                                message: 'Terjadi kesalahan saat memproses response'
                            };
                        }
                    }

                    showToast(response.message, response.status);
                    
                    if (response.status === 'success') {
                        // Redirect ke dashboard setelah sukses
                        setTimeout(function() {
                            window.location.href = 'dashboard.php';
                        }, 1500);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    showToast('Gagal menyimpan perubahan. Silakan coba lagi.', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });
        });

        // Format harga
        $('#harga_package').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(value);
        });

        // File input preview
        $('#foto_package').change(function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 5000000) {
                    showToast('File terlalu besar! Maksimal 5MB', 'error');
                    this.value = '';
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    showToast('Hanya file JPG, JPEG & PNG yang diperbolehkan!', 'error');
                    this.value = '';
                    return;
                }

                $('.current-image').html(`
                    <i class="fas fa-image"></i>
                    File baru: ${file.name}
                `);
            }
        });
    });
    </script>
</body>
</html>