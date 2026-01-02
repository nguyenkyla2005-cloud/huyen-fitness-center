<?php
session_start();

require_once __DIR__ . '/../db.php'; // provides $conn

function hf_base_url(): string {
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $pos = strpos($script, '/BTL/');
  if ($pos !== false) return substr($script, 0, $pos + 4); // "/BTL"
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
  return $path; // legacy relative path
}

function hf_table_exists(mysqli $conn, string $table): bool {
  $t = mysqli_real_escape_string($conn, $table);
  $rs = @mysqli_query($conn, "SHOW TABLES LIKE '{$t}'");
  if (!$rs) return false;
  $ok = mysqli_num_rows($rs) > 0;
  mysqli_free_result($rs);
  return $ok;
}

$posts = [];

// Prefer DB (site_news) if available, fallback to hardcode file.
if (isset($conn) && $conn && hf_table_exists($conn, 'site_news')) {
  $rs = @mysqli_query($conn, "SELECT slug,title,excerpt,content,image,date FROM site_news WHERE is_active=1 ORDER BY date DESC, id DESC");
  if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
      $posts[] = $row;
    }
    mysqli_free_result($rs);
  }
}

if (count($posts) === 0) {
  $posts = require __DIR__ . "/news-data.php";
  // Sort mới -> cũ
  usort($posts, function($a, $b){
    return strtotime($b["date"]) <=> strtotime($a["date"]);
  });
}

function vn_month_label($monthNum){
  return "Th" . intval($monthNum); // Th2, Th6...
}

$page = max(1, intval($_GET["page"] ?? 1));
$perPage = 6;
$total = count($posts);
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);

$start = ($page - 1) * $perPage;
$paged = array_slice($posts, $start, $perPage);

?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tin tức - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include "../header.php"; ?>

<!-- Banner giống style bạn đang dùng -->
<section class="page-hero news-hero">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1>Tin tức</h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <span>Tin tức</span>
    </div>
  </div>
</section>

<main class="news-page">
  <div class="container">
    <div class="news-grid">
      <?php foreach ($paged as $p): 
        $ts = strtotime($p["date"]);
        $day = date("d", $ts);
        $month = vn_month_label(date("n", $ts));
        $year = date("Y", $ts);
      ?>
        <article class="news-card">
          <a class="news-thumb" href="post.php?slug=<?= urlencode($p["slug"]) ?>">
            <img src="<?= htmlspecialchars(hf_asset_url($p["image"] ?? '')) ?>" alt="<?= htmlspecialchars($p["title"]) ?>">
            <div class="news-date">
              <span class="d"><?= htmlspecialchars($day) ?></span>
              <span class="m"><?= htmlspecialchars($month) ?></span>
              <span class="y"><?= htmlspecialchars($year) ?></span>
            </div>
          </a>

          <div class="news-body">
            <h3>
              <a href="post.php?slug=<?= urlencode($p["slug"]) ?>">
                <?= htmlspecialchars($p["title"]) ?>
              </a>
            </h3>
            <p class="news-excerpt"><?= htmlspecialchars($p["excerpt"] ?? '') ?></p>
            <a class="news-more" href="post.php?slug=<?= urlencode($p["slug"]) ?>">Read More</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <nav class="news-pagination" aria-label="Pagination">
      <?php for ($i=1; $i<=$totalPages; $i++): ?>
        <a class="<?= $i===$page ? "active" : "" ?>" href="?page=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
    </nav>
  </div>
</main>

<?php include "../footer.php"; ?>
</body>
</html>
