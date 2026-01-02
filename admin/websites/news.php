<?php
require_once __DIR__ . '/_init.php';

$errors = [];
$success = '';

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Load existing row if edit
$edit = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM site_news WHERE id = ?");
        $stmt->execute([$id]);
        $edit = $stmt->fetch();
        if (!$edit) {
            $errors[] = 'Không tìm thấy tin.';
            $action = '';
        }
    } catch (Exception $e) {
        $errors[] = 'Không đọc được dữ liệu.';
    }
}

if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM site_news WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM site_news WHERE id = ?")->execute([$id]);

        if ($row && !empty($row['image']) && strpos($row['image'], 'uploads/') === 0) {
            $file = realpath(__DIR__ . '/../../' . '/' . $row['image']);
            if ($file && is_file($file)) {
                @unlink($file);
            }
        }
        $success = 'Đã xóa tin.';
    } catch (Exception $e) {
        $errors[] = 'Không thể xóa tin.';
    }
}

if ($action === 'toggle' && $id > 0) {
    try {
        $pdo->prepare("UPDATE site_news SET is_active = IF(is_active=1,0,1) WHERE id = ?")->execute([$id]);
        $success = 'Đã cập nhật trạng thái.';
    } catch (Exception $e) {
        $errors[] = 'Không thể cập nhật trạng thái.';
    }
}

