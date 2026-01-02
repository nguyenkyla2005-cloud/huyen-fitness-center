<?php
require 'db.php';

// Thiết lập header để trình duyệt hiểu đây là file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Danh_Sach_Hoi_Vien_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Lấy dữ liệu
try {
    $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY id DESC");
    $stmt->execute();
    $customers = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
    exit;
}
?>

<meta charset="utf-8">
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>STT</th>
            <th>Mã KH</th>
            <th>Họ và Tên</th>
            <th>Số điện thoại</th>
            <th>Gói tập</th>
            <th>Ngày đăng ký</th>
            <th>Ngày hết hạn</th>
            <th>Số tiền nợ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $index => $row): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= $row['customer_code'] ?></td>
            <td><?= $row['fullname'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td><?= $row['service_name'] ?></td>
            <td><?= date('d/m/Y', strtotime($row['start_date'])) ?></td>
            <td><?= date('d/m/Y', strtotime($row['end_date'])) ?></td>
            <td style="color: <?= $row['debt'] > 0 ? 'red' : 'black' ?>">
                <?= number_format($row['debt']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>