<?php
require 'db.php';

// 1. Thiết lập header để tải file Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Lich_Tap_HLV_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 2. Lấy dữ liệu từ database (Lấy tất cả, sắp xếp ngày mới nhất lên đầu)
try {
    $sql = "SELECT * FROM pt_bookings ORDER BY booking_date DESC, time_slot ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h3>Danh Sách Lịch Đặt PT - Huyền Fitness</h3>
    <table>
        <thead>
            <tr style="background-color: #28a745; color: #fff;">
                <th style="width: 50px;">STT</th>
                <th style="width: 150px;">Ngày tập</th>
                <th style="width: 150px;">Khung giờ</th>
                <th style="width: 200px;">Tên Khách Hàng</th>
                <th style="width: 150px;">Huấn Luyện Viên</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $index => $row): ?>
                <tr>
                    <td style="text-align: center;"><?= $index + 1 ?></td>
                    <td><?= date('d/m/Y', strtotime($row['booking_date'])) ?></td>
                    <td><?= $row['time_slot'] ?></td>
                    <td><?= $row['customer_name'] ?></td>
                    <td><?= $row['trainer_name'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Chưa có lịch đặt nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>