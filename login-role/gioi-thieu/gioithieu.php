<?php
session_start();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Giới thiệu - Huyền Fitness Center</title>
  <link rel="stylesheet" href="style.css"> 
  <!-- Nếu bạn dùng style.css nằm cùng cấp thì đổi lại: href="style.css" -->
</head>
<body>
<?php include "../header.php"; ?>

<!-- BANNER -->
<section class="page-hero about-hero">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1>GIỚI THIỆU</h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <span>Giới thiệu</span>
    </div>
  </div>
</section>

<!-- CONTENT -->
<main class="about-page">
  <div class="container">
    <div class="about-grid">
      <!-- LEFT -->
      <div class="about-left">
        <h2>VỀ HUYỀN FITNESS CENTER</h2>
        <span class="about-line"></span>

        <p class="about-desc">
          Huyền Fitness Center là hệ thống phòng tập hiện đại, hướng đến trải nghiệm
          tập luyện khoa học – an toàn – hiệu quả. Chúng tôi cung cấp đa dạng bộ môn:
          Gym, Yoga, Aerobic… phù hợp nhiều mục tiêu.
        </p>

        <div class="about-list">
          <div class="about-item">
            <span class="tick">✓</span>
            <div>
              <h4>Hệ thống phòng tập hiện đại</h4>
              <p>Máy móc đầy đủ, không gian sạch sẽ, bố trí khoa học.</p>
            </div>
          </div>

          <div class="about-item">
            <span class="tick">✓</span>
            <div>
              <h4>Đa dạng bộ môn tập luyện</h4>
              <p>Gym, Yoga, Aerobic… linh hoạt theo thời gian của bạn.</p>
            </div>
          </div>

          <div class="about-item">
            <span class="tick">✓</span>
            <div>
              <h4>Đội ngũ HLV chuyên nghiệp</h4>
              <p>Hướng dẫn đúng kỹ thuật, xây dựng lộ trình phù hợp.</p>
            </div>
          </div>

          <div class="about-item">
            <span class="tick">✓</span>
            <div>
              <h4>Tiện ích & chăm sóc khách hàng</h4>
              <p>Hỗ trợ tận tâm, thân thiện, ưu tiên trải nghiệm hội viên.</p>
            </div>
          </div>

          <div class="about-item">
            <span class="tick">✓</span>
            <div>
              <h4>Môi trường tập luyện tích cực</h4>
              <p>Cộng đồng năng động, truyền cảm hứng mỗi ngày.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="about-right">
        <img src="images/about-gym.jpg" alt="Huyền Fitness Center">
      </div>
    </div>
  </div>
</main>

<?php include "../footer.php"; ?>
</body>
</html>
