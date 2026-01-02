<?php
require_once __DIR__ . '/_init.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: trials.php');
    exit;
}

$errors = [];
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'mark_contacted') {
            $pdo->prepare("UPDATE trial_registrations SET contacted=1, contacted_at=NOW() WHERE id=?")->execute([$id]);
            $success = 'Đã đánh dấu đã liên hệ.';
        } elseif ($action === 'mark_new') {
            $pdo->prepare("UPDATE trial_registrations SET contacted=0, contacted_at=NULL WHERE id=?")->execute([$id]);
            $success = 'Đã đưa về trạng thái mới.';
        } elseif ($action === 'save_note') {
            $note = trim($_POST['admin_note'] ?? '');
            $pdo->prepare("UPDATE trial_registrations SET admin_note=? WHERE id=?")->execute([$note, $id]);
            $success = 'Đã lưu ghi chú.';
        }
    } catch (Exception $e) {
        $errors[] = 'Không thể cập nhật. Vui lòng kiểm tra quyền DB.';
    }
}

// Fetch row
$trial = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM trial_registrations WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $trial = $stmt->fetch();
} catch (Exception $e) {
    $errors[] = 'Không đọc được dữ liệu.';
}

if (!$trial) {
    header('Location: trials.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết đăng ký tập thử</title>
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
      <h4 class="hf-page-title mb-0"><i class="fa-solid fa-id-card"></i> Chi tiết đăng ký tập thử</h4>
      <div class="text-muted">Xem thông tin, ghi chú và đánh dấu đã liên hệ.</div>
    </div>
    <div>
      <a class="btn btn-outline-secondary" href="trials.php"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= h($success) ?></div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <div class="text-muted">Mã</div>
              <h5 class="mb-0">#<?= (int)$trial['id'] ?></h5>
            </div>
            <div>
              <?php if ((int)($trial['contacted'] ?? 0) === 1): ?>
                <span class="badge bg-success">Đã liên hệ</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Mới</span>
              <?php endif; ?>
            </div>
          </div>

          <hr>

          <div class="mb-2"><b>Họ tên:</b> <?= h($trial['fullname'] ?? '') ?></div>
          <div class="mb-2"><b>Email:</b> <?= h($trial['email'] ?? '') ?></div>
          <div class="mb-2"><b>SĐT:</b> <?= h($trial['phone'] ?? '') ?></div>
          <div class="mb-2"><b>Thời gian đăng ký:</b> <?= h($trial['created_at'] ?? '') ?></div>
          <div class="mb-2"><b>User ID:</b> <?= h((string)($trial['user_id'] ?? '')) ?></div>
          <div class="mb-2"><b>Đã liên hệ lúc:</b> <?= h((string)($trial['contacted_at'] ?? '')) ?></div>

          <div class="d-flex gap-2 mt-3">
            <?php if ((int)($trial['contacted'] ?? 0) === 1): ?>
              <form method="post" class="m-0">
                <input type="hidden" name="action" value="mark_new">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa-regular fa-circle"></i> Đưa về mới</button>
              </form>
            <?php else: ?>
              <form method="post" class="m-0">
                <input type="hidden" name="action" value="mark_contacted">
                <button class="btn btn-success" type="submit"><i class="fa-solid fa-check"></i> Đánh dấu đã liên hệ</button>
              </form>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Ghi chú admin</h5>
          <form method="post">
            <input type="hidden" name="action" value="save_note">
            <textarea class="form-control" name="admin_note" rows="8" placeholder="Ví dụ: đã gọi, hẹn lịch, khách muốn tập yoga..."><?= h((string)($trial['admin_note'] ?? '')) ?></textarea>
            <button class="btn btn-primary mt-3" type="submit"><i class="fa fa-save"></i> Lưu ghi chú</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
