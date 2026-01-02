<?php 
session_start();
include 'dp.php'; 

// 1. LẤY KHOẢNG THỜI GIAN TỪ URL (Nếu không có sẽ mặc định từ đầu tháng đến hiện tại)
$tu_ngay = $_GET['tu_ngay'] ?? date('Y-m-01'); 
$den_ngay = $_GET['den_ngay'] ?? date('Y-m-d');

// Tạo chuỗi điều kiện lọc ngày để dùng chung cho các câu SQL bên dưới
$filter_date = " AND DATE(thoi_gian) BETWEEN '$tu_ngay' AND '$den_ngay'";

// 2. TÍNH TỔNG THU
$sql_thu = "SELECT SUM(tong_tien_hang) as total_thu FROM hoa_don WHERE loai_hd != 'Phiếu chi' $filter_date";
$res_thu = $conn->query($sql_thu);
$tong_thu = 0;
if ($res_thu) {
    $row_thu = $res_thu->fetch_assoc();
    $tong_thu = $row_thu['total_thu'] ?? 0;
}

// 3. TÍNH TỔNG CHI
$sql_chi = "SELECT SUM(tong_tien_hang) as total_chi FROM hoa_don WHERE loai_hd = 'Phiếu chi' $filter_date";
$res_chi = $conn->query($sql_chi);
$tong_chi = 0;
if ($res_chi) {
    $row_chi = $res_chi->fetch_assoc();
    $tong_chi = $row_chi['total_chi'] ?? 0;
}

// 4. TÍNH PHÁT SINH
$phat_sinh = $tong_thu - $tong_chi;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sổ quỹ - Huyen Fitness</title>
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .so-quy-container { display: flex; background: #f4f7f6; min-height: 100vh; }
        .sidebar-filter { width: 250px; background: #fff; border-right: 1px solid #ddd; padding: 15px; }
        .main-view { flex: 1; padding: 20px; }
        .stat-card { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 30px 50px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-item { flex: 1; border-right: 1px solid #eee; text-align: center; }
        .stat-item:last-child { border-right: none; }
        .stat-item b { font-size: 24px; display: block; margin-top: 5px; }
        .text-thu { color: #28a745; } 
        .text-chi { color: #dc3545; } 
        .text-ps { color: #17a2b8; }
        .status-done { background: #28a745; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; }
        .btn-filter { width:100%; padding:10px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; }
        
        /* CSS cho nút export để đồng bộ font chữ */
        .btn-export-custom {
            background:#6c757d; color:white; padding:8px 15px; border:none; 
            border-radius:4px; cursor:pointer; display:inline-flex; align-items:center; 
            gap:8px; text-decoration: none; font-size: 13.33px; font-family: Arial;
        }
        .btn-export-custom:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <?php include '../menuchung/main.php'; ?>

    <div class="so-quy-container">
        <aside class="sidebar-filter">
            <form method="GET" action=""> 
                <h3 style="font-size:14px; margin-bottom:15px;">Bộ lọc thời gian</h3>
                <div style="background:#007bff; color:white; padding:8px; border-radius:4px; margin-bottom:10px;">Thời gian</div>
                
                <label style="font-size:12px; color:#666;">Từ ngày:</label>
                <input type="date" name="tu_ngay" value="<?= $tu_ngay ?>" style="width:100%; padding:8px; margin-bottom:10px;">
                
                <label style="font-size:12px; color:#666;">Đến ngày:</label>
                <input type="date" name="den_ngay" value="<?= $den_ngay ?>" style="width:100%; padding:8px; margin-bottom:15px;">
                
                <button type="submit" class="btn-filter">
                    <i class="fa-solid fa-filter"></i> Lọc dữ liệu
                </button>
            </form>
        </aside>

        <main class="main-view">
            <div class="content-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2>Sổ quỹ</h2>
                <div style="display:flex; gap:10px;">
                    <button class="btn-add" style="background:#28a745; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;" onclick="window.location.href='them-phieu-thu.php'">
                        <i class="fa-solid fa-circle-plus"></i> Phiếu thu
                    </button>
                    
                    <button class="btn-add" style="background:#dc3545; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer;" onclick="window.location.href='them-phieu-chi.php'">
                        <i class="fa-solid fa-circle-minus"></i> Phiếu chi
                    </button>

                    <a href="export_soquy.php?tu_ngay=<?php echo $_GET['tu_ngay'] ?? ''; ?>&den_ngay=<?php echo $_GET['den_ngay'] ?? ''; ?>" 
                       class="btn-export-custom">
                        <i class="fa-solid fa-file-export"></i> Xuất file
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-item">
                    <label>Tổng thu</label>
                    <b class="text-thu"><?= number_format($tong_thu) ?> đ</b>
                </div>
                <div class="stat-item">
                    <label>Tổng chi</label>
                    <b class="text-chi"><?= number_format($tong_chi) ?> đ</b>
                </div>
                <div class="stat-item">
                    <label>Phát sinh</label>
                    <b class="text-ps"><?= ($phat_sinh >= 0 ? '+' : '') . number_format($phat_sinh) ?> đ</b>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Thời gian</th>
                        <th>Loại thu chi</th>
                        <th>Người nộp/nhận</th>
                        <th>Ghi chú</th>
                        <th>Giá trị</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    // 5. CẬP NHẬT DANH SÁCH
    $sql_list = "SELECT * FROM hoa_don WHERE 1=1 $filter_date ORDER BY id DESC";
    $result = $conn->query($sql_list);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $loai_goc = $row['loai_hd']; 

            if ($loai_goc == 'Dịch vụ') {
                $loai_hien_thi = 'Thu bán dịch vụ';
                $color_loai = '#28a745'; 
            } 
            elseif ($loai_goc == 'Thu ngoài lề') {
                $loai_hien_thi = 'Thu ngoài lề';
                $color_loai = '#17a2b8'; 
            } 
            elseif ($loai_goc == 'Phiếu chi') {
                $loai_hien_thi = 'Phiếu chi';
                $color_loai = '#dc3545'; 
            }
            else {
                $loai_hien_thi = 'Thu bán hàng';
                $color_loai = '#28a745'; 
            }

            echo "<tr>
                <td style='color:#007bff; font-weight:bold;'>".($row['ma_hd'] ?? 'HD'.$row['id'])."</td>
                <td>".date('d/m/Y', strtotime($row['thoi_gian']))."<br><span style='font-size:11px; color:#888;'>".date('H:i', strtotime($row['thoi_gian']))."</span></td>
                <td><span style='color: {$color_loai}; font-weight: 700;'>{$loai_hien_thi}</span></td>
                <td>".($row['ten_khach'] ?? 'Khách lẻ')."</td>
                <td style='color:#555; font-style: italic;'>".($row['ghi_chu'] ?? '')."</td>
                <td><b>".number_format($row['tong_tien_hang'])." đ</b></td>
                <td><span class='status-done'>Đã thanh toán</span></td>
              </tr>";
        }
    } else {
        echo "<tr><td colspan='7' style='text-align:center; padding:20px;'>Không tìm thấy dữ liệu trong khoảng thời gian này.</td></tr>";
    }
    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>