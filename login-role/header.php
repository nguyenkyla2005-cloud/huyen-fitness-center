<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$isLogged = is_array($user);
$username = $user['username'] ?? 'Tài khoản';
$role = $user['role'] ?? '';

$currentUrl = $_SERVER['REQUEST_URI'] ?? '/BTL/login-role/index.php';
?>

<header class="header">
  <div class="container header-flex">
    <div class="logo">
      <span class="logo-123">HUYỀN</span><span class="logo-gym">FITNESS</span>
    </div>

    <nav class="menu">
      <a href="/BTL/login-role/index.php">Trang chủ</a>
      <a href="/BTL/login-role/services/services.php">Dịch vụ</a>
      <a href="/BTL/login-role/schedule/schedule.php">Lịch tập</a>
      <a href="/BTL/login-role/trainer/trainer.php">Huấn luyện viên</a>
      <a href="/BTL/login-role/news/news.php">Tin tức</a>
      <a href="/BTL/login-role/tapthu/tapthu.php">Tập thử</a>
    </nav>

    <!-- LOGIN / USER MENU -->
    <div class="menu-auth">
      <?php if (!$isLogged): ?>
        <a class="auth-btn"
           href="login/login.php?redirect=<?= urlencode($currentUrl) ?>">
          Đăng nhập
        </a>
      <?php else: ?>
        <div class="auth-user" id="authUser">
          <button type="button" class="auth-toggle" id="authToggle" aria-haspopup="true" aria-expanded="false">
            <span class="auth-name"><?= htmlspecialchars($username) ?></span>
            <span class="auth-caret">▾</span>
          </button>

          <div class="auth-dropdown" id="authDropdown">
            <a href="/BTL/login-role/login/change-password.php">Đổi mật khẩu</a>

            <?php if ($role === 'admin'): ?>
              <a href="../admin/qlkh/admin.php">Quản trị</a>
            <?php endif; ?>

            <a class="danger" href="/BTL/login-role/login/logout.php">Đăng xuất</a>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</header>

<script>
(function(){
  const authUser = document.getElementById('authUser');
  const toggle = document.getElementById('authToggle');
  const dropdown = document.getElementById('authDropdown');

  if(!authUser || !toggle || !dropdown) return;

  toggle.addEventListener('click', function(e){
    e.stopPropagation();
    const isOpen = authUser.classList.toggle('open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  document.addEventListener('click', function(){
    authUser.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
  });

  dropdown.addEventListener('click', function(e){
    e.stopPropagation();
  });

  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      authUser.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
})();
</script>
