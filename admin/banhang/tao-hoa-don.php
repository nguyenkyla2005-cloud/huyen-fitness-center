<?php include 'dp.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bán hàng mới</title>
    <link rel="stylesheet" href="../menuchung/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pos-container { display: flex; gap: 20px; padding: 20px; }
        .product-list { flex: 2; background: white; padding: 15px; border-radius: 8px; }
        .cart-summary { flex: 1; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .input-group input { width: 100%; padding: 8px; border: 1px solid #ccc; }
        .btn-pay { width: 100%; background: #28a745; color: white; padding: 15px; border: none; font-size: 18px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include '../menuchung/main.php'; ?> <div class="pos-container">
        <div class="product-list">
    <h3>Danh sách dịch vụ/sản phẩm</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Tên dịch vụ</th>
                <th>Giá tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
    <?php
    // Truy vấn lấy danh sách sản phẩm từ database
    $sql = "SELECT * FROM san_pham ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($sp = $result->fetch_assoc()) {
            $ten_sp = $sp['ten_san_pham'];
            $thu_muc_anh = "images/";
            
            
            // TỰ ĐỘNG TÌM FILE: Tìm mọi file có tên sản phẩm với bất kỳ đuôi mở rộng nào
            $files = glob($thu_muc_anh . $ten_sp . ".*");
            
            // Nếu tìm thấy, lấy file đầu tiên; nếu không, dùng ảnh mặc định
            $duong_dan_anh = (count($files) > 0) ? $files[0] : "https://via.placeholder.com/50";
            
            echo "<tr>
                    <td>
                        <img src='{$duong_dan_anh}?v=" . time() . "' 
                             onerror=\"this.src='https://via.placeholder.com/50'\" 
                             style='width:50px; height:50px; border-radius:4px; object-fit:cover; border: 1px solid #ddd;'>
                    </td>
                    <td>{$sp['ten_san_pham']}</td>
                    <td>" . number_format($sp['gia_tien']) . " VNĐ</td>
                    <td>
                        <button type='button' class='btn-add-item' 
                                style='background:#007bff; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'
                                onclick=\"addToCart('{$sp['ten_san_pham']}', {$sp['gia_tien']})\">
                            Thêm
                        </button>
                    </td>
                  </tr>";
            
        }
    }
    ?>
</tbody>
    </table>
</div>

        <div class="cart-summary">
            <form action="xu-ly-ban-hang.php" method="POST">
                <h3>Thông tin hóa đơn</h3>
                <div class="input-group">
    <label>Tên khách hàng</label>
    <div style="display: flex; gap: 5px;">
        <input type="text" name="ten_khach" id="customer_name" required placeholder="Nhập tên khách hàng...">
        
        <button type="button" onclick="setGuest()" style="white-space: nowrap; background: #6c757d; color: white; border: none; padding: 0 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">
            Khách lẻ
        </button>
    </div>
</div>
                <div class="input-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="dien_thoai">
                </div>
                <hr>
                <div id="cart-items">
                    </div>
                <div class="input-group">
                    <label>Tổng tiền thanh toán</label>
                    <input type="number" name="tong_tien" id="total-amount" readonly value="0">
                </div>
                <button type="submit" class="btn-pay">THANH TOÁN (F1)</button>
            </form>
        </div>
    </div>

    <script>
        function addToCart(name, price) {
            // Logic Javascript để thêm dòng vào giỏ hàng và cộng tổng tiền
            document.getElementById('total-amount').value = price;
            // (Bạn có thể mở rộng thêm việc cộng dồn nhiều món ở đây)
        }
    </script>
</body>
</html>


<script>
    let total = 0;

function addToCart(name, price) {
    // 1. Cộng tổng tiền
    total += price;
    document.getElementById('total-amount').value = total;

    // 2. Hiển thị sản phẩm đã chọn vào khu vực cart-items
    const cartItems = document.getElementById('cart-items');
    const itemRow = document.createElement('div');
    itemRow.className = 'cart-item-row';
    itemRow.style = 'display:flex; justify-content:space-between; margin-bottom:5px; font-size:13px;';
    itemRow.innerHTML = `
        <span>${name}</span>
        <span>${price.toLocaleString()}đ</span>
        <input type="hidden" name="items[]" value="${name}|${price}">
    `;
    cartItems.appendChild(itemRow);
}
</script>

<script>
function setGuest() {
    // Tự động điền chữ "Khách lẻ" vào ô nhập tên
    document.getElementById('customer_name').value = "Khách lẻ";
    
    // Nếu muốn tự động điền luôn số điện thoại là trống hoặc mặc định
    // document.getElementsByName('dien_thoai')[0].value = "0000000000"; 
}
</script>

