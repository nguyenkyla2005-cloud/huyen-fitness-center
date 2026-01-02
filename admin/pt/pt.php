<?php
// Lùi ra thư mục gốc để lấy db.php
require 'db.php';

// --- THÊM DÒNG NÀY ĐỂ CHỈNH GIỜ VỀ VIỆT NAM ---
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Kiểm tra đăng nhập
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php"); exit;
}

// --- LOGIC NGÀY THÁNG ---
$today = time(); 
$start_of_week = isset($_GET['week_start']) ? strtotime($_GET['week_start']) : strtotime('monday this week', $today);

// Tính toán lại ngày trong tuần
$week_dates = [];
$week_days_label = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
$db_dates = []; 

for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+$i days", $start_of_week);
    $ymd = date('Y-m-d', $ts);
    $week_dates[] = [
        'label' => $week_days_label[$i],
        'date_short' => date('d/m', $ts), // Giữ cái này cho tiêu đề cột (ngắn gọn)
        'date_display' => date('d/m/Y', $ts), // Thêm cái này cho nút chọn thời gian (đầy đủ năm)
        'full_date' => $ymd,
        'is_today' => ($ymd == date('Y-m-d'))
    ];
    $db_dates[] = $ymd;
}

// --- LẤY DỮ LIỆU ---
$search_text = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_trainer = isset($_GET['trainer']) ? $_GET['trainer'] : '';

$sql = "SELECT * FROM pt_bookings WHERE booking_date BETWEEN ? AND ?";
$params = [$db_dates[0], $db_dates[6]];

