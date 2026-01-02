<?php include 'dp.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tồn kho - Huyen Fitness</title>
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../menuchung/main.php'; // Để giữ lại thanh Navbar ?>

    <div class="content" style="padding: 20px;">
    <div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="ban-hang.php" style="text-decoration: none; color: #666; font-size: 20px;" title="Quay lại">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h2 style="margin: 0;"><i class="fa-solid fa-boxes-stacked"></i> Danh sách tồn kho</h2>
        </div>
        
        <div class="content-header" style="display: flex; gap: 10px;">
    <button onclick="window.location.href='them-san-pham.php'" style="background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">
        <i class="fa-solid fa-plus-circle"></i> Thêm mới sản phẩm
    </button>
</div>
    </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên dịch vụ/Sản phẩm</th>
                    <th>Số lượng hiện có</th>
                    <th>Giá vốn</th>
                    <th>Giá bán</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
    <?php
    // Truy vấn lấy toàn bộ sản phẩm từ database
    $sql = "SELECT * FROM san_pham ORDER BY id DESC"; 
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $ten_sp = $row['ten_san_pham'];
            $thu_muc_anh = "images/";
            
            // TỰ ĐỘNG TÌM FILE: Hàm glob sẽ tìm mọi file có tên sản phẩm bất kể đuôi .jpg, .png, .jpeg...
            $files = glob($thu_muc_anh . $ten_sp . ".*");
            
            // Nếu tìm thấy file, lấy đường dẫn file đó; nếu không, dùng ảnh mặc định
            $duong_dan_anh = (count($files) > 0) ? $files[0] : "https://via.placeholder.com/50";
            
            // ... bên trong vòng lặp while($row = $result->fetch_assoc()) ...
echo "<tr>
        <td>
            <img src='{$duong_dan_anh}?v=" . time() . "' 
                 onerror=\"this.src='https://via.placeholder.com/50'\" 
                 style='width:40px; height:40px; border-radius:4px; object-fit:cover;'>
        </td>
        <td>{$row['ten_san_pham']}</td>
        <td><b style='color: " . ($row['so_luong_kho'] > 0 ? "green" : "red") . ";'>{$row['so_luong_kho']}</b></td>
        <td>" . number_format($row['gia_von']) . "đ</td>
        <td>" . number_format($row['gia_tien']) . "đ</td>
        <td>
            <button onclick=\"window.location.href='nhap-hang.php?ten=" . urlencode($row['ten_san_pham']) . "'\" 
                    style='padding:4px 8px; cursor:pointer; background:#007bff; color:white; border:none; border-radius:3px;' title='Nhập thêm số lượng'>
                <i class='fa-solid fa-plus'></i>
            </button>

            <button onclick=\"window.location.href='sua-san_pham.php?id={$row['id']}'\" 
                    style='padding:4px 8px; cursor:pointer; background:#ffc107; color:black; border:none; border-radius:3px; margin-left:5px;' title='Sửa thông tin'>
                <i class='fa-solid fa-pen-to-square'></i>
            </button>

            <button onclick=\"if(confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) { window.location.href='xoa-san-pham.php?id={$row['id']}'; }\" 
                    style='padding:4px 8px; cursor:pointer; background:#dc3545; color:white; border:none; border-radius:3px; margin-left:5px;' title='Xóa sản phẩm'>
                <i class='fa-solid fa-trash'></i>
            </button>
        </td>
      </tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center;'>Chưa có sản phẩm nào trong kho.</td></tr>";
    }
    ?>
</tbody>
        </table>
    </div>
</body>
</html>