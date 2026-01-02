<?php
require_once __DIR__ . '/_init.php';

$errors = [];
$success = '';

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$edit = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM site_trainers WHERE id = ?");
        $stmt->execute([$id]);
        $edit = $stmt->fetch();
        if (!$edit) {
            $errors[] = 'Không tìm thấy HLV.';
            $action = '';
        }
    } catch (Exception $e) {
        $errors[] = 'Không đọc được dữ liệu.';
    }
}

if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT avatar FROM site_trainers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM site_trainers WHERE id = ?")->execute([$id]);

        if ($row && !empty($row['avatar']) && strpos($row['avatar'], 'uploads/') === 0) {
            $file = realpath(__DIR__ . '/../../' . '/' . $row['avatar']);
            if ($file && is_file($file)) @unlink($file);
        }
        $success = 'Đã xóa HLV.';
    } catch (Exception $e) {
        $errors[] = 'Không thể xóa HLV.';
    }
}

if ($action === 'toggle' && $id > 0) {
    try {
        $pdo->prepare("UPDATE site_trainers SET is_active = IF(is_active=1,0,1) WHERE id = ?")->execute([$id]);
        $success = 'Đã cập nhật trạng thái.';
    } catch (Exception $e) {
        $errors[] = 'Không thể cập nhật trạng thái.';
    }
}

