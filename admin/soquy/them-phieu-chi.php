<?php 
include 'dp.php'; 
session_start(); 
$nguoi_tao = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Phiếu Chi Mới</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="content" style="padding: 20px; max-width: 600px; margin: auto;">
        <h2 style="color: #dc3545;"><i class="fa-solid fa-file-invoice-dollar"></i> Tạo Phiếu Chi (Xuất tiền)</h2>
        
        <form action="xu-ly-them-phieu-chi.php" method="POST" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #ddd; margin-top:20px;">
            
            <div style="margin-bottom:15px;">
                <label>Mã phiếu chi:</label>
                <input type="text" value="<?php echo 'PC' . time(); ?>" disabled style="width:100%; padding:8px; background:#eee; border:1px solid #ddd;">
                <input type="hidden" name="ma_phieu" value="<?php echo 'PC' . time(); ?>">
            </div>

            <div style="margin-bottom:15px;">
                <label>Tên phiếu chi (Lý do chi):</label>
                <input type="text" name="ten_phieu" required placeholder="Ví dụ: Trả tiền điện tháng 12, Mua nước suối..." style="width:100%; padding:8px;">
            </div>

            <div style="margin-bottom:15px;">
                <label>Loại chứng từ:</label>
                <input type="text" value="Phiếu chi" disabled style="width:100%; padding:8px; background:#eee; border:1px solid #ddd; color: #dc3545; font-weight: bold;">
                <input type="hidden" name="loai_hd" value="Phiếu chi">
            </div>

            <div style="margin-bottom:15px;">
                <label>Người nhận tiền:</label>
                <input type="text" name="nguoi_nhan" required placeholder="Ví dụ: Điện lực, Nhân viên A..." style="width:100%; padding:8px;">
            </div>

            <div style="margin-bottom:15px;">
                <label>Người lập phiếu:</label>
                <input type="text" value="<?php echo $nguoi_tao; ?>" disabled style="width:100%; padding:8px; background:#eee; border:1px solid #ddd;">
                <input type="hidden" name="nhan_vien" value="<?php echo $nguoi_tao; ?>">
            </div>

            <div style="margin-bottom:15px;">
                <label>Giá trị xuất quỹ (VNĐ):</label>
                <input type="number" name="gia_tri" required min="0" style="width:100%; padding:8px; font-weight:bold; color:#dc3545;">
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" style="background:#dc3545; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; flex:1;">Xác nhận Chi tiền</button>
                <a href="so-quy.php" style="display:block; padding:10px 20px; text-decoration:none; color:#666; border:1px solid #ddd; border-radius:4px;">Hủy</a>
            </div>
        </form>
    </div>
</body>
</html>