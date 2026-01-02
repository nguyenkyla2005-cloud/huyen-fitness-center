<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $service_id = $_POST['service_id'];
    $payment_status = $_POST['payment_status']; // Nhận trạng thái thanh toán từ form

    $customer_code = 'CUS' . rand(100000, 999999);
    $start_date = date('Y-m-d');
    
    // 1. CẤU HÌNH GIÁ VÀ TÊN GÓI
    $days_to_add = 0;
    $service_name = '';
    $price = 0; // Biến lưu giá tiền

    switch ($service_id) {
        case '1_day':
            $days_to_add = 0; 
            $service_name = '1 Ngày (70k)';
            $price = 70000;
            break;
        case '1_month':
            $days_to_add = 30;
            $service_name = '1 Tháng (500k)';
            $price = 500000;
            break;
        case '3_month':
            $days_to_add = 90;
            $service_name = '3 Tháng (1.200k)';
            $price = 1200000;
            break;
        case '6_month':
            $days_to_add = 180;
            $service_name = '6 Tháng (2.100k)';
            $price = 2100000;
            break;
        case '1_year':
            $days_to_add = 365;
            $service_name = '1 Năm (3.200k)';
            $price = 3200000;
            break;
        default:
            $days_to_add = 0;
            $service_name = 'Chưa đăng ký';
            $price = 0;
    }

    // 2. XỬ LÝ NỢ
    // Nếu chọn "debt" (Ghi nợ) thì cột debt = giá gói, ngược lại là 0
    $debt_amount = ($payment_status == 'debt') ? $price : 0;

    // 3. TÍNH NGÀY HẾT HẠN
    if ($service_id == '1_day') {
        $end_date = $start_date;
    } else {
        $end_date = date('Y-m-d', strtotime("+$days_to_add days"));
    }

    try {
        // Thêm cột debt vào câu lệnh INSERT
        $sql = "INSERT INTO customers (customer_code, fullname, phone, service_name, start_date, end_date, debt, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_code, $fullname, $phone, $service_name, $start_date, $end_date, $debt_amount]);

        header("Location: admin.php?msg=add_success");
    } catch (Exception $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}
?>