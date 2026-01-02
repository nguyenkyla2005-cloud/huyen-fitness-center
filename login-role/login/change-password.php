<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $oldPass = trim($_POST['old_password'] ?? "");
  $newPass = trim($_POST['new_password'] ?? "");
  $confirm = trim($_POST['confirm_password'] ?? "");

  $username = $_SESSION['user']['username'] ?? "";

  if ($oldPass === "" || $newPass === "" || $confirm === "") {
    $error = "Vui lòng nhập đầy đủ thông tin!";
  } elseif (strlen($newPass) < 6) {
    $error = "Mật khẩu mới phải ít nhất 6 ký tự!";
  } elseif ($newPass !== $confirm) {
    $error = "Xác nhận mật khẩu không khớp!";
  } else {
    $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE username=? AND password=? LIMIT 1");
    if (!$stmt) {
      $error = "Lỗi hệ thống. Vui lòng thử lại!";
    } else {
      mysqli_stmt_bind_param($stmt, "sss", $newPass, $username, $oldPass);
      mysqli_stmt_execute($stmt);

      if (mysqli_stmt_affected_rows($stmt) === 1) {
        $success = "Đổi mật khẩu thành công!";
      } else {
        $error = "Mật khẩu cũ không đúng!";
      }
      mysqli_stmt_close($stmt);
    }
  }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đổi mật khẩu</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="auth-bg"></div>
<div class="auth-overlay"></div>

<main class="auth-wrap">
  <section class="auth-card" style="grid-template-columns:1fr;">
    <div class="auth-right">
      <h1>Đổi mật khẩu</h1>
      <p class="sub">Cập nhật mật khẩu cho tài khoản của bạn</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert" style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.22);color:#166534;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="form">
        <label class="label">Mật khẩu cũ</label>
        <input class="input" type="password" name="old_password" required>

        <label class="label">Mật khẩu mới</label>
        <input class="input" type="password" name="new_password" required>

        <label class="label">Xác nhận mật khẩu mới</label>
        <input class="input" type="password" name="confirm_password" required>

        <button class="btn" type="submit">CẬP NHẬT</button>

        <div style="margin-top:12px;">
          <a class="link" href="/BTL/login-role/index.php">← Về trang chủ</a>
        </div>
      </form>
    </div>
  </section>
</main>
</body>
</html>