// Helper: ensure unique slug
function ensure_unique_slug(PDO $pdo, string $slug, ?int $excludeId = null): string {
    $base = $slug;
    $n = 1;
    while (true) {
        try {
            if ($excludeId) {
                $stmt = $pdo->prepare("SELECT id FROM site_news WHERE slug = ? AND id <> ? LIMIT 1");
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $pdo->prepare("SELECT id FROM site_news WHERE slug = ? LIMIT 1");
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

// Save add / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');

    if ($title === '') $errors[] = 'Vui lòng nhập tiêu đề.';
    if ($date === '') $date = date('Y-m-d');

    $slug = $slugInput !== '' ? hf_slugify($slugInput) : hf_slugify($title);

    if ($excerpt === '' && $content !== '') {
        $excerpt = mb_substr($content, 0, 140) . (mb_strlen($content) > 140 ? '...' : '');
    }

    $uploadPath = null;
    if (!empty($_FILES['image']['name'])) {
        $upload = hf_save_upload($_FILES['image'], 'news');
        if (!$upload['ok']) {
            $errors[] = $upload['error'];
        } else {
            $uploadPath = $upload['path'];
        }
    }

    if (empty($errors)) {
        try {
            if (isset($_POST['update_id']) && (int)$_POST['update_id'] > 0) {
                $updateId = (int)$_POST['update_id'];
                $slug = ensure_unique_slug($pdo, $slug, $updateId);

                if ($uploadPath) {
                    // delete old file if uploaded
                    $stmt = $pdo->prepare("SELECT image FROM site_news WHERE id = ?");
                    $stmt->execute([$updateId]);
                    $old = $stmt->fetch();
                    if ($old && !empty($old['image']) && strpos($old['image'], 'uploads/') === 0) {
                        $file = realpath(__DIR__ . '/../../' . '/' . $old['image']);
                        if ($file && is_file($file)) @unlink($file);
                    }

                    $pdo->prepare("UPDATE site_news SET slug=?, title=?, excerpt=?, content=?, image=?, date=? WHERE id=?")
                        ->execute([$slug, $title, $excerpt, $content, $uploadPath, $date, $updateId]);
                } else {
                    $pdo->prepare("UPDATE site_news SET slug=?, title=?, excerpt=?, content=?, date=? WHERE id=?")
                        ->execute([$slug, $title, $excerpt, $content, $date, $updateId]);
                }
                $success = 'Đã cập nhật tin.';
                $action = '';
                $edit = null;
            } else {
                $slug = ensure_unique_slug($pdo, $slug, null);
                $pdo->prepare("INSERT INTO site_news (slug, title, excerpt, content, image, date, is_active) VALUES (?,?,?,?,?,?,1)")
                    ->execute([$slug, $title, $excerpt, $content, $uploadPath, $date]);
                $success = 'Đã thêm tin mới.';
            }
        } catch (Exception $e) {
            $errors[] = 'Không thể lưu DB (kiểm tra quyền DB hoặc trùng slug).';
        }
    }
}

// Load list
$items = [];
try {
    $items = $pdo->query("SELECT * FROM site_news ORDER BY date DESC, id DESC")->fetchAll();
} catch (Exception $e) {
    $errors[] = 'Không đọc được dữ liệu site_news. Có thể DB chưa tạo bảng.';
}

$form = [
    'id' => $edit['id'] ?? 0,
    'title' => $edit['title'] ?? '',
    'slug' => $edit['slug'] ?? '',
    'excerpt' => $edit['excerpt'] ?? '',
    'content' => $edit['content'] ?? '',
    'date' => $edit['date'] ?? date('Y-m-d'),
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tin tức - Websites</title>
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
      <h4 class="hf-page-title mb-0"><i class="fa-regular fa-newspaper"></i> Tin tức (News)</h4>
      <div class="text-muted">Tin bạn thêm sẽ hiển thị trong trang <b>/login-role/news/news.php</b>.</div>
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
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3"><?= $form['id'] ? 'Sửa tin' : 'Thêm tin mới' ?></h5>
          <form method="post" enctype="multipart/form-data">
            <?php if ($form['id']): ?>
              <input type="hidden" name="update_id" value="<?= (int)$form['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label hf-form-label">Tiêu đề *</label>
              <input class="form-control" type="text" name="title" value="<?= h($form['title']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Slug (tuỳ chọn)</label>
              <input class="form-control" type="text" name="slug" value="<?= h($form['slug']) ?>" placeholder="Tu dong theo tieu de">
              <div class="form-text">Slug sẽ tự tạo từ tiêu đề. Nếu trùng, hệ thống tự thêm -2, -3...</div>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Ngày đăng</label>
              <input class="form-control" type="date" name="date" value="<?= h($form['date']) ?>">
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Tóm tắt (excerpt)</label>
              <textarea class="form-control" name="excerpt" rows="2" placeholder="Hiển thị ở danh sách tin...">
<?= h($form['excerpt']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Nội dung</label>
              <textarea class="form-control" name="content" rows="6" placeholder="Nội dung chi tiết...">
<?= h($form['content']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Ảnh đại diện</label>
              <input class="form-control" type="file" name="image" accept="image/*">
              <div class="form-text">Nếu không chọn, tin vẫn tạo được (không có ảnh).</div>
            </div>

            <button class="btn btn-primary" type="submit">
              <i class="fa fa-save"></i> <?= $form['id'] ? 'Cập nhật' : 'Thêm tin' ?>
            </button>
            <?php if ($form['id']): ?>
              <a class="btn btn-outline-secondary" href="news.php">Hủy sửa</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <div class="hf-note mt-3">
        Nội dung cũ trong file <b>login-role/news/news-data.php</b> vẫn giữ nguyên.
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Danh sách tin đã thêm (DB)</h5>

          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Ảnh</th>
                  <th>Tiêu đề</th>
                  <th>Ngày</th>
                  <th>Trạng thái</th>
                  <th style="width:150px">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($items)): ?>
                  <?php foreach ($items as $it): ?>
                    <tr>
                      <td>
                        <?php if (!empty($it['image'])): ?>
                          <img class="hf-thumb" src="<?= h(hf_asset_url($it['image'])) ?>" alt="">
                        <?php else: ?>
                          <span class="text-muted">(none)</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="fw-bold"><?= h($it['title']) ?></div>
                        <div class="text-muted small">slug: <?= h($it['slug']) ?></div>
                      </td>
                      <td><?= h($it['date'] ?? '') ?></td>
                      <td>
                        <?php if ((int)$it['is_active'] === 1): ?>
                          <span class="badge bg-success">Đang hiển thị</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Đang ẩn</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$it['id'] ?>"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="?action=toggle&id=<?= (int)$it['id'] ?>" title="Ẩn/Hiện"><i class="fa fa-toggle-on"></i></a>
                        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$it['id'] ?>" onclick="return confirm('Xóa tin này?')"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-muted">Chưa có tin nào trong DB.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
