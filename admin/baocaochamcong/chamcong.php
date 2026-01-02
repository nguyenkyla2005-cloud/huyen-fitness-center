<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include 'db.php';

// --- XỬ LÝ THÊM MỚI ---
if (isset($_POST['add_nv'])) {
    $ma = $_POST['ma_nv'];
    $ten = $_POST['ten_nv'];
    $sdt = $_POST['sdt'];
    // Lấy dữ liệu giờ vào/ra từ form
    $gio_vao = !empty($_POST['gio_vao']) ? $_POST['gio_vao'] : NULL;
    $gio_ra = !empty($_POST['gio_ra']) ? $_POST['gio_ra'] : NULL;
    
    $calam = !empty($_POST['so_ca_lam']) ? $_POST['so_ca_lam'] : 0;
    $cong = !empty($_POST['so_cong']) ? $_POST['so_cong'] : 0;
    $muon = !empty($_POST['di_muon']) ? $_POST['di_muon'] : 0;
    $som = !empty($_POST['ve_som']) ? $_POST['ve_som'] : 0;
    $nghi = !empty($_POST['nghi']) ? $_POST['nghi'] : 0;
    
    // Nếu $gio_vao là NULL thì trong SQL phải để là NULL hoặc '00:00:00'
    $sql_gio_vao = $gio_vao ? "'$gio_vao'" : "NULL";
    $sql_gio_ra = $gio_ra ? "'$gio_ra'" : "NULL";

    $sql = "INSERT INTO cham_cong (ma_nv, ten_nv, sdt, gio_vao, gio_ra, so_ca_lam, so_cong, di_muon, ve_som, nghi, ngay_cham_cong) 
            VALUES ('$ma', '$ten', '$sdt', $sql_gio_vao, $sql_gio_ra, '$calam', '$cong', '$muon', '$som', '$nghi', CURRENT_DATE())";
    
    if (!$conn->query($sql)) {
        echo "Lỗi: " . $conn->error;
    } else {
        header("Location: chamcong.php");
    }
}

// --- XỬ LÝ CẬP NHẬT ---
if (isset($_POST['update_nv'])) {
    $id = $_POST['edit_id'];
    $ma = $_POST['ma_nv'];
    $ten = $_POST['ten_nv'];
    $sdt = $_POST['sdt'];
    
    $gio_vao = !empty($_POST['gio_vao']) ? $_POST['gio_vao'] : NULL;
    $gio_ra = !empty($_POST['gio_ra']) ? $_POST['gio_ra'] : NULL;
    $sql_gio_vao = $gio_vao ? "'$gio_vao'" : "NULL";
    $sql_gio_ra = $gio_ra ? "'$gio_ra'" : "NULL";

    $calam = $_POST['so_ca_lam'];
    $cong = $_POST['so_cong'];
    $muon = $_POST['di_muon'];
    $som = $_POST['ve_som'];
    $nghi = $_POST['nghi'];
    
    $sql = "UPDATE cham_cong SET 
            ma_nv='$ma', ten_nv='$ten', sdt='$sdt', 
            gio_vao=$sql_gio_vao, gio_ra=$sql_gio_ra,
            so_ca_lam='$calam', so_cong='$cong', 
            di_muon='$muon', ve_som='$som', nghi='$nghi' 
            WHERE id='$id'";
    $conn->query($sql);
    header("Location: chamcong.php");
}

// --- XỬ LÝ XÓA ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM cham_cong WHERE id=$id");
    header("Location: chamcong.php");
}

// --- BỘ LỌC & PHÂN TRANG ---
$whereClause = "WHERE 1=1";
$params = "";

