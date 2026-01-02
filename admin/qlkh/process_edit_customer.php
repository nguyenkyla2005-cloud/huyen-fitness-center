<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];

    try {
        $sql = "UPDATE customers SET fullname = ?, phone = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fullname, $phone, $id]);

        header("Location: admin.php?msg=edit_success");
    } catch (Exception $e) {
        echo "Lỗi cập nhật: " . $e->getMessage();
    }
}
?>