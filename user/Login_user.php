<?php
session_start();

if (isset($_POST['submit'])) {
    include('../includes/db_connection.php');
    
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);
    
    $stmt = $conn->prepare("SELECT * FROM user WHERE email_user = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_user'])) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['email_user'] = $user['email_user'];
            $_SESSION['nama_user'] = $user['nama_user'];
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Email tidak ditemukan!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travoury</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NDB8fFRSQVZFTHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }



        .back-to-home:hover {
            background-color: #fff;
            transform: translateY(-2px);
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #f39c12;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: #666;
            font-size: 0.9em;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.9em;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-group input:focus {
            border-color: #f39c12;
            outline: none;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 47px;
            color: #666;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #f39c12;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }

        .error-message {
            background-color: #fee;
            color: #e74c3c;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .register-link p {
            color: #666;
            font-size: 0.9em;
        }

        .register-link a {
            color: #f39c12;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h1>Travoury</h1>
            <p>Selamat datang kembali! Silakan login untuk melanjutkan</p>
        </div>

        <form method="POST">
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Masukkan email Anda" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>

            <button type="submit" name="submit" class="submit-btn">
                Login <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="register-link">
            <p>Belum punya akun?<a href="register.php">Daftar Sekarang</a></p>
        </div>
    </div>
</body>
</html>