if (!empty($search_text)) {
    $sql .= " AND customer_name LIKE ?";
    $params[] = "%$search_text%";
}
if (!empty($search_trainer)) {
    $sql .= " AND trainer_name = ?";
    $params[] = $search_trainer;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$schedule_map = [];
foreach ($bookings as $b) {
    $d = $b['booking_date'];
    $t = $b['time_slot'];
    if (!isset($schedule_map[$d][$t])) {
        $schedule_map[$d][$t] = [];
    }
    $schedule_map[$d][$t][] = $b;
}

$time_slots = [
    "05:00 - 06:00", "06:00 - 07:00", "07:00 - 08:00", "08:00 - 09:00",
    "09:00 - 10:00", "10:00 - 11:00", "11:00 - 12:00", "13:00 - 14:00",
    "14:00 - 15:00", "15:00 - 16:00", "16:00 - 17:00", "17:00 - 18:00",
    "18:00 - 19:00", "19:00 - 20:00", "20:00 - 21:00", "21:00 - 22:00"
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý lịch tập HLV - Huyền Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../qlkh/style_admin.css">

    <link rel="stylesheet" href="../menuchung/main.css">

    <style>
        /* CSS GIAO DIỆN RIÊNG CHO TRANG PT */
        .pt-toolbar { background: #fff; padding: 10px 15px; border-bottom: 1px solid #dee2e6; }
        .btn-green-action {
            background-color: #28a745; color: white; border: none;
            font-size: 13px; padding: 6px 12px; border-radius: 4px;
            display: flex; align-items: center; gap: 5px; white-space: nowrap;
        }
        .btn-green-action:hover { background-color: #218838; color: white; }
        .search-pt-group { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        
        .calendar-container { height: calc(100vh - 135px); overflow-y: auto; background: white; } /* Đã chỉnh lại chiều cao chút */
        .calendar-table { font-size: 12px; border-color: #e5e7eb; width: 100%; table-layout: fixed; }
        
        .calendar-table thead th { 
            background-color: #f3f4f6; text-align: center; vertical-align: middle; 
            padding: 10px 5px; color: #4b5563; border-bottom: 2px solid #dee2e6;
            position: sticky; top: 0; z-index: 10;
        }
        
        .calendar-table tbody td { height: 50px; vertical-align: top; border-color: #e5e7eb; padding: 2px; }
        .calendar-table tbody td:first-child { 
            background-color: #f9fafb; font-weight: 600; text-align: center; vertical-align: middle;
            color: #6b7280; width: 90px; position: sticky; left: 0; z-index: 5;
        }
        .calendar-table tbody td:not(:first-child):hover { background-color: #f0fdf4; cursor: pointer; }
        
        .today-highlight { color: #007bff; background-color: #e7f1ff !important; border-bottom: 2px solid #007bff !important; }

        .booking-card {
            font-size: 11px; padding: 4px 8px; margin-bottom: 3px; border-radius: 4px;
            display: flex; justify-content: space-between; align-items: center;
            color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.15); font-weight: 500;
        }
        .bg-trainer-1 { background-color: #0d6efd !important; border-left: 3px solid #004085; } 
        .bg-trainer-2 { background-color: #d63384 !important; border-left: 3px solid #70103d; } 
        .booking-card i.fa-trash { opacity: 0.7; cursor: pointer; padding: 2px; }
        .booking-card i.fa-trash:hover { opacity: 1; color: #ffe6e6; transform: scale(1.1); }
    </style>
</head>
<body>

<?php include '../menuchung/main.php'; ?>

<div class="container-fluid p-0" style="margin-top: 10px;">
    <div class="pt-toolbar">
        <form action="" method="GET" id="filterForm">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex flex-column gap-2">
                    <h6 class="m-0 fw-bold text-secondary">Quản lý lịch tập HLV</h6>
                    <div class="search-pt-group">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Tìm khách hàng..." value="<?= htmlspecialchars($search_text) ?>" style="width: 180px;">
                        
                        <select name="trainer" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                            <option value="">-- Tất cả HLV --</option>
                            <option value="HLV Công" <?= $search_trainer == 'HLV Công' ? 'selected' : '' ?>>HLV Công</option>
                            <option value="HLV Huyền" <?= $search_trainer == 'HLV Huyền' ? 'selected' : '' ?>>HLV Huyền</option>
                        </select>

                        <div class="position-relative d-flex align-items-center" 
                             style="background: #e9ecef; padding: 6px 12px; border-radius: 5px; border: 1px solid #ced4da;">
                            
                            <span style="font-size: 13px; color: #495057; font-weight: 500; margin-right: 10px;">
                                <?= $week_dates[0]['date_display'] ?> - <?= $week_dates[6]['date_display'] ?>
                            </span>
                            
                            <i class="fa fa-calendar-alt text-secondary"></i>
                            
                            <input type="date" 
                                   class="position-absolute top-0 start-0 w-100 h-100" 
                                   style="opacity: 0; cursor: pointer;"
                                   value="<?= date('Y-m-d', $start_of_week) ?>"
                                   onchange="window.location.href='?week_start='+this.value">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i></button>
                    </div>
                </div>

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-green-action" onclick="openBookingModal()">
                        <i class="fa fa-calendar-plus"></i> Đặt lịch
                    </button>
                    <a href="export_pt_excel.php" class="btn btn-green-action text-decoration-none">
                        <i class="fa fa-file-excel"></i> Xuất file
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="calendar-container">
        <table class="table table-bordered calendar-table m-0">
            <thead class="sticky-top" style="z-index: 2;">
                <tr>
                    <th style="width: 100px;">Khung giờ</th>
                    <?php foreach($week_dates as $wd): ?>
                        <th class="<?= $wd['is_today'] ? 'today-highlight' : '' ?>">
                            <?= $wd['label'] ?> <br> <small><?= $wd['date_short'] ?></small>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($time_slots as $slot): ?>
                <tr>
                    <td><?= $slot ?></td>
                    <?php for($d=0; $d<7; $d++): ?>
                        <?php 
                            $curr_date = $week_dates[$d]['full_date'];
                            $cell_data = isset($schedule_map[$curr_date][$slot]) ? $schedule_map[$curr_date][$slot] : [];
                        ?>
                        <td onclick="if(event.target.tagName !== 'I') selectCell('<?= $curr_date ?>', '<?= $slot ?>')">
                            <?php foreach($cell_data as $bk): ?>
                                <?php $color_class = ($bk['trainer_name'] == 'HLV Công') ? 'bg-trainer-1' : 'bg-trainer-2'; ?>
                                <div class="booking-card <?= $color_class ?>" title="<?= $bk['trainer_name'] ?>">
                                    <span><?= htmlspecialchars($bk['customer_name']) ?></span>
                                    <i class="fa fa-trash" onclick="confirmDelete(<?= $bk['id'] ?>)"></i>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Đặt lịch tập mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_add_booking.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ngày tập</label>
                            <input type="date" name="booking_date" id="modal_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Khung giờ</label>
                            <select name="time_slot" id="modal_time" class="form-select" required>
                                <?php foreach($time_slots as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Tên khách hàng</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Nhập tên khách..." required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">HLV phụ trách</label>
                            <select name="trainer_name" class="form-select">
                                <option value="HLV Công">HLV Công (Xanh)</option>
                                <option value="HLV Huyền">HLV Huyền (Hồng)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu lịch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openBookingModal() {
        document.getElementById('modal_date').valueAsDate = new Date();
        var myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        myModal.show();
    }
    function selectCell(date, time) {
        document.getElementById('modal_date').value = date;
        document.getElementById('modal_time').value = time;
        var myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        myModal.show();
    }
    function confirmDelete(id) {
        if(confirm('Bạn có chắc chắn muốn xóa lịch tập này không?')) {
            window.location.href = 'process_delete_booking.php?id=' + id;
        }
    }
</script>
</body>
</html>