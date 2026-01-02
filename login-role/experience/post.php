<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$posts = require __DIR__ . "/news-data.php";
$slug = $_GET["slug"] ?? "";
$stmt = mysqli_prepare($conn,"SELECT * FROM site_news WHERE slug=? LIMIT 1");
mysqli_stmt_bind_param($stmt,"s",$slug);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($res);

$post = null;
foreach ($posts as $p) {
  if ($p["slug"] === $slug) { $post = $p; break; }
}

if (!$post) {
  http_response_code(404);
  $post = [
    "title" => "Không tìm thấy bài viết",
    "date" => date("Y-m-d"),
    "image" => "images/news1.jpg",
    "excerpt" => "",
    "content" => "Bài viết không tồn tại hoặc đường dẫn sai.",
  ];
}
$ts = strtotime($post["date"]);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($post["title"]) ?> - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "../header.php"; ?>

<section class="page-hero news-hero">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1>TIN TỨC</h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <a href="../index.php">Tin tức</a>
      <span>/</span>
      <span><?= htmlspecialchars($post["title"]) ?></span>
    </div>
  </div>
</section>

<main class="news-post">
  <div class="container">
    <a class="back-link" href="../index.php#news">← Quay lại</a>

    <article class="post-card">
      <img class="post-cover" src="<?= htmlspecialchars($post["image"]) ?>" alt="<?= htmlspecialchars($post["title"]) ?>">
      <div class="post-body">
        <h2><?= htmlspecialchars($post["title"]) ?></h2>
        <div class="post-meta"><?= date("d/m/Y", $ts) ?></div>
        <?php if (!empty($post["excerpt"])): ?>
          <p class="service-excerpt"><?= htmlspecialchars($post["excerpt"]) ?></p>
        <?php endif; ?>
        <div class="post-content">
        <?= $post["content"]; ?>
        </div>
      </div>
    </article>
  </div>
</main>

<?php include "../footer.php"; ?>
</body>
</html>
