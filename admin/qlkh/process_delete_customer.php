<?php
require 'db.php';

// Kiểm tra session (Dùng cách an toàn để tránh lỗi "Notice" bạn gặp lúc nãy)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. BẢO MẬT: Chỉ cho phép tài khoản Admin thực hiện xóa
// Nếu không phải admin, chuyển hướng về trang đăng nhập ngay
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// 2. XỬ LÝ XÓA
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Chuẩn bị câu lệnh SQL xóa dựa trên ID
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);

        // Xóa thành công -> Quay lại trang quản lý
        header("Location: admin.php?msg=delete_success");
        exit;
    } catch (Exception $e) {
        // Xử lý lỗi (Ví dụ: Lỗi kết nối hoặc ràng buộc dữ liệu)
        echo "<h3 style='color:red'>Lỗi không thể xóa!</h3>";
        echo "Chi tiết: " . $e->getMessage();
        echo "<br><br><a href='admin.php'>Quay lại trang chủ</a>";
    }
} else {
    // Nếu truy cập file này mà không có ID (ví dụ gõ trực tiếp URL) -> Đẩy về admin
    header("Location: admin.php");
    exit;
}
?>