if (isset($_GET['tim_ten']) && !empty($_GET['tim_ten'])) {
    $tim_ten = $_GET['tim_ten'];
    $whereClause .= " AND ten_nv LIKE '%$tim_ten%'";
    $params .= "&tim_ten=$tim_ten";
}
if (isset($_GET['tim_sdt']) && !empty($_GET['tim_sdt'])) {
    $tim_sdt = $_GET['tim_sdt'];
    $whereClause .= " AND sdt LIKE '%$tim_sdt%'";
    $params .= "&tim_sdt=$tim_sdt";
}
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
if (!empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND ngay_cham_cong BETWEEN '$start_date' AND '$end_date'";
    $params .= "&start_date=$start_date&end_date=$end_date";
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$params .= "&limit=$limit";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$sqlCount = "SELECT count(id) as total FROM cham_cong $whereClause";
$resultCount = $conn->query($sqlCount);
$rowCount = $resultCount->fetch_assoc();
$total_records = $rowCount['total'];
$total_pages = ceil($total_records / $limit);

$sqlData = "SELECT * FROM cham_cong $whereClause ORDER BY id DESC LIMIT $offset, $limit";
$result = $conn->query($sqlData);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chấm công</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="chamcong.css">
</head>
<body>

    <?php include '../menuchung/main.php'; ?>

    <div class="report-container">
        <aside class="filter-sidebar">
            <form method="GET" action="" id="filterForm">
                <div class="filter-group">
                    <div class="filter-header">Tìm kiếm</div>
                    <div class="filter-body">
                        <input type="text" name="tim_ten" placeholder="Theo Nhân viên" class="filter-input" value="<?php echo isset($_GET['tim_ten']) ? $_GET['tim_ten'] : ''; ?>">
                        <input type="text" name="tim_sdt" placeholder="Số điện thoại" class="filter-input" value="<?php echo isset($_GET['tim_sdt']) ? $_GET['tim_sdt'] : ''; ?>">
                        <button type="submit" class="btn-search-sidebar"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
                    </div>
                </div>
                <div class="filter-group">
                    <div class="filter-header">Thời gian <i class="fa-solid fa-chevron-up float-right"></i></div>
                    <div class="filter-body">
                        <div class="date-range-container">
                            <label>Từ ngày:</label>
                            <input type="date" name="start_date" class="filter-input" value="<?php echo $start_date; ?>">
                            <label>Đến ngày:</label>
                            <input type="date" name="end_date" class="filter-input" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <div class="filter-header">Tổng số bản ghi</div>
                    <div class="filter-body">
                        <p style="font-weight: bold; color: #333;">Tổng số: <?php echo $total_records; ?></p>
                    </div>
                </div>
            </form>
            <div class="bottom-user-icon"><i class="fa-solid fa-user"></i></div>
        </aside>

        <main class="report-content">
            <div class="content-header-row">
                <h2>Báo cáo chấm công</h2>
                <div class="header-actions">
                    <button class="btn-green" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Thêm mới</button>
                    <a href="export.php?type=summary<?php echo $params; ?>" class="btn-green" style="text-decoration: none; color: white;">
                        <i class="fa-solid fa-file-export"></i> Xuất file
                    </a>
                    <a href="export.php?type=checkin<?php echo $params; ?>" class="btn-green" style="text-decoration: none; color: white;">
                        <i class="fa-solid fa-file-export"></i> Xuất dữ liệu checkin
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Tên nhân viên</th>
                            <th class="text-center">Ngày</th>
                            <th class="text-center">Vào</th>
                            <th class="text-center">Ra</th>
                            <th class="text-center">Ca làm</th>
                            <th class="text-center">Công</th>
                            <th class="text-center">Nghỉ</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $ngay_hien_thi = isset($row['ngay_cham_cong']) ? date("d/m/Y", strtotime($row['ngay_cham_cong'])) : 'N/A';
                                // Xử lý hiển thị giờ (nếu null thì hiện --)
                                $show_vao = $row['gio_vao'] ? date("H:i", strtotime($row['gio_vao'])) : '--:--';
                                $show_ra = $row['gio_ra'] ? date("H:i", strtotime($row['gio_ra'])) : '--:--';
                        ?>
                        <tr>
                            <td><?php echo $row['ma_nv']; ?></td>
                            <td><?php echo $row['ten_nv']; ?></td>
                            <td class="text-center" style="color:#007bff;"><?php echo $ngay_hien_thi; ?></td>
                            
                            <td class="text-center" style="font-weight:bold; color: #28a745;"><?php echo $show_vao; ?></td>
                            <td class="text-center" style="font-weight:bold; color: #dc3545;"><?php echo $show_ra; ?></td>
                            
                            <td class="text-center"><?php echo $row['so_ca_lam']; ?></td>
                            <td class="text-center"><?php echo $row['so_cong']; ?></td>
                            <td class="text-center"><?php echo $row['nghi']; ?></td>
                            <td class="text-center">
                                <button class="btn-action btn-edit" onclick='openEditModal(<?php echo json_encode($row); ?>)'><i class="fa-solid fa-pen"></i></button>
                                <a href="chamcong.php?delete_id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Xóa?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>Không tìm thấy dữ liệu</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-footer">
                <div class="pagination-info">Tổng số: <b><?php echo $total_records; ?></b></div>
                <div class="pagination-controls">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="chamcong.php?page=<?php echo $i . $params; ?>" class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h3>Thêm nhân viên mới</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <input type="text" name="ma_nv" placeholder="Mã NV" required>
                    <input type="text" name="ten_nv" placeholder="Tên nhân viên" required>
                </div>
                <div class="form-row">
                    <input type="text" name="sdt" placeholder="Số điện thoại" style="width: 100%;">
                </div>
                
                <div class="form-row" style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: bold;">Giờ vào:</label>
                        <input type="time" name="gio_vao" class="filter-input" style="width: 100%;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: bold;">Giờ ra:</label>
                        <input type="time" name="gio_ra" class="filter-input" style="width: 100%;">
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-item"><label>Ca làm</label><input type="number" name="so_ca_lam" value="0"></div>
                    <div class="stat-item"><label>Công</label><input type="number" name="so_cong" value="0"></div>
                    <div class="stat-item"><label>Muộn</label><input type="number" name="di_muon" value="0"></div>
                    <div class="stat-item"><label>Sớm</label><input type="number" name="ve_som" value="0"></div>
                    <div class="stat-item"><label>Nghỉ</label><input type="number" name="nghi" value="0"></div>
                </div>
                <button type="submit" name="add_nv" class="btn-submit">Lưu lại</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>Cập nhật thông tin</h3>
            <form method="POST" action="">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="form-row">
                    <input type="text" name="ma_nv" id="edit_ma" placeholder="Mã NV" required>
                    <input type="text" name="ten_nv" id="edit_ten" placeholder="Tên nhân viên" required>
                </div>
                <div class="form-row">
                    <input type="text" name="sdt" id="edit_sdt" placeholder="Số điện thoại" style="width: 100%;">
                </div>

                <div class="form-row" style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: bold;">Giờ vào:</label>
                        <input type="time" name="gio_vao" id="edit_gio_vao" class="filter-input" style="width: 100%;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 12px; font-weight: bold;">Giờ ra:</label>
                        <input type="time" name="gio_ra" id="edit_gio_ra" class="filter-input" style="width: 100%;">
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-item"><label>Ca làm</label><input type="number" name="so_ca_lam" id="edit_calam"></div>
                    <div class="stat-item"><label>Công</label><input type="number" name="so_cong" id="edit_cong"></div>
                    <div class="stat-item"><label>Muộn</label><input type="number" name="di_muon" id="edit_muon"></div>
                    <div class="stat-item"><label>Sớm</label><input type="number" name="ve_som" id="edit_som"></div>
                    <div class="stat-item"><label>Nghỉ</label><input type="number" name="nghi" id="edit_nghi"></div>
                </div>
                <button type="submit" name="update_nv" class="btn-submit">Cập nhật</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = "block"; }
        function closeModal(id) { document.getElementById(id).style.display = "none"; }
        
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_ma').value = data.ma_nv;
            document.getElementById('edit_ten').value = data.ten_nv;
            document.getElementById('edit_sdt').value = data.sdt;
            
            // Đổ dữ liệu giờ vào/ra vào form sửa
            document.getElementById('edit_gio_vao').value = data.gio_vao;
            document.getElementById('edit_gio_ra').value = data.gio_ra;
            
            document.getElementById('edit_calam').value = data.so_ca_lam;
            document.getElementById('edit_cong').value = data.so_cong;
            document.getElementById('edit_muon').value = data.di_muon;
            document.getElementById('edit_som').value = data.ve_som;
            document.getElementById('edit_nghi').value = data.nghi;
            openModal('editModal');
        }
        window.onclick = function(event) { if (event.target.className === 'modal') event.target.style.display = "none"; }
    </script>
</body>
</html>