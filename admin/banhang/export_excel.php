    <?php
    require 'dp.php'; // Kết nối CSDL

    // 1. Nhận tham số tìm kiếm từ URL (giống trang bán hàng)
    $ma_hd = isset($_GET['ma_hd']) ? $_GET['ma_hd'] : '';
    $ten_kh = isset($_GET['ten_kh']) ? $_GET['ten_kh'] : '';

    // 2. Thiết lập Header báo cho trình duyệt biết đây là file Excel (.xls)
    $filename = "Bao_cao_ban_hang_" . date('Y-m-d_H-i') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // 3. Bắt đầu nội dung file Excel (Dùng HTML để tạo định dạng)
    echo '<!DOCTYPE html>';
    echo '<html lang="vi">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<style>
            /* CSS định dạng cho file Excel */
            table { border-collapse: collapse; width: 100%; }
            td, th { border: 1px solid #000; padding: 5px; vertical-align: middle; }
            .header { background-color: #f2f2f2; font-weight: bold; text-align: center; }
            .title { font-size: 18px; font-weight: bold; text-align: center; height: 40px; border: none; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
        </style>';
    echo '</head>';
    echo '<body>';

    echo '<table>';

    // --- PHẦN TIÊU ĐỀ BÁO CÁO (Gộp cột) ---
    echo '<tr>';
    echo '<td colspan="9" class="title" style="border:none;">DANH SÁCH HÓA ĐƠN BÁN HÀNG</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="9" style="text-align:center; border:none;">Ngày xuất: ' . date('d/m/Y H:i') . '</td>';
    echo '</tr>';
    echo '<tr><td colspan="9" style="border:none;"></td></tr>'; // Dòng trống

    // --- DÒNG TIÊU ĐỀ CỘT ---
    echo '<tr class="header">';
    echo '<th>STT</th>';
    echo '<th>Mã hóa đơn</th>';
    echo '<th>Thời gian</th>';
    echo '<th>Khách hàng</th>';
    echo '<th>Điện thoại</th>';
    echo '<th>Nhân viên</th>';
    echo '<th>Tổng tiền</th>';
    echo '<th>Giảm giá</th>';
    echo '<th>Khách trả</th>';
    echo '<th>Trạng thái</th>';
    echo '</tr>';

    // 4. Truy vấn dữ liệu (Logic lọc giống hệt trang ban-hang.php)
    $sql = "SELECT * FROM hoa_don WHERE 1=1";

    if (!empty($ma_hd)) {
        $ma = $conn->real_escape_string($ma_hd);
        $sql .= " AND ma_hd LIKE '%$ma%'";
    }

    if (!empty($ten_kh)) {
        $ten = $conn->real_escape_string($ten_kh);
        $sql .= " AND (ten_khach LIKE '%$ten%' OR dien_thoai LIKE '%$ten%')";
    }

    $sql .= " ORDER BY thoi_gian DESC";
    $result = $conn->query($sql);

    $stt = 1;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Xử lý dữ liệu
            $ngay_tao = date('d/m/Y H:i', strtotime($row['thoi_gian']));
            $tong_tien = number_format($row['tong_tien_hang']);
            $giam_gia = number_format($row['giam_gia']);
            $khach_tra = number_format($row['khach_da_tra']);
            
            // Style màu trạng thái (tùy chọn)
            $style_status = ($row['trang_thai'] == 'Hoàn thành') ? 'color:green; font-weight:bold;' : 'color:red;';

            echo '<tr>';
            echo '<td class="text-center">' . $stt++ . '</td>';
            echo '<td class="text-center">' . $row['ma_hd'] . '</td>';
            echo '<td class="text-center">' . $ngay_tao . '</td>';
            echo '<td>' . $row['ten_khach'] . '</td>';
            // Thêm style mso-number-format để Excel hiểu đây là chuỗi số (giữ số 0 đầu)
            echo '<td class="text-center" style="mso-number-format:\'\@\'">' . $row['dien_thoai'] . '</td>';
            echo '<td>' . $row['nhan_vien'] . '</td>';
            echo '<td class="text-right">' . $tong_tien . '</td>';
            echo '<td class="text-right">' . $giam_gia . '</td>';
            echo '<td class="text-right">' . $khach_tra . '</td>';
            echo '<td class="text-center" style="' . $style_status . '">' . $row['trang_thai'] . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="10" class="text-center">Không có dữ liệu phù hợp</td></tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
    ?>