<?php
session_start();

require_once __DIR__ . '/../db.php'; // provides $conn

function hf_base_url(): string {
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $pos = strpos($script, '/BTL/');
  if ($pos !== false) return substr($script, 0, $pos + 4);
  return '';
}

function hf_asset_url(string $path): string {
  $path = trim($path);
  if ($path === '') return '';
  if (preg_match('#^(https?:)?//#i', $path)) return $path;
  if (substr($path, 0, 1) === '/') return $path;
  if (strpos($path, 'uploads/') === 0) {
    $base = rtrim(hf_base_url(), '/');
    return ($base !== '' ? $base : '') . '/' . $path;
  }
  return $path;
}

function hf_table_exists(mysqli $conn, string $table): bool {
  $t = mysqli_real_escape_string($conn, $table);
  $rs = @mysqli_query($conn, "SHOW TABLES LIKE '{$t}'");
  if (!$rs) return false;
  $ok = mysqli_num_rows($rs) > 0;
  mysqli_free_result($rs);
  return $ok;
}

$slug = $_GET["slug"] ?? "";
$post = null;

// Prefer DB when available
if (isset($conn) && $conn && hf_table_exists($conn, 'site_news')) {
  $stmt = @mysqli_prepare($conn, "SELECT slug,title,excerpt,content,image,date FROM site_news WHERE is_active=1 AND slug=? LIMIT 1");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if ($rs && mysqli_num_rows($rs) === 1) {
      $post = mysqli_fetch_assoc($rs);
      mysqli_free_result($rs);
    }
    mysqli_stmt_close($stmt);
  }
}

// Fallback to legacy hardcode file
if (!$post) {
  $posts = require __DIR__ . "/news-data.php";
  foreach ($posts as $p) {
    if (($p["slug"] ?? '') === $slug) { $post = $p; break; }
  }
}

if (!$post) {
  http_response_code(404);
  $post = [
    "title" => "Không tìm thấy bài viết",
    "date" => date("Y-m-d"),
    "image" => "images/news/placeholder.jpg",
    "content" => "Bài viết không tồn tại hoặc đường dẫn sai.",
  ];
}

$ts = strtotime($post["date"] ?? date('Y-m-d'));
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($post["title"]) ?> - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include "../header.php"; ?>

<section class="page-hero news-hero">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1>Tin tức</h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <a href="news.php">Tin tức</a>
      <span>/</span>
      <span><?= htmlspecialchars($post["title"]) ?></span>
    </div>
  </div>
</section>

<main class="news-post">
  <div class="container">
    <a class="back-link" href="news.php">← Quay lại</a>

    <article class="post-card">
      <img class="post-cover" src="<?= htmlspecialchars(hf_asset_url($post["image"] ?? '')) ?>" alt="<?= htmlspecialchars($post["title"]) ?>">

      <div class="post-body">
        <h2><?= htmlspecialchars($post["title"]) ?></h2>
        <div class="post-meta"><?= date("d/m/Y", $ts) ?></div>
        <div class="post-content">
          <?= nl2br(htmlspecialchars($post["content"])) ?>
        </div>
      </div>
    </article>
  </div>
</main>

<?php include "../footer.php"; ?>
</body>
</html>
