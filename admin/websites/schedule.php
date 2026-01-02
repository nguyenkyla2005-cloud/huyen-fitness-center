<?php
require_once __DIR__ . '/_init.php';

$errors = [];
$success = '';

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$edit = null;
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM site_schedule WHERE id = ?");
        $stmt->execute([$id]);
        $edit = $stmt->fetch();
        if (!$edit) {
            $errors[] = 'Không tìm thấy dòng lịch.';
            $action = '';
        }
    } catch (Exception $e) {
        $errors[] = 'Không đọc được dữ liệu.';
    }
}

if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM site_schedule WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM site_schedule WHERE id = ?")->execute([$id]);

        if ($row && !empty($row['image']) && strpos($row['image'], 'uploads/') === 0) {
            $file = realpath(__DIR__ . '/../../' . '/' . $row['image']);
            if ($file && is_file($file)) @unlink($file);
        }

        $success = 'Đã xóa lịch.';
    } catch (Exception $e) {
        $errors[] = 'Không thể xóa lịch.';
    }
}

if ($action === 'toggle' && $id > 0) {
    try {
        $pdo->prepare("UPDATE site_schedule SET is_active = IF(is_active=1,0,1) WHERE id = ?")->execute([$id]);
        $success = 'Đã cập nhật trạng thái.';
    } catch (Exception $e) {
        $errors[] = 'Không thể cập nhật trạng thái.';
    }
}

