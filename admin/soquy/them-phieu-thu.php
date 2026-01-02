<?php include 'dp.php'; 
session_start();
$nguoi_dang_nhap = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Phiếu Thu Mới</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="content" style="padding: 20px; max-width: 600px; margin: auto;">
        <h2><i class="fa-solid fa-file-invoice-dollar"></i> Tạo Phiếu Thu Khác</h2>
        
        <form action="xu-ly-them-phieu-thu.php" method="POST" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #ddd; margin-top:20px;">
            
            <div style="margin-bottom:15px;">
                <label>Mã phiếu thu:</label>
                <input type="text" value="<?php echo 'PT' . time(); ?>" disabled style="width:100%; padding:8px; background:#eee; border:1px solid #ddd;">
                <input type="hidden" name="ma_phieu" value="<?php echo 'PT' . time(); ?>">
            </div>

            <div style="margin-bottom:15px;">
                <label>Tên phiếu thu (Lý do nộp):</label>
                <input type="text" name="ten_phieu" required placeholder="Ví dụ: Thu tiền bán giấy vụn..." style="width:100%; padding:8px;">
            </div>

            <div style="margin-bottom:15px;">
                <label>Loại thu chi:</label>
                <input type="text" value="Thu ngoài lề" disabled style="width:100%; padding:8px; background:#eee; border:1px solid #ddd;">
                <input type="hidden" name="loai_thu" value="Thu ngoài lề">
            </div>

            <div style="margin-bottom:15px;">
    <label>Người nộp:</label>
    
    <input type="text" value="<?php echo $nguoi_dang_nhap; ?>" disabled 
           style="width:100%; padding:8px; background:#eee; border:1px solid #ddd; font-weight:bold; color:#333;">
    
    <input type="hidden" name="nguoi_nop" value="<?php echo $nguoi_dang_nhap; ?>">
</div>

            <div style="margin-bottom:15px;">
                <label>Giá trị (VNĐ):</label>
                <input type="number" name="gia_tri" required min="0" style="width:100%; padding:8px; font-weight:bold; color:#28a745;">
            </div>

            <div style="margin-bottom:15px;">
                <label>Trạng thái:</label>
                <span style="background:#28a745; color:white; padding:5px 10px; border-radius:15px; font-size:12px;">Đã thanh toán</span>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; flex:1;">Lưu Phiếu Thu</button>
                <a href="so-quy.php" style="display:block; padding:10px 20px; text-decoration:none; color:#666; border:1px solid #ddd; border-radius:4px;">Hủy</a>
            </div>
        </form>
    </div>
</body>
</html>