<?php
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="panel admin">
    <h1>🛠 Trang quản trị Admin</h1>
    <p>Xin chào ADMIN: <b><?= $_SESSION['user']['username'] ?></b></p>
    <a href="logout.php">Đăng xuất</a>
</div>

</body>
</html>