function ensure_unique_slug_trainer(PDO $pdo, string $slug, ?int $excludeId = null): string {
    $base = $slug;
    $n = 1;
    while (true) {
        try {
            if ($excludeId) {
                $stmt = $pdo->prepare("SELECT id FROM site_trainers WHERE slug = ? AND id <> ? LIMIT 1");
                $stmt->execute([$slug, $excludeId]);
            } else {
                $stmt = $pdo->prepare("SELECT id FROM site_trainers WHERE slug = ? LIMIT 1");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $short_text = trim($_POST['short_text'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $specialties = trim($_POST['specialties'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');

    if ($name === '') $errors[] = 'Vui lòng nhập tên HLV.';

    $slug = $slugInput !== '' ? hf_slugify($slugInput) : hf_slugify($name);

    $uploadPath = null;
    if (!empty($_FILES['avatar']['name'])) {
        $upload = hf_save_upload($_FILES['avatar'], 'trainers');
        if (!$upload['ok']) $errors[] = $upload['error'];
        else $uploadPath = $upload['path'];
    }

    if (empty($errors)) {
        try {
            if (isset($_POST['update_id']) && (int)$_POST['update_id'] > 0) {
                $updateId = (int)$_POST['update_id'];
                $slug = ensure_unique_slug_trainer($pdo, $slug, $updateId);

                if ($uploadPath) {
                    $stmt = $pdo->prepare("SELECT avatar FROM site_trainers WHERE id = ?");
                    $stmt->execute([$updateId]);
                    $old = $stmt->fetch();
                    if ($old && !empty($old['avatar']) && strpos($old['avatar'], 'uploads/') === 0) {
                        $file = realpath(__DIR__ . '/../../' . '/' . $old['avatar']);
                        if ($file && is_file($file)) @unlink($file);
                    }

                    $pdo->prepare("UPDATE site_trainers SET slug=?, name=?, title=?, experience=?, short_text=?, bio=?, specialties=?, certifications=?, phone=?, email=?, avatar=? WHERE id=?")
                        ->execute([$slug, $name, $title, $experience, $short_text, $bio, $specialties, $certifications, $phone, $email, $uploadPath, $updateId]);
                } else {
                    $pdo->prepare("UPDATE site_trainers SET slug=?, name=?, title=?, experience=?, short_text=?, bio=?, specialties=?, certifications=?, phone=?, email=? WHERE id=?")
                        ->execute([$slug, $name, $title, $experience, $short_text, $bio, $specialties, $certifications, $phone, $email, $updateId]);
                }

                $success = 'Đã cập nhật HLV.';
                $action = '';
                $edit = null;
            } else {
                $slug = ensure_unique_slug_trainer($pdo, $slug, null);
                $pdo->prepare("INSERT INTO site_trainers (slug, name, title, experience, short_text, bio, specialties, certifications, phone, email, avatar, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)")
                    ->execute([$slug, $name, $title, $experience, $short_text, $bio, $specialties, $certifications, $phone, $email, $uploadPath]);
                $success = 'Đã thêm HLV mới.';
            }
        } catch (Exception $e) {
            $errors[] = 'Không thể lưu DB (kiểm tra quyền DB hoặc trùng slug).';
        }
    }
}

$items = [];
try {
    $items = $pdo->query("SELECT * FROM site_trainers ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $errors[] = 'Không đọc được dữ liệu site_trainers.';
}

$form = [
    'id' => $edit['id'] ?? 0,
    'name' => $edit['name'] ?? '',
    'slug' => $edit['slug'] ?? '',
    'title' => $edit['title'] ?? '',
    'experience' => $edit['experience'] ?? '',
    'short_text' => $edit['short_text'] ?? '',
    'bio' => $edit['bio'] ?? '',
    'specialties' => $edit['specialties'] ?? '',
    'certifications' => $edit['certifications'] ?? '',
    'phone' => $edit['phone'] ?? '',
    'email' => $edit['email'] ?? '',
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>HLV - Websites</title>
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
      <h4 class="hf-page-title mb-0"><i class="fa-solid fa-user-tie"></i> Huấn luyện viên (HLV)</h4>
      <div class="text-muted">HLV bạn thêm sẽ hiển thị trong trang <b>/login-role/trainer/trainer.php</b>.</div>
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
          <h5 class="mb-3"><?= $form['id'] ? 'Sửa HLV' : 'Thêm HLV mới' ?></h5>

          <form method="post" enctype="multipart/form-data">
            <?php if ($form['id']): ?>
              <input type="hidden" name="update_id" value="<?= (int)$form['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label hf-form-label">Tên HLV *</label>
              <input class="form-control" name="name" value="<?= h($form['name']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Slug (tuỳ chọn)</label>
              <input class="form-control" name="slug" value="<?= h($form['slug']) ?>" placeholder="Tu dong theo ten">
            </div>

            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label hf-form-label">Chức danh</label>
                <input class="form-control" name="title" value="<?= h($form['title']) ?>" placeholder="PT Gym / Yoga Coach...">
              </div>
              <div class="col-md-6">
                <label class="form-label hf-form-label">Kinh nghiệm</label>
                <input class="form-control" name="experience" value="<?= h($form['experience']) ?>" placeholder="5 năm...">
              </div>
            </div>

            <div class="mb-3 mt-2">
              <label class="form-label hf-form-label">Mô tả ngắn</label>
              <textarea class="form-control" name="short_text" rows="2" placeholder="Hiển thị ở thẻ HLV..."><?= h($form['short_text']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Bio chi tiết</label>
              <textarea class="form-control" name="bio" rows="4" placeholder="Hồ sơ chi tiết..."><?= h($form['bio']) ?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Chuyên môn (cách nhau bằng dấu phẩy)</label>
              <input class="form-control" name="specialties" value="<?= h($form['specialties']) ?>" placeholder="Giảm mỡ, Tăng cơ, Yoga trị liệu...">
            </div>

            <div class="mb-3">
              <label class="form-label hf-form-label">Chứng chỉ (cách nhau bằng dấu phẩy)</label>
              <input class="form-control" name="certifications" value="<?= h($form['certifications']) ?>" placeholder="NASM, ACE...">
            </div>

            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label hf-form-label">Điện thoại</label>
                <input class="form-control" name="phone" value="<?= h($form['phone']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label hf-form-label">Email</label>
                <input class="form-control" type="email" name="email" value="<?= h($form['email']) ?>">
              </div>
            </div>

            <div class="mb-3 mt-2">
              <label class="form-label hf-form-label">Ảnh đại diện</label>
              <input class="form-control" type="file" name="avatar" accept="image/*">
            </div>

            <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> <?= $form['id'] ? 'Cập nhật' : 'Thêm HLV' ?></button>
            <?php if ($form['id']): ?>
              <a class="btn btn-outline-secondary" href="trainers.php">Hủy sửa</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <div class="hf-note mt-3">
        HLV cũ (hardcode trong file trainers-data.php) vẫn giữ nguyên.
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Danh sách HLV đã thêm (DB)</h5>

          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Ảnh</th>
                  <th>Thông tin</th>
                  <th>Trạng thái</th>
                  <th style="width:160px">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($items)): ?>
                  <?php foreach ($items as $it): ?>
                    <tr>
                      <td>
                        <?php if (!empty($it['avatar'])): ?>
                          <img class="hf-thumb" src="<?= h(hf_asset_url($it['avatar'])) ?>" alt="">
                        <?php else: ?>
                          <span class="text-muted">(none)</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="fw-bold"><?= h($it['name']) ?></div>
                        <div class="text-muted small"><?= h($it['title'] ?? '') ?><?= ($it['experience'] ? ' • '.h($it['experience']) : '') ?></div>
                        <div class="text-muted small">slug: <?= h($it['slug']) ?></div>
                      </td>
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
                        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$it['id'] ?>" onclick="return confirm('Xóa HLV này?')"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-muted">Chưa có HLV nào trong DB.</td></tr>
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
