<?php
session_start();
require 'dp.php'; // Kết nối CSDL

// 1. XỬ LÝ BỘ LỌC NGÀY
$tu_ngay = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-01');
$den_ngay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');

// Điều kiện SQL chung
$sql_filter = " AND DATE(thoi_gian) BETWEEN '$tu_ngay' AND '$den_ngay'";

// 2. LẤY SỐ LIỆU TỔNG QUAN (Cột bên trái)
$sql_summary = "SELECT 
    COUNT(id) as so_luong_don,
    SUM(tong_tien_hang) as tong_doanh_thu,
    SUM(giam_gia) as tong_giam_gia,
    SUM(khach_da_tra) as thuc_thu
    FROM hoa_don 
    WHERE 1=1 $sql_filter";
$tong_doanh_thu = $row_summary['tong_doanh_thu'] ?? 0;
$tong_giam_gia = $row_summary['tong_giam_gia'] ?? 0;
$thuc_thu = $row_summary['thuc_thu'] ?? 0;
$so_luong_don = $row_summary['so_luong_don'] ?? 0;
$tong_no = $tong_doanh_thu - $thuc_thu; // Giả sử nợ = doanh thu - thực thu

// 3. LẤY DỮ LIỆU VẼ BIỂU ĐỒ (Group theo ngày)
$sql_chart = "SELECT DATE(thoi_gian) as ngay, SUM(tong_tien_hang) as doanh_thu 
              FROM hoa_don 
              WHERE 1=1 $sql_filter 
              GROUP BY DATE(thoi_gian) 
              ORDER BY ngay ASC";
$res_chart = $conn->query($sql_chart);
$chart_labels = [];
$chart_data = [];
while($row = $res_chart->fetch_assoc()){
    $chart_labels[] = date('d/m/Y', strtotime($row['ngay']));
    $chart_data[] = (int)$row['doanh_thu'];
}

// 4. LẤY DỮ LIỆU BẢNG CHI TIẾT
$sql_list = "SELECT * FROM hoa_don WHERE 1=1 $sql_filter ORDER BY thoi_gian DESC";
$res_list = $conn->query($sql_list);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo bán hàng</title>
    <link rel="stylesheet" href="../menuchung/main.css"> <link rel="stylesheet" href="style_baocao.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include '../menuchung/main.php'; ?>

<div class="report-container">
    <aside class="sidebar-filter">
        <form method="GET" action="">
        

            <div class="filter-box">
                <div class="filter-header">Thời gian <i class="fa fa-chevron-up"></i></div>
                <div class="filter-body">
                    <label style="font-size:12px; display:block; margin-bottom:5px">Từ ngày:</label>
                    <input type="date" name="tu_ngay" value="<?= $tu_ngay ?>" style="width:100%; padding:5px; margin-bottom:10px; border:1px solid #ddd;">
                    
                    <label style="font-size:12px; display:block; margin-bottom:5px">Đến ngày:</label>
                    <input type="date" name="den_ngay" value="<?= $den_ngay ?>" style="width:100%; padding:5px; margin-bottom:10px; border:1px solid #ddd;">
                    
                    <button type="submit" class="btn-filter"><i class="fa fa-filter"></i> Lọc dữ liệu</button>
                </div>
            </div>
        </form>
    </aside>

    <main class="report-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title">Báo cáo bán hàng</h2>
            <a href="export_soquy.php?tu_ngay=<?=$tu_ngay?>&den_ngay=<?=$den_ngay?>" class="btn-export-excel">
                <i class="fa-solid fa-file-excel"></i> Xuất file
            </a>
        </div>

        <div class="stats-chart-wrapper">
            <div class="stats-summary">
                <div class="stat-row">
                    <span class="stat-label">Tổng tiền hàng</span>
                    <span class="stat-value"><?= number_format($tong_doanh_thu) ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Tổng giảm giá</span>
                    <span class="stat-value"><?= number_format($tong_giam_gia) ?></span>
                </div>
                <div class="stat-row" style="border-top:1px solid #eee; padding-top:10px;">
                    <span class="stat-label fw-bold">Tổng doanh thu</span>
                    <span class="stat-value text-primary"><?= number_format($tong_doanh_thu - $tong_giam_gia) ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Tổng nợ</span>
                    <span class="stat-value text-danger"><?= number_format($tong_no) ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Tổng thực thu</span>
                    <span class="stat-value text-success"><?= number_format($thuc_thu) ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Số lượng hóa đơn</span>
                    <span class="stat-value"><?= $so_luong_don ?></span>
                </div>
            </div>

            <div class="chart-container">
                <h4 style="font-size:14px; font-weight:bold; margin-bottom:15px;">Doanh thu bán hàng theo thời gian</h4>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="detail-table-wrapper">
            <div class="section-title">Thông tin chi tiết</div>
            <table class="table-report">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th class="text-right">Tổng tiền hàng</th>
                        <th class="text-right">Giảm giá</th>
                        <th class="text-right">Doanh thu</th>
                        <th class="text-right">Thực thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res_list && $res_list->num_rows > 0): ?>
                        <?php while($row = $res_list->fetch_assoc()): ?>
                        <tr>
                            <td style="color:#007bff; font-weight:bold;"><?= $row['ma_hd'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['thoi_gian'])) ?></td>
                            <td><?= $row['ten_khach'] ?></td>
                            <td class="text-right"><?= number_format($row['tong_tien_hang']) ?></td>
                            <td class="text-right"><?= number_format($row['giam_gia']) ?></td>
                            <td class="text-right fw-bold"><?= number_format($row['tong_tien_hang'] - $row['giam_gia']) ?></td>
                            <td class="text-right"><?= number_format($row['khach_da_tra']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center" style="padding:20px;">Không có dữ liệu</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    // Nhận dữ liệu từ PHP
    const labels = <?= json_encode($chart_labels) ?>;
    const dataRevenue = <?= json_encode($chart_data) ?>;

    const ctx = document.getElementById('revenueChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', // Loại biểu đồ cột
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu',
                data: dataRevenue,
                backgroundColor: '#36a2eb', // Màu xanh dương giống ảnh mẫu
                barThickness: 20, // Độ dày cột
                borderRadius: 4 // Bo góc cột
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Ẩn chú thích nếu không cần
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 4] } // Kẻ lưới nét đứt
                },
                x: {
                    grid: { display: false } // Ẩn lưới dọc
                }
            }
        }
    });
</script>

</body>
</html>