<?php
require_once __DIR__ . '/_init.php';

function count_table($pdo, $table, $where = '1=1') {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM {$table} WHERE {$where}");
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

$counts = [
    'gallery' => count_table($pdo, 'site_gallery'),
    'news'    => count_table($pdo, 'site_news'),
    'schedule'=> count_table($pdo, 'site_schedule'),
    'trainers'=> count_table($pdo, 'site_trainers'),
    'trials_new' => count_table($pdo, 'trial_registrations', 'contacted = 0'),
    'trials_total' => count_table($pdo, 'trial_registrations'),
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Websites - Huyền Fitness Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../menuchung/main.css">
  <link rel="stylesheet" href="websites.css">
</head>
<body>
  <?php include '../menuchung/main.php'; ?>

  <div class="container-fluid p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h4 class="hf-page-title mb-1"><i class="fa-solid fa-globe"></i> Quản trị Website</h4>
        <div class="text-muted">Thêm hình ảnh, tin tức , lịch tập, HLV và khách hàng đăng ký tập thử.</div>
      </div>
    </div>

    <div class="hf-note mb-4">
      <b>Lưu ý:</b> Nội dung bạn thêm ở đây sẽ được <b>thêm mới</b> và hiển thị kèm theo.
    </div>

    <div class="row g-3">
      <div class="col-md-6 col-xl-4">
        <a class="hf-card-link" href="gallery.php">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted">Hình ảnh phòng tập</div>
                  <h5 class="mb-0">Gallery</h5>
                </div>
                <div class="text-primary" style="font-size:28px"><i class="fa-regular fa-images"></i></div>
              </div>
              <div class="mt-2 text-muted">Đang có: <b><?= (int)$counts['gallery'] ?></b> ảnh</div>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-xl-4">
        <a class="hf-card-link" href="news.php">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted">Trang tin tức</div>
                  <h5 class="mb-0">Tin tức</h5>
                </div>
                <div class="text-success" style="font-size:28px"><i class="fa-regular fa-newspaper"></i></div>
              </div>
              <div class="mt-2 text-muted">Đang có: <b><?= (int)$counts['news'] ?></b> tin</div>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-xl-4">
        <a class="hf-card-link" href="schedule.php">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted">Lịch tập theo Yoga/Aerobic</div>
                  <h5 class="mb-0">Lịch tập</h5>
                </div>
                <div class="text-warning" style="font-size:28px"><i class="fa-regular fa-calendar"></i></div>
              </div>
              <div class="mt-2 text-muted">Bảng lịch tập: <b><?= (int)$counts['schedule'] ?></b></div>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-xl-4">
        <a class="hf-card-link" href="trainers.php">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted">Quản lý HLV</div>
                  <h5 class="mb-0">Huấn luyện viên</h5>
                </div>
                <div class="text-info" style="font-size:28px"><i class="fa-solid fa-user-tie"></i></div>
              </div>
              <div class="mt-2 text-muted">Đang có: <b><?= (int)$counts['trainers'] ?></b> HLV</div>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 col-xl-4">
        <a class="hf-card-link" href="trials.php">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted">Khách đăng ký tập thử</div>
                  <h5 class="mb-0">Thông báo / Tập thử</h5>
                </div>
                <div class="text-danger" style="font-size:28px"><i class="fa-solid fa-bell"></i></div>
              </div>
              <div class="mt-2 text-muted">
                Mới chưa xử lý: <b><?= (int)$counts['trials_new'] ?></b> / Tổng: <b><?= (int)$counts['trials_total'] ?></b>
              </div>
            </div>
          </div>
        </a>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
