<?php
require 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['customer_id'];
    $new_service = $_POST['new_service'];

    $stmt = $pdo->prepare("SELECT end_date FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $current_customer = $stmt->fetch();

    if ($current_customer) {
        $current_end_date = $current_customer['end_date'];
        $today = date('Y-m-d');

        // Nếu còn hạn thì cộng nối tiếp, hết hạn thì tính từ hôm nay
        $base_date = ($current_end_date >= $today) ? $current_end_date : $today;

        $days_to_add = 0;
        $service_name_new = '';

        switch ($new_service) {
            case '1_day':
                $days_to_add = 0; // Cộng 0 ngày vì vé ngày chỉ dùng trong ngày mua
                // Với vé ngày, thường logic gia hạn sẽ tính là "dùng hôm nay"
                $base_date = $today; 
                $service_name_new = 'Gia hạn 1 Ngày (70k)';
                break;
            case '1_month':
                $days_to_add = 30;
                $service_name_new = 'Gia hạn 1 Tháng (500k)';
                break;
            case '3_month':
                $days_to_add = 90;
                $service_name_new = 'Gia hạn 3 Tháng (1.200k)';
                break;
            case '6_month':
                $days_to_add = 180;
                $service_name_new = 'Gia hạn 6 Tháng (2.100k)';
                break;
            case '1_year':
                $days_to_add = 365;
                $service_name_new = 'Gia hạn 1 Năm (3.200k)';
                break;
        }

        $new_end_date = date('Y-m-d', strtotime("$base_date +$days_to_add days"));

        $sql = "UPDATE customers SET end_date = ?, service_name = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$new_end_date, $service_name_new, $id]);

        header("Location: admin.php?msg=extend_success");
    }
}
?>