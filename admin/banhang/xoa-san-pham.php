<?php
include 'dp.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Thực hiện lệnh xóa
    $sql = "DELETE FROM san_pham WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Xóa sản phẩm thành công!'); window.location.href='quan-ly-kho.php';</script>";
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }
}
?>