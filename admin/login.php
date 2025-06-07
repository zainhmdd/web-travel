<?php
session_start();

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

$error_message = '';

if (isset($_POST['submit'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email_admin = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password_admin'])) {
                $_SESSION['id_admin'] = $admin['id_admin'];
                $_SESSION['email_admin'] = $admin['email_admin'];
                $_SESSION['nama_admin'] = $admin['nama_admin'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Password salah!";
            }
        } else {
            $error_message = "Email tidak ditemukan!";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            transform: translateY(0);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        h1 {
            color: #1e3c72;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 700;
            position: relative;
        }

        h1::after {
            content: '';
            display: block;
            width: 70px;
            height: 4px;
            background: linear-gradient(to right, #1e3c72, #2a5298);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            font-size: 14px;
            color: #1e3c72;
            margin-bottom: 8px;
            display: block;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.1);
            background-color: #fff;
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.4);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }

        button:active {
            transform: translateY(0);
        }

        .error-message {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 14px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #ef4444;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 28px;
            }

            .form-group input {
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login Admin</h1>
        <form method="POST" action="login.php">
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Masukkan email anda" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password anda" required>
            </div>
            
            <button type="submit" name="submit">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>