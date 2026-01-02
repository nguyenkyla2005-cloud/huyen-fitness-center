<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>

<nav class="navbar">
    <div class="nav-left">
        <ul class="nav-menu">
            <li><a href="../qlkh/admin.php"><i class="fa-solid fa-users"></i> Khách hàng</a></li>
            <li><a href="../banhang/ban-hang.php"><i class="fa-solid fa-cart-shopping"></i> Bán hàng</a></li>
            <li><a href="../pt/pt.php"><i class="fa-solid fa-user-tie"></i> PT</a></li>
            <li><a href="../soquy/so-quy.php"><i class="fa-solid fa-dollar-sign"></i> Số quỹ</a></li>
            <li class="has-child">
    <a href="#"><i class="fa-solid fa-chart-line"></i> Báo cáo <i class="fa-solid fa-caret-down" style="font-size: 12px; margin-left: 5px;"></i></a>
    
    <ul class="sub-menu">
        <li><a href="../baocaochamcong/chamcong.php">Báo cáo chấm công</a></li>
        <li><a href="../baocaobanhang/bao-cao-ban-hang.php">Báo cáo bán hàng</a></li>
    </ul>
</li>
            
            <li><a href="../websites/index.php"><i class="fa-solid fa-globe"></i> Websites</a></li>
        </ul>
    </div>

    <div class="nav-right">
        <div class="user-profile">
            <span class="branch-name">
                <i class="fa-solid fa-circle-user"></i> 
                <?php echo isset($_SESSION['user']['fullname']) ? $_SESSION['user']['fullname'] : 'Admin'; ?> 
                <i class="fa-solid fa-caret-down"></i>
            </span>
            <div class="user-dropdown">
    <a href="../../login-role/index.php" class="site-link">
    <i class="fa-solid fa-globe"></i> Xem website
</a>
    <a href="../../login-role/login/logout.php" class="logout-link">
        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
    </a>
</div>
            </div>
        </div>
    </div>
</nav>