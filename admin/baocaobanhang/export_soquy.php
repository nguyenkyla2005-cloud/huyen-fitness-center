<?php
include 'dp.php';

// 1. Nhận tham số loại xuất dữ liệu
$type = $_GET['type'] ?? 'hd';
$filename = "Bao_cao_" . $type . "_" . date('d-m-Y') . ".xls";

// 2. Thiết lập Header để trình duyệt tải file Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// Xuất BOM để hiển thị đúng tiếng Việt
echo "\xEF\xBB\xBF";

// 3. Khởi tạo các biến tính toán (Dựa trên số liệu ảnh 1 và 4 của bạn)
$total_thu = 0;
$total_chi = 0;
$count = 0;
?>

<style>
    .title { font-size: 16pt; font-weight: bold; text-align: center; }
    .header { background-color: #28a745; color: #ffffff; font-weight: bold; border: 1px solid #000; }
    .number { mso-number-format: "#,##0"; } /* Định dạng số trong Excel */
    .total-label { font-weight: bold; background-color: #f8f9fa; }
    .total-value { font-weight: bold; color: #d9534f; background-color: #f8f9fa; }
</style>

<table border="1">
    <?php if ($type == 'sq' || $type == 'bc'): ?>
        <tr>
            <th colspan="6" class="title">BÁO CÁO CHI TIẾT SỔ QUỸ & DOANH THU</th>
        </tr>
        <tr>
            <th class="header">Mã phiếu</th>
            <th class="header">Thời gian</th>
            <th class="header">Loại thu chi</th>
            <th class="header">Người nộp/nhận</th>
            <th class="header">Ghi chú</th>
            <th class="header">Giá trị (VNĐ)</th>
        </tr>

        <?php
        // Truy vấn dữ liệu (Nếu là báo cáo thì lấy hết để vẽ biểu đồ)
        $sql = "SELECT * FROM hoa_don ORDER BY thoi_gian ASC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $is_chi = (strpos($row['ma_hd'], 'PC') !== false);
            $val = $row['tong_tien_hang'];
            
            if ($is_chi) {
                $total_chi += $val;
                $loai = "Phiếu chi";
            } else {
                $total_thu += $val;
                $loai = (strpos($row['ma_hd'], 'PT') !== false) ? "Thu ngoài lề" : "Thu bán hàng";
            }
            $count++;
        ?>
            <tr>
                <td><?php echo $row['ma_hd']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['thoi_gian'])); ?></td>
                <td><?php echo $loai; ?></td>
                <td><?php echo $row['ten_khach']; ?></td>
                <td><?php echo $row['ghi_chu'] ?? ''; ?></td>
                <td class="number"><?php echo $val; ?></td>
            </tr>
        <?php } ?>

        <tr><td colspan="6"></td></tr>
        <tr>
            <td colspan="5" class="total-label">TỔNG THU (Hóa đơn + Thu ngoài):</td>
            <td class="total-value number"><?php echo $total_thu; ?></td>
        </tr>
        <tr>
            <td colspan="5" class="total-label">TỔNG CHI (Phiếu chi):</td>
            <td class="total-value number"><?php echo $total_chi; ?></td>
        </tr>
        <tr>
            <td colspan="5" class="total-label" style="background:#e9ecef;">PHÁT SINH (THỰC THU):</td>
            <td class="total-value number" style="background:#e9ecef;"><?php echo ($total_thu - $total_chi); ?></td>
        </tr>
        <tr>
            <td colspan="5" class="total-label">SỐ LƯỢNG PHIẾU:</td>
            <td><?php echo $count; ?></td>
        </tr>

    <?php else: ?>
        <tr><th colspan="5" class="title">DANH SÁCH HÓA ĐƠN BÁN HÀNG</th></tr>
        <tr>
            <th class="header">Mã hóa đơn</th>
            <th class="header">Thời gian</th>
            <th class="header">Khách hàng</th>
            <th class="header">Tổng tiền</th>
            <th class="header">Trạng thái</th>
        </tr>
        <?php
        $sql = "SELECT * FROM hoa_don WHERE ma_hd LIKE 'HD%' ORDER BY thoi_gian DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $total_thu += $row['tong_tien_hang'];
        ?>
            <tr>
                <td><?php echo $row['ma_hd']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['thoi_gian'])); ?></td>
                <td><?php echo $row['ten_khach']; ?></td>
                <td class="number"><?php echo $row['tong_tien_hang']; ?></td>
                <td>Hoàn thành</td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="3" class="total-label">TỔNG DOANH THU:</td>
            <td class="total-value number"><?php echo $total_thu; ?></td>
            <td></td>
        </tr>
    <?php endif; ?>
</table>