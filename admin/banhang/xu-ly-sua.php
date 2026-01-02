<?php
include 'dp.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $ten = $_POST['ten_sp'];
    $von = $_POST['gia_von'];
    $ban = $_POST['gia_ban'];

    $sql = "UPDATE san_pham SET ten_san_pham='$ten', gia_von='$von', gia_tien='$ban' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: quan-ly-kho.php");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>