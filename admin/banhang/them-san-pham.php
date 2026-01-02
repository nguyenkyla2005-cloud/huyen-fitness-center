<?php include 'dp.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm mới</title>
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../menuchung/main.php'; ?>
    
    <div class="content" style="padding: 20px; max-width: 800px; margin: auto;">
        <div style="margin-bottom: 20px;">
            <a href="quan-ly-kho.php" style="text-decoration: none; color: #666;"><i class="fa-solid fa-arrow-left"></i> Quay lại kho</a>
        </div>
        <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-plus-circle"></i> Thêm sản phẩm mới</h2>
        
        <form action="xu_ly_them_moi.php" method="POST" enctype="multipart/form-data" style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ddd;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Tên sản phẩm/dịch vụ:</label>
                <input type="text" name="ten_sp" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Chọn ảnh sản phẩm:</label>
                <input type="file" name="anh_upload" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Giá vốn (đ):</label>
                    <input type="number" name="gia_von" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Giá bán (đ):</label>
                    <input type="number" name="gia_ban" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Số lượng nhập kho:</label>
                <input type="number" name="so_luong" value="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <button type="submit" style="background: #28a745; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold;">LƯU SẢN PHẨM</button>
        </form>
    </div>
</body>
</html>