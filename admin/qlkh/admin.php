<?php
require 'db.php';

// 1. SESSION: Chỉ start nếu chưa có session nào chạy
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BẢO MẬT: Kiểm tra đăng nhập Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// --- 3. XỬ LÝ LOGIC TÌM KIẾM VÀ BỘ LỌC ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cong_no = isset($_GET['cn']) ? $_GET['cn'] : 'all'; // cn: Tình trạng phí
$status = isset($_GET['sv']) ? $_GET['sv'] : 'all';  // sv: Dịch vụ

try {
    $sql = "SELECT * FROM customers WHERE 1=1";
    $params = [];

    // Lọc theo từ khóa (Tên, Mã, SĐT)
    if (!empty($search)) {
        $sql .= " AND (fullname LIKE ? OR customer_code LIKE ? OR phone LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Lọc theo trạng thái thời gian tập
    if ($status == 'active') {
        $sql .= " AND end_date >= CURDATE()";
    } elseif ($status == 'expired') {
        $sql .= " AND end_date < CURDATE()";
    }

    // --- [THAY ĐỔI 1] Lọc công nợ (Đã kích hoạt) ---
    if ($cong_no == 'debt') {
        $sql .= " AND debt > 0"; // Lấy những người có tiền nợ > 0
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

} catch (Exception $e) {
    $customers = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng - Huyền Fitness</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../menuchung/main.css">
    
    <link rel="stylesheet" href="style_admin.css">
</head>
<body>

<body>

<?php include '../menuchung/main.php'; ?>
<div class="container-fluid">
    

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <div class="sidebar-filter">
                <form id="filterForm" action="" method="GET">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    
                    <div class="filter-title">Bộ lọc tìm kiếm <i class="fa fa-chevron-up"></i></div>
                    
                    <div class="filter-group">
                        <label class="fw-bold" style="color:#666">Tình Trạng Phí</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input filter-radio" type="radio" name="cn" value="all" <?= $cong_no == 'all' ? 'checked' : '' ?>> Tất cả
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="cn" value="debt" <?= $cong_no == 'debt' ? 'checked' : '' ?>> 
                            <span class="text-danger fw-bold">Chưa thanh toán</span>
                        </div>
                    </div>

                    <div class="filter-title">Trạng thái dịch vụ <i class="fa fa-chevron-up"></i></div>
                    <div class="filter-group">
                        <div class="form-check small">
                            <input class="form-check-input filter-radio" type="radio" name="sv" value="all" <?= $status == 'all' ? 'checked' : '' ?>> 
                            Tất cả
                        </div>
                        <div class="form-check small">
                            <input class="form-check-input filter-radio" type="radio" name="sv" value="active" <?= $status == 'active' ? 'checked' : '' ?>> 
                            Đang hoạt động
                        </div>
                        <div class="form-check small">
                            <input class="form-check-input filter-radio" type="radio" name="sv" value="expired" <?= $status == 'expired' ? 'checked' : '' ?>> 
                            Hết hạn
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-10 p-3">
            <div class="content-header-wrapper mb-3">
                <div class="d-flex align-items-center flex-grow-1">
                    <h5 class="m-0 fw-bold me-4 text-nowrap">Quản lý thông tin Khách hàng</h5>
                    
                    <div class="search-box-container flex-grow-1">
                        <form action="" method="GET" class="d-flex gap-2">
                            <input type="hidden" name="cn" value="<?= htmlspecialchars($cong_no) ?>">
                            <input type="hidden" name="sv" value="<?= htmlspecialchars($status) ?>">
                            
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Nhập tên, SĐT, mã KH..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i> Tìm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex gap-2 ms-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="fa fa-plus-circle"></i> Tạo mới (F1)
                    </button>
                    
                    <a href="export_excel.php" class="btn btn-success" style="background-color: #198754; border-color: #198754;">
                        <i class="fa-solid fa-file-excel"></i> Xuất Excel
                    </a>

                    <button class="btn btn-dark" onclick="toggleFullScreen()">
                        <i class="fa-solid fa-expand"></i> Toàn màn hình
                    </button>
                </div>
            </div>

            <div class="table-container shadow-sm p-0">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Hành động</th>
                            <th>Tên khách hàng</th>
                            <th>Điện thoại</th>
                            <th>Dịch vụ & Thanh toán</th> 
                            <th>Ngày bắt đầu</th>
                            <th>Hết hạn</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($customers)): ?>
                            <?php foreach ($customers as $row): ?>
                            <?php 
                                $is_active = strtotime($row['end_date']) >= time();
                                $status_label = $is_active ? 'Đang hoạt động' : 'Hết hạn';
                                $status_class = $is_active ? 'status-active' : 'bg-secondary text-white px-2 py-1 rounded small';
                            ?>
                            <tr>
                                <td>
                                    <i class="fa fa-plus-square text-primary cursor-pointer me-2" 
                                       onclick="openExtendModal(<?= $row['id'] ?>, '<?= $row['fullname'] ?>')"
                                       title="Gia hạn"></i> 
                                    
                                    <i class="fa fa-edit text-warning cursor-pointer me-2" 
                                       onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)"
                                       title="Sửa thông tin"></i>

                                    <i class="fa fa-trash text-danger cursor-pointer" 
                                       onclick="confirmDelete(<?= $row['id'] ?>, '<?= $row['fullname'] ?>')"
                                       title="Xóa khách hàng"></i>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($row['fullname']) ?></div>
                                    <small class="text-muted"><?= $row['customer_code'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['service_name'] ?? '-') ?></div>
                                    <?php if (!empty($row['debt']) && $row['debt'] > 0): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="fa-solid fa-circle-exclamation"></i> Nợ: <?= number_format($row['debt']) ?>đ
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row['start_date'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['end_date'])) ?></td>
                                <td><span class="<?= $status_class ?>"><?= $status_label ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4">Không tìm thấy dữ liệu phù hợp.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="process_add_customer.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Thêm Khách Hàng Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã thẻ (Auto)</label>
                            <input type="text" class="form-control" placeholder="Hệ thống tự tạo..." disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chọn gói tập</label>
                            <select name="service_id" class="form-select">
                                <option value="1_day">1 Ngày - 70k</option>
                                <option value="1_month">1 Tháng - 500k</option>
                                <option value="3_month">3 Tháng - 1.200k</option>
                                <option value="6_month">6 Tháng - 2.100k</option>
                                <option value="1_year">1 Năm - 3.200k</option>
                            </select>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label fw-bold">Trạng thái thanh toán</label>
                            <div class="d-flex gap-4 border rounded p-3 bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_status" id="pay_now" value="paid" checked>
                                    <label class="form-check-label text-success fw-bold" for="pay_now">
                                        <i class="fa-solid fa-check-circle"></i> Thanh toán ngay
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_status" id="pay_debt" value="debt">
                                    <label class="form-check-label text-danger fw-bold" for="pay_debt">
                                        <i class="fa-solid fa-circle-exclamation"></i> Ghi nợ (Chưa đóng tiền)
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Lưu và Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="process_edit_customer.php" method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Sửa thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="extendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="process_extend.php" method="POST">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Gia hạn dịch vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Khách hàng: <strong id="extend_name" class="text-primary"></strong></p>
                    <input type="hidden" name="customer_id" id="extend_id">
                    <div class="mb-3">
                        <label class="form-label">Chọn gói gia hạn</label>
                        <select name="new_service" class="form-select">
                            <option value="1_day">1 Ngày - 70k</option>
                            <option value="1_month">1 Tháng - 500k</option>
                            <option value="3_month">3 Tháng - 1.200k</option>
                            <option value="6_month">6 Tháng - 2.100k</option>
                            <option value="1_year">1 Năm - 3.200k</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Thanh toán & Gia hạn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. Tự động submit form khi chọn filter bên trái
    document.querySelectorAll('.filter-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });

    // 2. Chế độ toàn màn hình
    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) { document.exitFullscreen(); }
        }
    }

    // 3. Mở Modal Sửa
    function openEditModal(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_fullname').value = data.fullname;
        document.getElementById('edit_phone').value = data.phone;
        new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    }

    // 4. Mở Modal Gia hạn
    function openExtendModal(id, name) {
        document.getElementById('extend_id').value = id;
        document.getElementById('extend_name').innerText = name;
        new bootstrap.Modal(document.getElementById('extendModal')).show();
    }

    // 5. Xác nhận Xóa
    function confirmDelete(id, name) {
        if (confirm("CẢNH BÁO:\nBạn có chắc chắn muốn xóa khách hàng [" + name + "] không?\n\nDữ liệu sẽ bị mất vĩnh viễn!")) {
            window.location.href = "process_delete_customer.php?id=" + id;
        }
    }

    // 6. Phím tắt F1
    document.addEventListener('keydown', function(event) {
        if (event.key === "F1") {
            event.preventDefault(); 
            new bootstrap.Modal(document.getElementById('addCustomerModal')).show();
        }
    });
</script>
</body>
</html>