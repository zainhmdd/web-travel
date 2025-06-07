<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../includes/db_connection.php');

if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit();
}

$sql = "SELECT * FROM paymentmethod ORDER BY payment_date DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f2f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            padding: 2rem;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 2rem;
            text-align: center;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }


        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
        }

        .btn-delete {
            background: #dc3545;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-lunas {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-menunggu {
            background: #fff3e0;
            color: #ef6c00;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 10px;
            width: 400px;
            padding: 2rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .modal-content h2 {
            margin-top: 0;
            color: #333;
        }

        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 1rem 0;
            font-size: 1rem;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .bukti-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .bukti-preview:hover {
            transform: scale(1.1);
        }

        .preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .preview-content {
            max-width: 90%;
            max-height: 90%;
        }

        .preview-content img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 5px;
        }

        .close-preview {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }

        .amount-cell {
            font-family: 'Roboto Mono', monospace;
            font-weight: 500;
            color: #1a73e8;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #43a047, #66bb6a);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            margin: 2rem auto;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        footer {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }


    </style>
</head>
<body>
    <div class="header">
        <h1>Kelola Pembayaran</h1>
        <a href="logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
    </div>

    <div class="container">
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID Pembayaran</th>
                        <th>ID Transaksi</th>
                        <th>Status</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pembayaran</th>
                        <th>Bukti Transaksi</th>
                        <th>Tanggal Keberangkatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#<?php echo $row['id_payment']; ?></td>
                        <td>#<?php echo $row['id_transaction']; ?></td>
                        <td>
                            <span class="status-badge <?php echo ($row['payment_status'] == 'Lunas' || $row['payment_status'] == 'Sudah Lunas') ? 'status-lunas' : 'status-menunggu'; ?>">
                                <?php echo $row['payment_status']; ?>
                            </span>
                        </td>
                        <td class="amount-cell">Rp <?php echo number_format($row['payment_amount'], 0, ',', '.'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['payment_date'])); ?></td>
                        <td>
                            <?php if($row['bukti_transaksi'] && $row['bukti_transaksi'] != '*NULL*'): ?>
                                <img src="../<?php echo $row['bukti_transaksi']; ?>" alt="Bukti Transaksi" class="bukti-preview" 
                                     onclick="showPreview('../<?php echo $row['bukti_transaksi']; ?>')">
                            <?php else: ?>
                                <span class="status-badge status-menunggu">Belum Ada</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['departure_date'])); ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="openModal(<?php echo $row['id_payment']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="delete_payment.php?id=<?php echo $row['id_payment']; ?>" class="btn btn-delete" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Update Status -->
    <div class="modal" id="updateModal">
        <div class="modal-content">
            <h2>Update Status Pembayaran</h2>
            <select id="newStatus">
                <option value="Lunas">Lunas</option>
                <option value="Menunggu">Menunggu</option>
            </select>
            <div class="modal-buttons">
                <button class="btn btn-edit" onclick="updateStatus()">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <button class="btn btn-delete" onclick="closeModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Preview Image -->
    <div class="preview-modal" id="previewModal" onclick="closePreview()">
        <span class="close-preview">&times;</span>
        <div class="preview-content">
            <img id="previewImage" src="" alt="Preview Bukti Transaksi">
        </div>
    </div>

    <center>
    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</center>

<footer>
    <p>&copy; 2025 Travel Website - All rights reserved</p>
</footer>

<script>
    let currentPaymentId = null;

    function openModal(paymentId) {
        // Update ID pembayaran saat tombol Edit diklik
        currentPaymentId = paymentId;
        document.getElementById('updateModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('updateModal').style.display = 'none';
    }

    function showPreview(imageSrc) {
        document.getElementById('previewImage').src = imageSrc;
        document.getElementById('previewModal').style.display = 'flex';
    }

    function closePreview() {
        document.getElementById('previewModal').style.display = 'none';
    }

    function updateStatus() {
        const newStatus = document.getElementById('newStatus').value;

        if (currentPaymentId === null) {
            alert('ID pembayaran tidak valid');
            return;
        }

        $.ajax({
            url: 'update_payment.php',
            type: 'POST',
            data: {
                id_payment: currentPaymentId, // Pastikan ID dikirim dengan benar
                payment_status: newStatus
            },
            success: function(response) {
                alert('Status berhasil diperbarui');
                location.reload();  // Reload halaman untuk menampilkan status yang baru
            },
            error: function() {
                alert('Terjadi kesalahan, status tidak dapat diperbarui');
            }
        });
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('updateModal')) {
            closeModal();
        }
    }

    // Prevent closing preview when clicking the image
    document.querySelector('.preview-content').onclick = function(event) {
        event.stopPropagation();
    }
</script>
</body>
</html>