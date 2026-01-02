<?php
include 'dp.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_sp = $_POST['ten_sp'];
    $gia_von = $_POST['gia_von'];
    $gia_ban = $_POST['gia_ban'];
    $so_luong = $_POST['so_luong'];

    // Xử lý Upload ảnh
    if (isset($_FILES['anh_upload']) && $_FILES['anh_upload']['error'] == 0) {
        $target_dir = "images/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); } // Tự tạo thư mục nếu chưa có
        
        $ext = pathinfo($_FILES['anh_upload']['name'], PATHINFO_EXTENSION);
        $file_name = $ten_sp . "." . $ext; // Đặt tên ảnh theo tên sản phẩm
        $target_file = $target_dir . $file_name;

        move_uploaded_file($_FILES['anh_upload']['tmp_name'], $target_file);
    }

    // Lưu vào database
    $sql = "INSERT INTO san_pham (ten_san_pham, gia_von, gia_tien, so_luong_kho) 
            VALUES ('$ten_sp', '$gia_von', '$gia_ban', '$so_luong')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Thêm sản phẩm và ảnh thành công!'); window.location.href='quan-ly-kho.php';</script>";
    } else {
        echo "Lỗi SQL: " . $conn->error;
    }
}
?>