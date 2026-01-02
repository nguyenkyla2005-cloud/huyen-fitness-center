<?php
include 'dp.php'; // Kết nối db từ file dp.php của bạn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_hd = "HD" . time(); // Tạo mã hóa đơn tự động
    $ten_kh = $_POST['ten_khach'];
    $sdt = $_POST['dien_thoai'];
    $tong_tien = $_POST['tong_tien'];
    $nv = "Huyen fitness center"; // Mặc định như trong ảnh
    $ngay = date("Y-m-d H:i:s");

    $sql = "INSERT INTO hoa_don (ma_hd, thoi_gian, ten_khach, dien_thoai, nhan_vien, tong_tien_hang, khach_da_tra, trang_thai) 
            VALUES ('$ma_hd', '$ngay', '$ten_kh', '$sdt', '$nv', '$tong_tien', '$tong_tien', 'Hoàn thành')";
    // Sau khi chèn hóa đơn thành công, thực hiện trừ kho
    // Giả sử bạn gửi kèm ID sản phẩm qua form
    $sql_update_kho = "UPDATE san_pham SET so_luong_kho = so_luong_kho - 1 WHERE ten_san_pham = '$ten_san_pham'";
    $conn->query($sql_update_kho);
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ban-hang.php"); // Thành công thì quay về trang danh sách
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>