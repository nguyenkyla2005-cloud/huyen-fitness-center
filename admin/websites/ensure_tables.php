<?php
// Bảng cần thiết cho Website

if (!isset($pdo)) {
    return;
}

$charset = "DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$queries = [];

$queries[] = "CREATE TABLE IF NOT EXISTS site_gallery (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB $charset";

$queries[] = "CREATE TABLE IF NOT EXISTS site_news (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt VARCHAR(500) NULL,
    content MEDIUMTEXT NULL,
    image VARCHAR(255) NULL,
    date DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_site_news_slug (slug)
) ENGINE=InnoDB $charset";

$queries[] = "CREATE TABLE IF NOT EXISTS site_trainers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NULL,
    experience VARCHAR(255) NULL,
    short_text VARCHAR(500) NULL,
    bio MEDIUMTEXT NULL,
    specialties TEXT NULL,
    certifications TEXT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_site_trainers_slug (slug)
) ENGINE=InnoDB $charset";

$queries[] = "CREATE TABLE IF NOT EXISTS site_schedule (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    club_key VARCHAR(50) NOT NULL DEFAULT 'cs1',
    club_name VARCHAR(255) NOT NULL,
    program_key VARCHAR(50) NOT NULL,
    program_name VARCHAR(255) NOT NULL,
    week_key VARCHAR(50) NOT NULL,
    week_label VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_site_schedule_lookup (club_key, program_key, week_key)
) ENGINE=InnoDB $charset";

$queries[] = "CREATE TABLE IF NOT EXISTS trial_registrations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    contacted TINYINT(1) NOT NULL DEFAULT 0,
    contacted_at DATETIME NULL,
    admin_note TEXT NULL,
    KEY idx_trial_created (created_at),
    KEY idx_trial_contacted (contacted),
    UNIQUE KEY uq_trial_phone (phone),
    UNIQUE KEY uq_trial_email (email)
) ENGINE=InnoDB $charset";

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
    } catch (Exception $e) {
        // Do not block the UI if hosting doesn't allow CREATE TABLE.
        // Admin pages will show an error when queries fail.
    }
}

// ---- Add missing columns to trial_registrations (older project may already have this table)
// We run safe ALTER statements and ignore errors if columns already exist.
$alterTrials = [
    "ALTER TABLE trial_registrations ADD COLUMN contacted TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE trial_registrations ADD COLUMN contacted_at DATETIME NULL",
    "ALTER TABLE trial_registrations ADD COLUMN admin_note TEXT NULL",
    "ALTER TABLE trial_registrations ADD INDEX idx_trial_contacted (contacted)",
    "ALTER TABLE trial_registrations ADD INDEX idx_trial_created (created_at)",
];
foreach ($alterTrials as $q) {
    try {
        $pdo->exec($q);
    } catch (Exception $e) {
        // ignore
    }
}

// ---- Normalize legacy data (để unique key hoạt động đúng)
// - phone: bỏ khoảng trắng/ký tự phân cách phổ biến
// - email: lowercase; '' -> NULL
try {
    $pdo->exec("UPDATE trial_registrations SET email = NULL WHERE email = ''");
} catch (Exception $e) {}

try {
    $pdo->exec("UPDATE trial_registrations SET email = LOWER(email) WHERE email IS NOT NULL");
} catch (Exception $e) {}

try {
    // remove: space, '-', '.', '(', ')', '+'
    $pdo->exec("UPDATE trial_registrations SET phone = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'.',''),'(',''),')',''),'+','')");
} catch (Exception $e) {}

// ---- Enforce: mỗi SĐT / email chỉ đăng ký 1 lần (email cho phép NULL)
// Lưu ý: nếu DB đã có dữ liệu trùng, câu lệnh có thể fail và sẽ được ignore.
$alterUniqueTrials = [
    "ALTER TABLE trial_registrations ADD UNIQUE KEY uq_trial_phone (phone)",
    "ALTER TABLE trial_registrations ADD UNIQUE KEY uq_trial_email (email)",
];
foreach ($alterUniqueTrials as $q) {
    try {
        $pdo->exec($q);
    } catch (Exception $e) {
        // ignore
    }
}

