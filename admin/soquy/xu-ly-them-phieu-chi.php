<?php
include 'dp.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_hd = $_POST['ma_phieu'];
    $ghi_chu = $_POST['ten_phieu'];    // Lý do chi lưu vào cột ghi_chu
    $loai_hd = $_POST['loai_hd'];      // Giá trị là 'Phiếu chi'
    $nguoi_nhan = $_POST['nguoi_nhan']; // Lưu vào cột ten_khach (tạm dùng cột này cho người nhận)
    $tong_tien = $_POST['gia_tri'];
    $nhan_vien = $_POST['nhan_vien'];

    // Câu lệnh SQL: Lưu vào bảng hoa_don
    $sql = "INSERT INTO hoa_don (ma_hd, ten_khach, tong_tien_hang, loai_hd, ghi_chu, thoi_gian, trang_thai, nhan_vien) 
            VALUES ('$ma_hd', '$nguoi_nhan', '$tong_tien', '$loai_hd', '$ghi_chu', NOW(), 'Hoàn thành', '$nhan_vien')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Đã lập phiếu chi thành công!'); window.location.href='so-quy.php';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>