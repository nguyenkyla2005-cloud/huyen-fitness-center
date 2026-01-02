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

function hf_split_list(string $text): array {
  $text = trim($text);
  if ($text === '') return [];
  $parts = preg_split('/\r\n|\r|\n|,/', $text);
  $out = [];
  foreach ($parts as $p) {
    $p = trim($p);
    if ($p !== '') $out[] = $p;
  }
  return $out;
}

$trainers = [];

// Prefer DB (site_trainers) if available; fallback to hardcode.
if (isset($conn) && $conn && hf_table_exists($conn, 'site_trainers')) {
  $rs = @mysqli_query($conn, "SELECT slug,name,title,experience,short_text,bio,specialties,certifications,phone,email,avatar FROM site_trainers WHERE is_active=1 ORDER BY id DESC");
  if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
      $row['specialties'] = isset($row['specialties']) ? hf_split_list((string)$row['specialties']) : [];
      $row['certifications'] = isset($row['certifications']) ? hf_split_list((string)$row['certifications']) : [];
      $trainers[] = $row;
    }
    mysqli_free_result($rs);
  }
}

if (count($trainers) === 0) {
  $trainers = require __DIR__ . "/trainers-data.php";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Huấn luyện viên - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css" />
  <!-- nếu bạn dùng style.css chung ở root thì đổi path -->
</head>
<body>
<?php include '../header.php'; ?>

<section class="page-hero hlv-hero">
  <div class="page-hero__overlay"></div>

  <div class="container page-hero__content">
    <h1>HUẤN LUYỆN VIÊN</h1>

    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <span>HUẤN LUYỆN VIÊN</span>
    </div>
  </div>
</section>


<main class="trainer-page">
  <div class="container">
    <div class="trainer-head">
      <h2>Đội ngũ Huấn luyện viên</h2>
      <p>Chọn HLV phù hợp mục tiêu của bạn và xem hồ sơ chi tiết.</p>
    </div>

    <div class="trainer-grid">
      <?php foreach ($trainers as $t): ?>
        <article class="trainer-card">
          <div class="trainer-thumb">
            <img src="<?= htmlspecialchars(hf_asset_url($t["avatar"] ?? '')) ?>" alt="<?= htmlspecialchars($t["name"] ?? '') ?>">
          </div>

          <div class="trainer-body">
            <h3><?= htmlspecialchars($t["name"] ?? '') ?></h3>
            <div class="trainer-meta">
              <span><?= htmlspecialchars($t["title"] ?? '') ?></span>
              <span class="dot">•</span>
              <span><?= htmlspecialchars($t["experience"] ?? '') ?></span>
            </div>

            <p class="trainer-desc">
              <?= htmlspecialchars($t["short_text"] ?? ($t["short"] ?? '')) ?>
            </p>

            <a class="btn-outline"
               href="profile.php?slug=<?= urlencode($t["slug"]) ?>">
              View Profile
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php include '../footer.php'; ?>
</body>
</html>
