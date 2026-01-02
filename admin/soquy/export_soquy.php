<?php
require 'dp.php'; // Kết nối CSDL

// 1. Nhận tham số lọc ngày
$tu_ngay = !empty($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-01'); 
$den_ngay = !empty($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');

// 2. Thiết lập Header báo cho trình duyệt file Excel
$filename = "So_quy_tu_" . date('d-m-Y', strtotime($tu_ngay)) . "_den_" . date('d-m-Y', strtotime($den_ngay)) . ".xls";

// Xóa bộ nhớ đệm
if (ob_get_level()) ob_end_clean();

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Xuất BOM để hiển thị tiếng Việt
echo "\xEF\xBB\xBF"; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 13px; }
    table { border-collapse: collapse; width: 100%; }
    
    td, th { 
        border: 1px solid #000; 
        padding: 8px; 
        vertical-align: middle; 
        white-space: nowrap; /* Giữ chữ trên 1 dòng */
    }
    
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .text-bold { font-weight: bold; }
</style>
</head>
<body>

<table>
    <tr>
        <td colspan="8" style="border:none; font-size:18px; font-weight:bold; text-align:center; height:45px;">BÁO CÁO SỔ QUỸ THU CHI</td>
    </tr>
    <tr>
        <td colspan="8" style="border:none; text-align:center; font-style:italic; height:30px;">
            Từ ngày: <?= date('d/m/Y', strtotime($tu_ngay)) ?> - Đến ngày: <?= date('d/m/Y', strtotime($den_ngay)) ?>
        </td>
    </tr>
    <tr><td colspan="8" style="border:none;"></td></tr>

    <tr style="background-color:#f2f2f2; font-weight:bold; text-align:center; height: 40px;">
        <th style="width: 60px;">STT</th>
        <th style="width: 200px;">Mã phiếu</th>
        <th style="width: 200px;">Thời gian</th>
        <th style="width: 180px;">Loại thu chi</th>
        <th style="width: 250px;">Người nộp/nhận</th>
        <th style="width: 350px;">Ghi chú</th>
        <th style="width: 180px;">Giá trị</th>
        <th style="width: 180px;">Trạng thái</th>
    </tr>

    <?php
    $sql = "SELECT * FROM hoa_don WHERE DATE(thoi_gian) BETWEEN '$tu_ngay' AND '$den_ngay' ORDER BY thoi_gian DESC";
    $result = $conn->query($sql);

    $stt = 1;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ngay_tao = date('d/m/Y H:i', strtotime($row['thoi_gian']));
            $gia_tri = number_format($row['tong_tien_hang']);
            
            // Xử lý màu sắc
            $loai_goc = $row['loai_hd'];
            $loai_hien_thi = 'Thu bán hàng';
            $style_mau = 'color:#28a745; font-weight:bold;'; 

            if ($loai_goc == 'Dịch vụ') {
                $loai_hien_thi = 'Thu bán dịch vụ';
                $style_mau = 'color:#28a745; font-weight:bold;';
            } elseif ($loai_goc == 'Thu ngoài lề') {
                $loai_hien_thi = 'Thu ngoài lề';
                $style_mau = 'color:#17a2b8; font-weight:bold;';
            } elseif ($loai_goc == 'Phiếu chi') {
                $loai_hien_thi = 'Phiếu chi';
                $style_mau = 'color:#dc3545; font-weight:bold;';
            }

            $ten_doi_tuong = !empty($row['ten_khach']) ? $row['ten_khach'] : 'Khách lẻ';

            echo '<tr>';
            echo '<td class="text-center">' . $stt++ . '</td>';
            echo '<td class="text-center" style="color:#007bff; font-weight:bold;">' . ($row['ma_hd'] ?? 'HD'.$row['id']) . '</td>';
            echo '<td class="text-center">' . $ngay_tao . '</td>';
            echo '<td class="text-center" style="' . $style_mau . '">' . $loai_hien_thi . '</td>';
            echo '<td class="text-left">' . $ten_doi_tuong . '</td>';
            echo '<td class="text-left">' . ($row['ghi_chu'] ?? '') . '</td>';
            echo '<td class="text-right" style="font-weight:bold;">' . $gia_tri . '</td>';
            echo '<td class="text-center" style="color:green; font-weight:bold;">Đã thanh toán</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8" class="text-center" style="padding:20px;">Không có dữ liệu trong khoảng thời gian này</td></tr>';
    }
    ?>
</table>
</body>
</html>