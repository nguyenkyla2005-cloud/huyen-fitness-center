<?php
include 'dp.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $ten_sp = $_POST['ten_san_pham'];
    $so_luong_moi = $_POST['so_luong_moi'];

    // Câu lệnh SQL cộng dồn số lượng vào cột so_luong_kho
    $sql = "UPDATE san_pham SET so_luong_kho = so_luong_kho + $so_luong_moi WHERE ten_san_pham = '$ten_sp'";

    if ($conn->query($sql) === TRUE) {
        // Nếu thành công, hiện thông báo và quay lại trang kho
        echo "<script>alert('Đã nhập thêm " . $so_luong_moi . " sản phẩm vào kho!'); window.location.href='quan-ly-kho.php';</script>";
    } else {
        // Nếu lỗi, hiển thị lỗi để kiểm tra
        echo "Lỗi cập nhật kho: " . $conn->error;
    }
}
?>