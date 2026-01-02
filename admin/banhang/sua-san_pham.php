<?php 
include 'dp.php'; 
$id = $_GET['id'];
$res = $conn->query("SELECT * FROM san_pham WHERE id = $id");
$data = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="../menuchung/main.css">
</head>
<body>
    <div class="content" style="padding: 20px; max-width: 500px; margin: auto;">
        <h2>Sửa thông tin sản phẩm</h2>
        <form action="xu-ly-sua.php" method="POST" style="background:#fff; padding:20px; border:1px solid #ddd; border-radius:8px;">
            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
            
            <label>Tên sản phẩm:</label>
            <input type="text" name="ten_sp" value="<?php echo $data['ten_san_pham']; ?>" required style="width:100%; padding:8px; margin-bottom:15px;">

            <label>Giá vốn (đ):</label>
            <input type="number" name="gia_von" value="<?php echo $data['gia_von']; ?>" required style="width:100%; padding:8px; margin-bottom:15px;">

            <label>Giá bán (đ):</label>
            <input type="number" name="gia_ban" value="<?php echo $data['gia_tien']; ?>" required style="width:100%; padding:8px; margin-bottom:15px;">

            <button type="submit" style="background:#28a745; color:white; border:none; padding:10px; width:100%; cursor:pointer;">Cập nhật sản phẩm</button>
            <a href="quan-ly-kho.php" style="display:block; text-align:center; margin-top:10px; text-decoration:none; color:#666;">Hủy bỏ</a>
        </form>
    </div>
</body>
</html>