// ---- Seed legacy hardcode data into DB (so admin can sửa/xóa tin cũ & HLV cũ)
// Only seed when the target table is empty.
function hf_table_count(PDO $pdo, string $table): int {
    try {
        $row = $pdo->query("SELECT COUNT(*) AS c FROM {$table}")->fetch();
        return (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function hf_unique_slug(PDO $pdo, string $table, string $slug, ?int $excludeId = null): string {
    $base = $slug;
    $n = 1;
    while (true) {
        try {
            if ($excludeId) {
                $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug=? AND id<>? LIMIT 1");
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug=? LIMIT 1");
                $stmt->execute([$slug]);
            }
            $exist = $stmt->fetch();
        } catch (Exception $e) {
            return $slug;
        }
        if (!$exist) return $slug;
        $n++;
        $slug = $base . '-' . $n;
    }
}

$projectRoot = realpath(__DIR__ . '/../../'); // /BTL

// Seed NEWS
try {
    if ($projectRoot && hf_table_count($pdo, 'site_news') === 0) {
        $newsFile = $projectRoot . '/login-role/news/news-data.php';
        if (is_file($newsFile)) {
            $news = require $newsFile;
            if (is_array($news)) {
                foreach ($news as $p) {
                    $title = trim((string)($p['title'] ?? ''));
                    if ($title === '') continue;
                    $rawSlug = (string)($p['slug'] ?? $title);
                    $slug = function_exists('hf_slugify') ? hf_slugify($rawSlug) : $rawSlug;
                    $slug = hf_unique_slug($pdo, 'site_news', $slug);
                    $excerpt = (string)($p['excerpt'] ?? '');
                    $content = (string)($p['content'] ?? '');
                    $image = (string)($p['image'] ?? '');
                    $date = (string)($p['date'] ?? date('Y-m-d'));

                    $stmt = $pdo->prepare("INSERT INTO site_news (slug, title, excerpt, content, image, date, is_active) VALUES (?,?,?,?,?,?,1)");
                    $stmt->execute([$slug, $title, $excerpt, $content, $image, $date]);
                }
            }
        }
    }
} catch (Exception $e) {
    // ignore seeding failure
}

// Seed TRAINERS
try {
    if ($projectRoot && hf_table_count($pdo, 'site_trainers') === 0) {
        $trainersFile = $projectRoot . '/login-role/trainer/trainers-data.php';
        if (is_file($trainersFile)) {
            $trainers = require $trainersFile;
            if (is_array($trainers)) {
                foreach ($trainers as $t) {
                    $name = trim((string)($t['name'] ?? ''));
                    if ($name === '') continue;
                    $rawSlug = (string)($t['slug'] ?? $name);
                    $slug = function_exists('hf_slugify') ? hf_slugify($rawSlug) : $rawSlug;
                    $slug = hf_unique_slug($pdo, 'site_trainers', $slug);

                    $title = (string)($t['title'] ?? '');
                    $experience = (string)($t['experience'] ?? '');
                    $short = (string)($t['short'] ?? '');
                    $bio = (string)($t['bio'] ?? '');
                    $spec = $t['specialties'] ?? '';
                    if (is_array($spec)) $spec = implode(", ", $spec);
                    $cert = $t['certifications'] ?? '';
                    if (is_array($cert)) $cert = implode("\n", $cert);
                    $phone = (string)($t['phone'] ?? '');
                    $email = (string)($t['email'] ?? '');
                    $avatar = (string)($t['avatar'] ?? '');

                    $stmt = $pdo->prepare("INSERT INTO site_trainers (slug, name, title, experience, short_text, bio, specialties, certifications, phone, email, avatar, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)");
                    $stmt->execute([$slug, $name, $title, $experience, $short, $bio, (string)$spec, (string)$cert, $phone, $email, $avatar]);
                }
            }
        }
    }
} catch (Exception $e) {
    // ignore seeding failure
}
