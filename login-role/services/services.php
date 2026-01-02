<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dịch vụ - Huyền Fitness</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include '../header.php'; ?>

<!-- BANNER -->
<section class="page-banner">
  <div class="banner-overlay"></div>

  <div class="banner-content">
    <h1>Dịch vụ</h1>

    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <span>Dịch vụ</span>
    </div>
  </div>
</section>
<!-- SERVICES -->
<section class="service-page">
    <div class="container service-grid">

        <!-- ITEM -->
        <div class="service-card">
            <img src="images/service1.webp" alt="Gym">
            <h3>GYM</h3>
            <p>
                Gym là loại hình tập luyện phù hợp với mọi đối tượng, mọi giới tính,<br>
                không phân biệt tuổi tác vì nó giúp mang lại cho người tập một sức khỏe,<br>
                một cơ thể săn chắc, một vóc dáng trẻ trung, loại bỏ stress.
            </p>
            <a class="btn-read" href="service.php?slug=phong-tap-gym">Read more</a>
        </div>

        <div class="service-card">
            <img src="images/service2.webp" alt="PT Gym">
            <h3>HUẤN LUYỆN VIÊN CÁ NHÂN</h3>
            <p>
                Lộ trình tập luyện 1-1, theo sát từng buổi,
                giúp đạt mục tiêu nhanh và an toàn.
            </p>
            <a class="btn-read" href="service.php?slug=huan-luyen-vien-ca-nhan">Read more</a>
        </div>

        <div class="service-card">
            <img src="images/service3.webp" alt="Aerobic">
            <h3>AEROBIC</h3>
            <p>
                Các lớp tập nhóm sôi động như Body Combat,
                Dance, Aerobic, giúp đốt mỡ hiệu quả.
            </p>
            <a class="btn-read" href="service.php?slug=aerobic">Read more</a>
        </div>

        <div class="service-card">
            <img src="images/service4.jpg" alt="Yoga">
            <h3>YOGA</h3>
            <p>
                Yoga giúp cân bằng cơ thể, cải thiện sức khỏe tinh thần
                và tăng độ dẻo dai.
            </p>
            <a class="btn-read" href="service.php?slug=yoga">Read more</a>
        </div>

    </div>
</section>

<?php include '../footer.php'; ?>

</body>
</html>
