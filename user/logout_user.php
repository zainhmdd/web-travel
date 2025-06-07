<?php
session_start();
session_unset(); // Menghapus semua session
session_destroy(); // Menghancurkan session
header("Location: Login_user.php"); // Mengarahkan kembali ke halaman login
exit();
?>