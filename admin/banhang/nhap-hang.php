<?php include 'dp.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhập hàng mới</title>
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../menuchung/main.php'; ?>

    <div class="content" style="padding: 20px;">
        <div style="margin-bottom: 20px;">
            <a href="quan-ly-kho.php" style="text-decoration: none; color: #666;"><i class="fa-solid fa-arrow-left"></i> Quay lại kho</a>
        </div>

        <h2><i class="fa-solid fa-truck-loading"></i> Nhập hàng vào kho</h2>
        
        <form action="xu-ly-nhap-hang.php" method="POST" style="margin-top: 20px; max-width: 600px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Chọn sản phẩm:</label>
                <select name="ten_san_pham" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <?php
                    $result = $conn->query("SELECT ten_san_pham FROM san_pham");
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['ten_san_pham']}'>{$row['ten_san_pham']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Số lượng nhập thêm:</label>
                <input type="number" name="so_luong_moi" min="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                Xác nhận nhập kho
            </button>
        </form>
    </div>
</body>
</html>