$programOptions = [
    'yoga' => 'Yoga',
    'aerobic' => 'Aerobic',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_key = trim($_POST['club_key'] ?? 'cs1');
    $club_name = trim($_POST['club_name'] ?? 'Huyền Fitness');
    $program_key = trim($_POST['program_key'] ?? 'yoga');
    $program_name = trim($_POST['program_name'] ?? ($programOptions[$program_key] ?? $program_key));
    $week_key = trim($_POST['week_key'] ?? 'this');
    $week_label = trim($_POST['week_label'] ?? 'Tuần này');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($club_name === '') $errors[] = 'Vui lòng nhập tên cơ sở.';
    if ($program_key === '') $errors[] = 'Vui lòng chọn chương trình.';
    if ($week_key === '') $errors[] = 'Vui lòng nhập week_key.';
    if ($week_label === '') $errors[] = 'Vui lòng nhập nhãn tuần.';

    $uploadPath = null;
    if (!empty($_FILES['image']['name'])) {
        $upload = hf_save_upload($_FILES['image'], 'schedules');
        if (!$upload['ok']) $errors[] = $upload['error'];
        else $uploadPath = $upload['path'];
    }

    if (empty($errors)) {
        try {
            if (isset($_POST['update_id']) && (int)$_POST['update_id'] > 0) {
                $updateId = (int)$_POST['update_id'];

                if ($uploadPath) {
                    // remove old file
                    $stmt = $pdo->prepare("SELECT image FROM site_schedule WHERE id = ?");
                    $stmt->execute([$updateId]);
                    $old = $stmt->fetch();
                    if ($old && !empty($old['image']) && strpos($old['image'], 'uploads/') === 0) {
                        $file = realpath(__DIR__ . '/../../' . '/' . $old['image']);
                        if ($file && is_file($file)) @unlink($file);
                    }

                    $pdo->prepare("UPDATE site_schedule SET club_key=?, club_name=?, program_key=?, program_name=?, week_key=?, week_label=?, image=?, sort_order=? WHERE id=?")
                        ->execute([$club_key, $club_name, $program_key, $program_name, $week_key, $week_label, $uploadPath, $sort_order, $updateId]);
                } else {
                    $pdo->prepare("UPDATE site_schedule SET club_key=?, club_name=?, program_key=?, program_name=?, week_key=?, week_label=?, sort_order=? WHERE id=?")
                        ->execute([$club_key, $club_name, $program_key, $program_name, $week_key, $week_label, $sort_order, $updateId]);
                }

                $success = 'Đã cập nhật lịch.';
                $action = '';
                $edit = null;
            } else {
                if (!$uploadPath) {
                    $errors[] = 'Vui lòng chọn ảnh lịch.';
                } else {
                    $pdo->prepare("INSERT INTO site_schedule (club_key, club_name, program_key, program_name, week_key, week_label, image, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,1)")
                        ->execute([$club_key, $club_name, $program_key, $program_name, $week_key, $week_label, $uploadPath, $sort_order]);
                    $success = 'Đã thêm lịch mới.';
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Không thể lưu DB.';
        }
    }
}

$items = [];
try {
    $items = $pdo->query("SELECT * FROM site_schedule ORDER BY week_key DESC, program_key ASC, sort_order ASC, id DESC")->fetchAll();
} catch (Exception $e) {
    $errors[] = 'Không đọc được dữ liệu site_schedule.';
}

$form = [
    'id' => $edit['id'] ?? 0,
    'club_key' => $edit['club_key'] ?? 'cs1',
    'club_name' => $edit['club_name'] ?? 'Huyền Fitness',
    'program_key' => $edit['program_key'] ?? 'yoga',
    'program_name' => $edit['program_name'] ?? 'Yoga',
    'week_key' => $edit['week_key'] ?? 'this',
    'week_label' => $edit['week_label'] ?? '',
    'sort_order' => $edit['sort_order'] ?? 0,
];
if ($form['week_label'] === '') {
    $form['week_label'] = 'Tuần này';
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Lịch tập - Websites</title>
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
      <h4 class="hf-page-title mb-0"><i class="fa-regular fa-calendar"></i> Lịch tập (Ảnh)</h4>
      <div class="text-muted">Bạn upload ảnh lịch tập để thay đổi nhanh (Yoga/Aerobic...)</div>
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
          <h5 class="mb-3"><?= $form['id'] ? 'Sửa lịch' : 'Thêm lịch mới' ?></h5>

          <form method="post" enctype="multipart/form-data">
            <?php if ($form['id']): ?>
              <input type="hidden" name="update_id" value="<?= (int)$form['id'] ?>">
            <?php endif; ?>

            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label hf-form-label">club_key</label>
                <input class="form-control" name="club_key" value="<?= h($form['club_key']) ?>">
              </div>
              <div class="col-md-8">
                <label class="form-label hf-form-label">Tên cơ sở *</label>
                <input class="form-control" name="club_name" value="<?= h($form['club_name']) ?>" required>
              </div>
            </div>

            <div class="row g-2 mt-1">
              <div class="col-md-6">
                <label class="form-label hf-form-label">Chương trình *</label>
                <select class="form-select" name="program_key">
                  <?php foreach ($programOptions as $k=>$v): ?>
                    <option value="<?= h($k) ?>" <?= $form['program_key']===$k?'selected':'' ?>><?= h($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label hf-form-label">Tên hiển thị</label>
                <input class="form-control" name="program_name" value="<?= h($form['program_name']) ?>">
              </div>
            </div>

            <div class="row g-2 mt-1">
              <div class="col-md-4">
                <label class="form-label hf-form-label">week_key *</label>
                <input class="form-control" name="week_key" value="<?= h($form['week_key']) ?>" required>
              </div>
              <div class="col-md-8">
                <label class="form-label hf-form-label">Nhãn tuần *</label>
                <input class="form-control" name="week_label" value="<?= h($form['week_label']) ?>" required>
              </div>
            </div>

            <div class="row g-2 mt-1">
              <div class="col-md-4">
                <label class="form-label hf-form-label">Sort</label>
                <input class="form-control" type="number" name="sort_order" value="<?= (int)$form['sort_order'] ?>">
              </div>
              <div class="col-md-8">
                <label class="form-label hf-form-label">Ảnh lịch <?= $form['id'] ? '(chọn để thay)' : '*' ?></label>
                <input class="form-control" type="file" name="image" accept="image/*" <?= $form['id'] ? '' : 'required' ?>>
              </div>
            </div>

            <button class="btn btn-primary mt-3" type="submit">
              <i class="fa fa-save"></i> <?= $form['id'] ? 'Cập nhật' : 'Thêm lịch' ?>
            </button>
            <?php if ($form['id']): ?>
              <a class="btn btn-outline-secondary mt-3" href="schedule.php">Hủy sửa</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <div class="hf-note mt-3">
        Trang front-end sẽ ưu tiên dữ liệu DB. Nếu DB chưa có, trang sẽ dùng ảnh hardcode cũ.
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Danh sách lịch (DB)</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Ảnh</th>
                  <th>Club</th>
                  <th>Program</th>
                  <th>Week</th>
                  <th>Trạng thái</th>
                  <th style="width:160px">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($items)): ?>
                  <?php foreach ($items as $it): ?>
                    <tr>
                      <td><img class="hf-thumb" src="<?= h(hf_asset_url($it['image'])) ?>" alt=""></td>
                      <td>
                        <div class="fw-bold"><?= h($it['club_name']) ?></div>
                        <div class="text-muted small"><?= h($it['club_key']) ?></div>
                      </td>
                      <td>
                        <div class="fw-bold"><?= h($it['program_name']) ?></div>
                        <div class="text-muted small"><?= h($it['program_key']) ?></div>
                      </td>
                      <td>
                        <div class="fw-bold"><?= h($it['week_label']) ?></div>
                        <div class="text-muted small"><?= h($it['week_key']) ?></div>
                      </td>
                      <td>
                        <?php if ((int)$it['is_active'] === 1): ?>
                          <span class="badge bg-success">Đang dùng</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Ẩn</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$it['id'] ?>"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="?action=toggle&id=<?= (int)$it['id'] ?>" title="Ẩn/Hiện"><i class="fa fa-toggle-on"></i></a>
                        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$it['id'] ?>" onclick="return confirm('Xóa lịch này?')"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="6" class="text-muted">Chưa có lịch nào trong DB.</td></tr>
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
