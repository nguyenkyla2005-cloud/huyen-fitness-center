<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'login_role';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

try {
    // Biến $pdo dùng cho trang admin mới
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Biến $conn dùng cho trang login cũ (sửa lỗi Undefined variable $conn)
    $conn = mysqli_connect($host, $user, $pass, $db);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>