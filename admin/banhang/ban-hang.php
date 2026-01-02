<?php include 'dp.php';  ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn - Huyen Fitness</title>
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../menuchung/main.php'; ?>

    <div class="main-container">
        <aside class="sidebar">
            <form method="GET" action="">
                <h3>Tìm kiếm</h3>
                <input type="text" name="ma_hd" placeholder="Theo mã Hóa đơn" value="<?php echo $_GET['ma_hd'] ?? ''; ?>">
                <input type="text" name="ten_kh" placeholder="Tên khách hàng, số điện thoại" value="<?php echo $_GET['ten_kh'] ?? ''; ?>">
                
                <button type="submit" class="btn-search"><i class="fa fa-search"></i> Tìm kiếm</button>
                <br><br>
                <a href="ban-hang.php" style="text-decoration:none; color:#666; font-size:13px;">Xóa bộ lọc</a>
            </form>
        </aside>

        <main class="content">
            <div class="content-header">
        <h2>Hóa đơn</h2>
        <div class="header-action-group" style="display: flex; gap: 10px;">
        <button class="btn-add" onclick="window.location.href='tao-hoa-don.php'" 
                style="background:#28a745; color:white; padding:8px 15px; border:none; border-radius:4px; cursor:pointer; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-plus"></i> <b>Bán hàng(F1)</b>
        </button>

        <button class="btn-inventory" onclick="window.location.href='quan-ly-kho.php'" 
                style="background:#ffc107; color:#212529; padding:8px 15px; border:none; border-radius:4px; cursor:pointer; display:flex; align-items:center; gap:8px; font-weight:bold;">
            <i class="fa-solid fa-boxes-stacked"></i> Quản lý kho
        </button>

        <a href="export_excel.php?ma_hd=<?php echo $_GET['ma_hd'] ?? ''; ?>&ten_kh=<?php echo $_GET['ten_kh'] ?? ''; ?>" 
   class="btn-export" 
   style="background:#6c757d; color:white; padding:8px 15px; border:none; border-radius:4px; cursor:pointer; display:flex; align-items:center; gap:8px; text-decoration: none; font-size: 13.33px; font-family: Arial;">
    <i class="fa-solid fa-file-export"></i> Xuất file
</a>
    </div>
</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Thời gian</th>
                        <th>Tên khách hàng</th>
                        <th>Điện thoại</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền</th>
                        <th>Giảm giá</th>
                        <th>Khách trả</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
        <?php
    $sql = "SELECT * FROM hoa_don WHERE ma_hd LIKE 'HD%'"; 

    if (!empty($_GET['ma_hd'])) {
    $ma = $conn->real_escape_string($_GET['ma_hd']);
    $sql .= " AND ma_hd LIKE '%$ma%'";
    }
                        if (!empty($_GET['ten_kh'])) {
                            $ten = $conn->real_escape_string($_GET['ten_kh']);
                            $sql .= " AND (ten_khach LIKE '%$ten%' OR dien_thoai LIKE '%$ten%')";
                        }
                        $sql .= " ORDER BY thoi_gian DESC";

                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td><span style='color:#007bff;'>{$row['ma_hd']}</span></td>
                                        <td>" . date('d/m/Y H:i', strtotime($row['thoi_gian'])) . "</td>
                                        <td>{$row['ten_khach']}</td>
                                        <td>{$row['dien_thoai']}</td>
                                        <td>{$row['nhan_vien']}</td>
                                        <td>" . number_format($row['tong_tien_hang']) . "</td>
                                        <td>" . number_format($row['giam_gia']) . "</td>
                                        <td>" . number_format($row['khach_da_tra']) . "</td>
                                        <td><span class='status-done'>{$row['trang_thai']}</span></td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9' style='text-align:center;'>Không có dữ liệu</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>