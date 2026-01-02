<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['booking_date'];
    $time = $_POST['time_slot'];
    $customer = $_POST['customer_name'];
    $trainer = $_POST['trainer_name'];

    try {
        // Kiểm tra xem giờ đó có ai đặt chưa (Tránh trùng lịch cho cùng 1 HLV)
        $check = $pdo->prepare("SELECT id FROM pt_bookings WHERE booking_date=? AND time_slot=? AND trainer_name=?");
        $check->execute([$date, $time, $trainer]);
        
        if ($check->rowCount() > 0) {
            echo "<script>alert('Lịch này của HLV $trainer đã bị trùng!'); window.location.href='pt.php';</script>";
        } else {
            $sql = "INSERT INTO pt_bookings (customer_name, trainer_name, booking_date, time_slot) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$customer, $trainer, $date, $time]);
            header("Location: pt.php?msg=success");
        }
    } catch (Exception $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}
?>