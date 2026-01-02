<?php
session_start();
require '../db.php';

// Lấy redirect từ GET/POST để login xong quay lại đúng trang
$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '../index.php');

// Chặn open-redirect: chỉ cho phép đường dẫn nội bộ (tương đối)
if (preg_match('#^https?://#i', $redirect)) {
    $redirect = '../index.php';
}


// Nếu đã đăng nhập thì tự chuyển hướng
if (isset($_SESSION['user'])) {
    if (!empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
        header("Location: ../../admin/qlkh/admin.php");
    } else {
        header("Location: " . $redirect);
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($username === "" || $password === "") {
        $error = "Vui lòng nhập đầy đủ tài khoản và mật khẩu!";
    } else {
        // Giữ đúng kiểu đăng nhập hiện tại (password lưu plain text)
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? AND password = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);
                $_SESSION['user'] = $user;

               if (!empty($user['role']) && $user['role'] === 'admin') {
               header("Location: ../../admin/qlkh/admin.php");
              } else {
              header("Location: " . $redirect);
              }
            exit;
            } else {
                $error = "Sai tài khoản hoặc mật khẩu!";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Lỗi hệ thống. Vui lòng thử lại!";
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập - Huyền Fitness</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="auth-bg"></div>
<div class="auth-overlay"></div>

<main class="auth-wrap">
  <section class="auth-card">

    <!-- LEFT (Logo + giới thiệu) -->
    <div class="auth-left">
      <div class="brand">
        <img class="brand-logo" src="images/logo.png" alt="Huyền Fitness"
             onerror="this.style.display='none';">
        <div class="brand-text">
          <div class="brand-name"><span>HUYỀN</span>FITNESS</div>
          <div class="brand-tagline">Hệ thống Fitness chuyên nghiệp</div>
        </div>
      </div>

      <div class="auth-left-content">
        <h2>Chào mừng bạn trở lại!</h2>
        <p>Đăng nhập để đăng ký tập thử.</p>
        <ul class="auth-points">
          <li>✅ Phòng tập hiện đại</li>
          <li>✅ HLV chuyên nghiệp</li>
          <li>✅ GYM • YOGA • AEROBIC</li>
        </ul>
      </div>
    </div>

    <!-- RIGHT (Form) -->
    <div class="auth-right">
      <h1>Đăng nhập</h1>
      <p class="sub">Nhập thông tin để tiếp tục</p>
     <form method="post" class="form" autocomplete="on">
        <label class="label">Tên đăng nhập</label>
        <input type="text" name="username" class="input" placeholder="Tên đăng nhập" required>

        <label class="label">Mật khẩu</label>
        <input type="password" name="password" class="input" placeholder="Mật khẩu" required>

        <div class="row">
          <label class="checkbox">
            <input type="checkbox" name="remember">
            <span>Ghi nhớ đăng nhập</span>
          </label>

          <!-- Link quên mật khẩu -->
          <a class="link" href="forgot-password.php">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn">ĐĂNG NHẬP</button>

        <div class="divider"><span>hoặc</span></div>

        <!-- Link đăng ký -->
        <a class="btn btn-ghost" href="register.php">ĐĂNG KÝ TÀI KHOẢN</a>
        <p class="mini">
          Về trang chủ? <a class="link" href="../index.php">Quay lại</a>
        </p>
      </form>
    </div>

  </section>
</main>

</body>
</html>
