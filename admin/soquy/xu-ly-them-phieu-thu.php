<?php
include 'dp.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_hd = $_POST['ma_phieu'];
    $ten_phieu = $_POST['ten_phieu']; // Lưu vào cột ghi_chu
    $loai_hd = $_POST['loai_thu'];    // 'Thu ngoài lề'
    $nguoi_nop = $_POST['nguoi_nop']; // 'Chủ tài khoản'
    $tong_tien = $_POST['gia_tri'];
    
    // Lấy tên nhân viên đang đăng nhập
    $nhan_vien = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

    // Câu lệnh chèn vào bảng hoa_don
    $sql = "INSERT INTO hoa_don (ma_hd, ten_khach, tong_tien_hang, loai_hd, ghi_chu, thoi_gian, trang_thai, nhan_vien) 
            VALUES ('$ma_hd', '$nguoi_nop', '$tong_tien', '$loai_hd', '$ten_phieu', NOW(), 'Hoàn thành', '$nhan_vien')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Tạo phiếu thu thành công!'); window.location.href='so-quy.php';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>