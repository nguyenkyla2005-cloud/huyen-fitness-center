<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$services = require __DIR__ . "/services-data.php";
$slug = $_GET["slug"] ?? "";

$sv = $services[$slug] ?? null;
if (!$sv) {
  http_response_code(404);
  $sv = [
    "title" => "Không tìm thấy dịch vụ",
    "banner" => "../images/banner-service.jpg",
    "cover" => "../images/service.jpg",
    "excerpt" => "",
    "intro" => ["Dịch vụ không tồn tại hoặc đường dẫn sai."],
    "highlights" => [],
    "who" => [],
    "cta_text" => "Quay lại danh sách dịch vụ.",
    "cta_link" => "index.php",
  ];
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($sv["title"]) ?> - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include "../header.php"; ?>

<!-- HERO -->
<section class="page-hero service-hero" style="background-image:url('<?= htmlspecialchars($sv["banner"]) ?>')">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1><?= htmlspecialchars(mb_strtoupper($sv["title"], "UTF-8")) ?></h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <a href="services.php">Dịch vụ</a>
      <span>/</span>
      <span><?= htmlspecialchars($sv["title"]) ?></span>
    </div>
  </div>
</section>

<main class="service-detail">
  <div class="container">
    <div class="service-detail-grid">
      <!-- MAIN -->
      <article class="service-main">
        <?php if (!empty($sv["badge"])): ?>
          <div class="service-badge"><?= htmlspecialchars($sv["badge"]) ?></div>
        <?php endif; ?>

        <img class="service-cover" src="<?= htmlspecialchars($sv["cover"]) ?>" alt="<?= htmlspecialchars($sv["title"]) ?>">

        <h2><?= htmlspecialchars($sv["title"]) ?></h2>
        <?php if (!empty($sv["excerpt"])): ?>
          <p class="service-excerpt"><?= htmlspecialchars($sv["excerpt"]) ?></p>
        <?php endif; ?>

        <?php foreach (($sv["intro"] ?? []) as $para): ?>
          <p class="service-paragraph"><?= htmlspecialchars($para) ?></p>
        <?php endforeach; ?>

        <?php if (!empty($sv["highlights"])): ?>
          <h3>Điểm nổi bật</h3>
          <div class="service-highlights">
            <?php foreach ($sv["highlights"] as $h): ?>
              <div class="hl-item">
                <div class="hl-check">✓</div>
                <div>
                  <div class="hl-title"><?= htmlspecialchars($h[0]) ?></div>
                  <div class="hl-desc"><?= htmlspecialchars($h[1]) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($sv["who"])): ?>
          <h3>Phù hợp cho</h3>
          <ul class="service-list">
            <?php foreach ($sv["who"] as $w): ?>
              <li><?= htmlspecialchars($w) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <div class="service-cta">
          <div class="service-cta-text"><?= htmlspecialchars($sv["cta_text"]) ?></div>
          <a class="btn-primary" href="<?= htmlspecialchars($sv["cta_link"]) ?>">ĐĂNG KÝ TẬP THỬ</a>
        </div>
      </article>

      <!-- SIDEBAR -->
      <aside class="service-side">
        <div class="side-card">
          <h4>Hotline</h4>
          <p>097 696 36 89</p>
        </div>
        <div class="side-card">
          <h4>Giờ hoạt động</h4>
          <p>Thứ 2–Thứ 7: 05:00–21:00</p>
          <p>CN: 15:00–19:00</p>
          <p>(Yoga khoảng 17:00–21:00)</p>
        </div>
        <div class="side-card">
          <h4>Địa chỉ</h4>
          <p>Số 24 Thượng Đức Nam Đồ Sơn TP.Hải PHòng</p>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php include "../footer.php"; ?>
</body>
</html>
