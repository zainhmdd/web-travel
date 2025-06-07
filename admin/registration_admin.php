<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin']) || !isset($_SESSION['email_admin'])) {
    header("Location: login.php");  // Jika belum login, arahkan ke halaman login
    exit();
}

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "travel";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data pendaftaran paket wisata
$sql = "SELECT r.id_registration, u.nama_user, p.nama_package, r.status_pendaftaran, r.tanggal_pendaftaran, r.additional_note 
        FROM registration r
        JOIN user u ON r.id_user = u.id_user
        JOIN package p ON r.id_package = p.id_package";
$result = $conn->query($sql);

// Proses perubahan status pendaftaran
if (isset($_POST['update_status'])) {
    $id_registration = $_POST['id_registration'];
    $new_status = $_POST['status_pendaftaran'];
    
    $update_sql = "UPDATE registration SET status_pendaftaran = ? WHERE id_registration = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $id_registration);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Status pendaftaran berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Terjadi kesalahan dalam memperbarui status!";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
}

// Proses penghapusan data pendaftaran
if (isset($_POST['delete'])) {
    $id_registration = $_POST['id_registration'];
    
    $delete_sql = "DELETE FROM registration WHERE id_registration = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id_registration);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Pendaftaran berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Terjadi kesalahan dalam menghapus data!";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();

    // Redirect untuk mencegah pengiriman form ulang
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pendaftaran Paket Wisata</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --success: #2ecc71;
            --warning: #f1c40f;
            --surface: #ffffff;
            --background: #f5f6fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            padding: 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Container Styles */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            flex: 1;
        }

        h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--secondary);
            display: inline-block;
        }

        /* Message Styles */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .message.success {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .message.error {
            background-color: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 1.5rem 0;
        }

        th, td {
            padding: 1.2rem 1rem;
            text-align: left;
        }

        th {
            background: var(--primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        tr:not(:last-child) td {
            border-bottom: 1px solid #eee;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateX(6px);
        }

        /* Form Control Styles */
        select {
            padding: 0.6rem 1rem;
            border: 2px solid #ddd;
            border-radius: 6px;
            background-color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
            min-width: 140px;
        }

        select:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        button {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        button[name="update_status"] {
            background-color: var(--secondary);
        }

        button[name="update_status"]:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        button[name="delete"] {
            background-color: var(--accent);
        }

        button[name="delete"]:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        /* Back Button Styles */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background-color: var(--success);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .back-button:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
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
        @media (max-width: 1024px) {
            .container {
                padding: 1rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            header h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8rem;
            }

            th, td {
                padding: 1rem 0.8rem;
            }

            .back-button {
                width: 100%;
                justify-content: center;
            }
        }

        /* Action Column Styles */
        td form {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Status Colors */
        td:nth-child(4) {
            font-weight: 500;
        }

        td:nth-child(4):contains("Diterima") {
            color: var(--success);
        }

        td:nth-child(4):contains("Dalam Proses") {
            color: var(--warning);
        }

        td:nth-child(4):contains("Dibatalkan") {
            color: var(--accent);
        }
    </style>
</head>
<body>

<header>
    <h1><i class="fas fa-plane-departure"></i> Pendaftaran Paket Wisata</h1>
</header>

<div class="container">
    <h2><i class="fas fa-list"></i> Daftar Pendaftaran Paket Wisata</h2>
    
    <?php
    // Menampilkan pesan notifikasi
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        echo "<div class='message $message_type'>
                <i class='fas fa-" . ($message_type == 'success' ? 'check-circle' : 'exclamation-circle') . "'></i>
                $message
              </div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <table>
        <thead>
            <tr>
                <th><i class="fas fa-hashtag"></i> No</th>
                <th><i class="fas fa-user"></i> Nama Pengguna</th>
                <th><i class="fas fa-box"></i> Nama Paket</th>
                <th><i class="fas fa-info-circle"></i> Status</th>
                <th><i class="fas fa-calendar"></i> Tanggal</th>
                <th><i class="fas fa-sticky-note"></i> Catatan</th>
                <th><i class="fas fa-cog"></i> Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $i = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $i++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_user']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_package']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status_pendaftaran']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tanggal_pendaftaran']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['additional_note']) . "</td>";
                    echo "<td>
                            <form method='POST'>
                                <input type='hidden' name='id_registration' value='" . $row['id_registration'] . "'>
                                <select name='status_pendaftaran'>
                                    <option value='Dalam Proses' " . ($row['status_pendaftaran'] == 'Dalam Proses' ? 'selected' : '') . ">Dalam Proses</option>
                                    <option value='Diterima' " . ($row['status_pendaftaran'] == 'Diterima' ? 'selected' : '') . ">Diterima</option>
                                    <option value='Dibatalkan' " . ($row['status_pendaftaran'] == 'Dibatalkan' ? 'selected' : '') . ">Dibatalkan</option>
                                </select>
                                <button type='submit' name='update_status'><i class='fas fa-sync-alt'></i> Update</button>
                            </form>
                            <form method='POST' style='display:inline-block'>
                                <input type='hidden' name='id_registration' value='" . $row['id_registration'] . "'>
                                <button type='submit' name='delete'><i class='fas fa-trash-alt'></i> Hapus</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align: center;'>Tidak ada data pendaftaran</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

<footer>
    <p>&copy; 2025 Travel Website | <i class="fas fa-heart"></i> Dibuat dengan sepenuh hati</p>
</footer>

</body>
</html>