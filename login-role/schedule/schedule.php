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

// --- Fallback hardcode (giữ nguyên như cũ)
$clubs = [
    "cs1" => "Huyền Fitness ",
];

$programs = [
    "yoga"    => "Yoga",
    "aerobic" => "Aerobic",
];

$weeks = [
    "this" => "22/12 - 26/12",
];

$schedules = [
    "cs1" => [
        "yoga" => [
            "this" => "images/cs1-yoga-this.jpg",
        ],
        "aerobic" => [
            "this" => "images/cs1-aerobic-this.png",
        ],
    ],
];

// --- Prefer DB schedule if available (admin upload)
if (isset($conn) && $conn && hf_table_exists($conn, 'site_schedule')) {
    $rows = [];
    $rs = @mysqli_query($conn, "SELECT club_key,club_name,program_key,program_name,week_key,week_label,image FROM site_schedule WHERE is_active=1 ORDER BY sort_order ASC, id DESC");
    if ($rs) {
        while ($r = mysqli_fetch_assoc($rs)) $rows[] = $r;
        mysqli_free_result($rs);
    }

    if (count($rows) > 0) {
        $clubs = [];
        $programs = [];
        $weeks = [];
        $schedules = [];

        foreach ($rows as $r) {
            $ck = $r['club_key'];
            $pk = $r['program_key'];
            $wk = $r['week_key'];
            $clubs[$ck] = $r['club_name'];
            $programs[$pk] = $r['program_name'];
            $weeks[$wk] = $r['week_label'];
            if (!isset($schedules[$ck])) $schedules[$ck] = [];
            if (!isset($schedules[$ck][$pk])) $schedules[$ck][$pk] = [];
            $schedules[$ck][$pk][$wk] = $r['image'];
        }
    }
}

function pick_valid($value, $allowed, $default_key) {
    return array_key_exists($value, $allowed) ? $value : $default_key;
}

/* Lấy lựa chọn từ URL, nhưng vì chỉ có 1 option nên luôn an toàn */
$clubDefault = array_key_first($clubs) ?? 'cs1';
$programDefault = array_key_first($programs) ?? 'yoga';
$weekDefault = array_key_first($weeks) ?? 'this';

$club    = pick_valid($_GET["club"] ?? $clubDefault, $clubs, $clubDefault);
$program = pick_valid($_GET["program"] ?? $programDefault, $programs, $programDefault);
$week    = pick_valid($_GET["week"] ?? $weekDefault, $weeks, $weekDefault);
/* Ảnh lịch */
$img = $schedules[$club][$program][$week] ?? "images/schedules/placeholder.jpg";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lịch Tập - Huyền Fitness</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include '../header.php'; ?>

<section class="page-hero">
    <div class="page-hero__overlay"></div>
    <div class="container page-hero__content">
        <h1>Lịch Tập</h1>
        <div class="breadcrumb-pill">
            <a href="../index.php">Home</a>
            <span>/</span>
            <span>Lịch tập</span>
        </div>
    </div>
</section>

<main class="schedule">
    <div class="container">
        <div class="schedule-head">
            <h2>Lịch Tập Hệ Thống</h2>
            <p class="schedule-sub">Huyền Fitness Center</p>
        </div>

        <div class="schedule-card">
            <div class="schedule-top">
                <h3>Chọn Lịch Tập</h3>

                <form class="schedule-filters" method="get" action="">
                    <!-- CƠ SỞ: chỉ 1 option -->
                    <div class="filter-item">
                        <label>Chọn Cơ Sở</label>
                        <select name="club">
                            <?php foreach ($clubs as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $club === $k ? "selected" : "" ?>>
                                    <?= htmlspecialchars($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- CHƯƠNG TRÌNH -->
                    <div class="filter-item">
                        <label>Chọn Chương Trình</label>
                        <select name="program">
                            <?php foreach ($programs as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $program === $k ? "selected" : "" ?>>
                                    <?= htmlspecialchars($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- TUẦN: chỉ 1 option -->
                    <div class="filter-item">
                        <label>Chọn Tuần</label>
                        <select name="week">
                            <?php foreach ($weeks as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $week === $k ? "selected" : "" ?>>
                                    <?= htmlspecialchars($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button class="btn-primary" type="submit">Xem lịch</button>
                </form>
            </div>

            <div class="schedule-image">
                <img src="<?= htmlspecialchars(hf_asset_url($img)) ?>" alt="Lịch tập <?= htmlspecialchars($programs[$program]) ?> - <?= htmlspecialchars($weeks[$week]) ?>">
            </div>
        </div>

     <div class="hours-grid">
    <div class="hours-box">
        <h4>Giờ hoạt động (Thứ 2 - Thứ 6)</h4>
        <p>05:00 – 18:30</p>
    </div>
    <div class="hours-box">
                <h4>Giờ hoạt động (Thứ 7)</h4>
                <p>05:00 – 18:30</p>
            </div>
        </div>
</div>

</main>

<?php include '../footer.php'; ?>
</body>
</html>
