<?php
require_once __DIR__ . '/_init.php';

$errors = [];
$success = '';

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'toggle' && $id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT contacted FROM trial_registrations WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $contacted = (int)$row['contacted'];
            if ($contacted === 0) {
                $pdo->prepare("UPDATE trial_registrations SET contacted=1, contacted_at=NOW() WHERE id=?")->execute([$id]);
                $success = 'Đã đánh dấu: Đã liên hệ.';
            } else {
                $pdo->prepare("UPDATE trial_registrations SET contacted=0, contacted_at=NULL WHERE id=?")->execute([$id]);
                $success = 'Đã đánh dấu: Chưa liên hệ.';
            }
        }
    } catch (Exception $e) {
        $errors[] = 'Không thể cập nhật trạng thái.';
    }
}

if ($action === 'delete' && $id > 0) {
    try {
        $pdo->prepare("DELETE FROM trial_registrations WHERE id=?")->execute([$id]);
        $success = 'Đã xóa đăng ký.';
    } catch (Exception $e) {
        $errors[] = 'Không thể xóa đăng ký.';
    }
}

$filter = $_GET['filter'] ?? 'new'; // new | all | contacted
$where = '1=1';
if ($filter === 'new') $where = 'contacted = 0';
if ($filter === 'contacted') $where = 'contacted = 1';

$items = [];
try {
    $items = $pdo->query("SELECT * FROM trial_registrations WHERE {$where} ORDER BY created_at DESC, id DESC")->fetchAll();
} catch (Exception $e) {
    $errors[] = 'Không đọc được dữ liệu trial_registrations.';
}

function badge($contacted) {
    if ((int)$contacted === 1) return '<span class="badge bg-success">Đã liên hệ</span>';
    return '<span class="badge bg-danger">Chưa liên hệ</span>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký tập thử - Websites</title>
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
      <h4 class="hf-page-title mb-0"><i class="fa-solid fa-bell"></i> Đăng ký tập thử</h4>
      <div class="text-muted">Danh sách khách hàng đăng ký tập thử (từ trang chủ và trang Tập thử).</div>
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

  <div class="d-flex gap-2 mb-3">
    <a class="btn btn<?= $filter==='new'?'':'-outline' ?>-primary" href="?filter=new">Chưa liên hệ</a>
    <a class="btn btn<?= $filter==='contacted'?'':'-outline' ?>-success" href="?filter=contacted">Đã liên hệ</a>
    <a class="btn btn<?= $filter==='all'?'':'-outline' ?>-secondary" href="?filter=all">Tất cả</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Họ tên</th>
              <th>Điện thoại</th>
              <th>Email</th>
              <th>Thời gian</th>
              <th>Trạng thái</th>
              <th style="width:190px">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><?= (int)$it['id'] ?></td>
                  <td class="fw-bold"><?= h($it['fullname']) ?></td>
                  <td><?= h($it['phone']) ?></td>
                  <td><?= h($it['email'] ?? '') ?></td>
                  <td><?= h($it['created_at']) ?></td>
                  <td><?= badge($it['contacted']) ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" href="trial_view.php?id=<?= (int)$it['id'] ?>"><i class="fa fa-eye"></i></a>
                    <a class="btn btn-sm btn-outline-success" href="?action=toggle&id=<?= (int)$it['id'] ?>" title="Đã liên hệ / Chưa liên hệ">
                      <i class="fa fa-check"></i>
                    </a>
                    <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$it['id'] ?>" onclick="return confirm('Xóa đăng ký này?')"><i class="fa fa-trash"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-muted">Chưa có dữ liệu.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
