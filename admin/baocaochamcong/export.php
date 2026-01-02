<?php
include 'db.php';

// 1. NHẬN THAM SỐ LỌC (Giữ nguyên logic lọc)
$whereClause = "WHERE 1=1";

if (isset($_GET['tim_ten']) && !empty($_GET['tim_ten'])) {
    $tim_ten = $_GET['tim_ten'];
    $whereClause .= " AND ten_nv LIKE '%$tim_ten%'";
}
if (isset($_GET['tim_sdt']) && !empty($_GET['tim_sdt'])) {
    $tim_sdt = $_GET['tim_sdt'];
    $whereClause .= " AND sdt LIKE '%$tim_sdt%'";
}
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];
    $whereClause .= " AND ngay_cham_cong BETWEEN '$start' AND '$end'";
}

// 2. XÁC ĐỊNH LOẠI BÁO CÁO
$type = isset($_GET['type']) ? $_GET['type'] : 'summary';

if ($type == 'checkin') {
    $filename = "Checkin_" . date('d-m-Y') . ".csv";
    $columns = ['Mã NV', 'Tên Nhân Viên', 'Ngày', 'Giờ vào', 'Giờ ra', 'Ghi chú']; 
} else {
    $filename = "BaoCao_TongHop_" . date('d-m-Y') . ".csv";
    $columns = ['Mã NV', 'Tên Nhân Viên', 'SĐT', 'Ngày', 'Ca làm', 'Công', 'Muộn', 'Sớm', 'Nghỉ'];
}

// 3. THIẾT LẬP HEADER ĐỂ TẢI FILE
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Mở luồng ghi file
$output = fopen('php://output', 'w');

// --- QUAN TRỌNG: SỬA LỖI FONT TIẾNG VIỆT ---
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Ghi tiêu đề cột
fputcsv($output, $columns);

// 4. LẤY DỮ LIỆU
$sql = "SELECT * FROM cham_cong $whereClause ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        // --- XỬ LÝ FORMAT DỮ LIỆU ---
        
        // 1. Xử lý ngày: Chuyển sang dd/mm/yyyy để Excel dễ hiểu
        $ngay = isset($row['ngay_cham_cong']) ? date("d/m/Y", strtotime($row['ngay_cham_cong'])) : '';

        // 2. Xử lý Số điện thoại: Thêm dấu cách hoặc \t ở đầu để Excel hiểu là VĂN BẢN, không bị lỗi E+08
        $sdt = isset($row['sdt']) ? "\t" . $row['sdt'] : ''; 

        // 3. Xử lý Giờ: Chỉ hiển thị H:i (Giờ:Phút)
        $gio_vao = (!empty($row['gio_vao'])) ? date("H:i", strtotime($row['gio_vao'])) : '';
        $gio_ra  = (!empty($row['gio_ra']))  ? date("H:i", strtotime($row['gio_ra']))  : '';

        if ($type == 'checkin') {
            // Xuất dữ liệu checkin
            $lineData = [
                $row['ma_nv'], 
                $row['ten_nv'], 
                $ngay, 
                $gio_vao, 
                $gio_ra, 
                '' // Cột ghi chú để trống
            ];
        } else {
            // Xuất dữ liệu tổng hợp
            $lineData = [
                $row['ma_nv'], 
                $row['ten_nv'], 
                $sdt, // SĐT đã được sửa lỗi format
                $ngay,
                $row['so_ca_lam'], 
                $row['so_cong'], 
                $row['di_muon'], 
                $row['ve_som'], 
                $row['nghi']
            ];
        }
        fputcsv($output, $lineData);
    }
}
fclose($output);
exit();
?>