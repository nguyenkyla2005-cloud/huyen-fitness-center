<?php
require_once __DIR__ . '/_init.php';

$errors = [];
$success = '';

// Actions
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM site_gallery WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM site_gallery WHERE id = ?")->execute([$id]);

        if ($row && !empty($row['image']) && strpos($row['image'], 'uploads/') === 0) {
            $file = realpath(__DIR__ . '/../../' . '/' . $row['image']);
            if ($file && is_file($file)) {
                @unlink($file);
            }
        }

        $success = 'Đã xóa ảnh.';
    } catch (Exception $e) {
        $errors[] = 'Không thể xóa ảnh.';
    }
}

if ($action === 'toggle' && $id > 0) {
    try {
        $pdo->prepare("UPDATE site_gallery SET is_active = IF(is_active=1,0,1) WHERE id = ?")->execute([$id]);
        $success = 'Đã cập nhật trạng thái.';
    } catch (Exception $e) {
        $errors[] = 'Không thể cập nhật trạng thái.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_gallery'])) {
    $caption = trim($_POST['caption'] ?? '');
    $upload = hf_save_upload($_FILES['image'] ?? [], 'gallery');

    if (!$upload['ok']) {
        $errors[] = $upload['error'];
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO site_gallery (image, caption, is_active) VALUES (?,?,1)");
            $stmt->execute([$upload['path'], $caption]);
            $success = 'Đã thêm ảnh mới.';
        } catch (Exception $e) {
            $errors[] = 'Không thể lưu vào DB. Kiểm tra lại quyền DB.';
        }
    }
}

// Load items
$items = [];
try {
    $items = $pdo->query("SELECT * FROM site_gallery ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $errors[] = 'Không đọc được dữ liệu site_gallery. Có thể DB chưa tạo bảng.';
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Gallery - Websites</title>
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
      <h4 class="hf-page-title mb-0"><i class="fa-regular fa-images"></i> Hình ảnh (Gallery)</h4>
      <div class="text-muted">Ảnh bạn thêm sẽ hiển thị trong mục <b>HÌNH ẢNH PHÒNG TẬP</b> ở trang chủ.</div>
    </div>
    <div>
      <a class="btn btn-outline-secondary" href="index.php"><i class="fa fa-arrow-left"></i> Quay lại</a>
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
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Thêm ảnh mới</h5>
          <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label hf-form-label">Chọn ảnh *</label>
              <input class="form-control" type="file" name="image" accept="image/*" required>
            </div>
            <div class="mb-3">
              <label class="form-label hf-form-label">Caption (tuỳ chọn)</label>
              <input class="form-control" type="text" name="caption" placeholder="Mô tả ngắn...">
            </div>
            <button class="btn btn-primary" type="submit" name="add_gallery">
              <i class="fa fa-plus"></i> Thêm ảnh
            </button>
          </form>
        </div>
      </div>

      <div class="hf-note mt-3">
        Mẹo: Ảnh nên là JPG/PNG/WEBP, dung lượng vừa phải để web tải nhanh.
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Danh sách ảnh đã thêm (DB)</h5>

          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Ảnh</th>
                  <th>Caption</th>
                  <th>Trạng thái</th>
                  <th style="width:140px">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($items)): ?>
                  <?php foreach ($items as $it): ?>
                    <tr>
                      <td>
                        <img class="hf-thumb" src="<?= h(hf_asset_url($it['image'])) ?>" alt="">
                      </td>
                      <td><?= h($it['caption'] ?? '') ?></td>
                      <td>
                        <?php if ((int)$it['is_active'] === 1): ?>
                          <span class="badge bg-success">Đang hiển thị</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Đang ẩn</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" href="?action=toggle&id=<?= (int)$it['id'] ?>">
                          <i class="fa fa-toggle-on"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$it['id'] ?>" onclick="return confirm('Xóa ảnh này?')">
                          <i class="fa fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-muted">Chưa có ảnh nào trong DB.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="text-muted small">
            Nội dung cũ (9 ảnh gym1..gym9) vẫn giữ nguyên. Ảnh DB sẽ được <b>thêm</b> vào.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
