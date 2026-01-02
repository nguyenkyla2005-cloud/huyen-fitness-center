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

$slug = $_GET["slug"] ?? "";
$trainer = null;

// Prefer DB when available
if (isset($conn) && $conn && hf_table_exists($conn, 'site_trainers')) {
  $stmt = @mysqli_prepare($conn, "SELECT slug,name,title,experience,short_text,bio,specialties,certifications,phone,email,avatar FROM site_trainers WHERE is_active=1 AND slug=? LIMIT 1");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if ($rs && mysqli_num_rows($rs) === 1) {
      $trainer = mysqli_fetch_assoc($rs);
      $trainer['specialties'] = isset($trainer['specialties']) ? hf_split_list((string)$trainer['specialties']) : [];
      $trainer['certifications'] = isset($trainer['certifications']) ? hf_split_list((string)$trainer['certifications']) : [];
      mysqli_free_result($rs);
    }
    mysqli_stmt_close($stmt);
  }
}

// Fallback to legacy hardcode file
if (!$trainer) {
  $trainers = require __DIR__ . "/trainers-data.php";
  foreach ($trainers as $t) {
    if (($t["slug"] ?? '') === $slug) { $trainer = $t; break; }
  }
}

if (!$trainer) {
  http_response_code(404);
  $trainer = [
    "name" => "Không tìm thấy huấn luyện viên",
    "avatar" => "images/trainers/placeholder.jpg",
    "title" => "",
    "experience" => "",
    "bio" => "Hồ sơ không tồn tại hoặc đường dẫn sai.",
    "specialties" => [],
    "certifications" => [],
    "phone" => "",
    "email" => ""
  ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($trainer["name"]) ?> - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include '../header.php'; ?>

<section class="page-hero hlv-hero">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1>Hồ sơ HLV</h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a><span>/</span>
      <a href="trainer.php">Huấn luyện viên</a><span>/</span>
      <span><?= htmlspecialchars($trainer["name"]) ?></span>
    </div>
  </div>
</section>

<main class="trainer-profile">
  <div class="container">
    <a class="back-link" href="trainer.php">← Quay lại danh sách</a>

    <div class="profile-card">
      <div class="profile-left">
        <img src="<?= htmlspecialchars(hf_asset_url($trainer["avatar"] ?? '')) ?>" alt="<?= htmlspecialchars($trainer["name"]) ?>">
      </div>

      <div class="profile-right">
        <h2><?= htmlspecialchars($trainer["name"]) ?></h2>

        <?php if (!empty($trainer["title"]) || !empty($trainer["experience"])): ?>
          <div class="profile-meta">
            <?php if (!empty($trainer["title"])): ?><span><?= htmlspecialchars($trainer["title"]) ?></span><?php endif; ?>
            <?php if (!empty($trainer["experience"])): ?><span class="dot">•</span><span><?= htmlspecialchars($trainer["experience"]) ?></span><?php endif; ?>
          </div>
        <?php endif; ?>

        <p class="profile-bio"><?= nl2br(htmlspecialchars($trainer["bio"])) ?></p>

        <?php if (!empty($trainer["specialties"])): ?>
          <h3>Thế mạnh</h3>
          <ul class="tag-list">
            <?php foreach ($trainer["specialties"] as $s): ?>
              <li><?= htmlspecialchars($s) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (!empty($trainer["certifications"])): ?>
          <h3>Chứng chỉ</h3>
          <ul class="bullet-list">
            <?php foreach ($trainer["certifications"] as $c): ?>
              <li><?= htmlspecialchars($c) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <div class="profile-contact">
          <?php if (!empty($trainer["phone"])): ?><p><b>Hotline:</b> <?= htmlspecialchars($trainer["phone"]) ?></p><?php endif; ?>
          <?php if (!empty($trainer["email"])): ?><p><b>Email:</b> <?= htmlspecialchars($trainer["email"]) ?></p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../footer.php'; ?>
</body>
</html>
