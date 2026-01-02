<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dịch vụ - Huyền Fitness</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include '../header.php'; ?>

<!-- BANNER -->
<section class="page-banner">
    <div class="banner-overlay"></div>
    <div class="banner-content">
        <h1>Dịch vụ</h1>
        <p><a href="../index.php">Home</a> / Dịch vụ</p>
    </div>
</section>

<!-- SERVICES -->
<section class="service-page">
    <div class="container service-grid">

        <!-- ITEM -->
        <div class="service-card">
            <img src="images/service1.webp" alt="Zumba">
            <h3>ZUMBA</h3>
            <p>
                Zumba giúp đốt cháy mỡ thừa, cải thiện sức bền và tinh thần,
                phù hợp cho mọi lứa tuổi.
            </p>
            <a href="#" class="btn-read">Read more</a>
        </div>

        <div class="service-card">
            <img src="images/service2.jpg" alt="PT Gym">
            <h3>HUẤN LUYỆN VIÊN CÁ NHÂN</h3>
            <p>
                Lộ trình tập luyện 1-1, theo sát từng buổi,
                giúp đạt mục tiêu nhanh và an toàn.
            </p>
            <a href="#" class="btn-read">Read more</a>
        </div>

        <div class="service-card">
            <img src="images/service3.jpg" alt="Group X">
            <h3>GROUP X</h3>
            <p>
                Các lớp tập nhóm sôi động như Body Combat,
                Dance, Aerobic, giúp đốt mỡ hiệu quả.
            </p>
            <a href="#" class="btn-read">Read more</a>
        </div>

        <div class="service-card">
            <img src="images/service4.jpg" alt="Yoga">
            <h3>YOGA</h3>
            <p>
                Yoga giúp cân bằng cơ thể, cải thiện sức khỏe tinh thần
                và tăng độ dẻo dai.
            </p>
            <a href="#" class="btn-read">Read more</a>
        </div>

    </div>
</section>

<?php include '../footer.php'; ?>

</body>
</html>
