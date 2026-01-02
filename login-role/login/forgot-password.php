<?php
session_start();
require '../db.php';

$error = "";
$success = "";



function clearResetSession() {
    unset($_SESSION['reset_user'], $_SESSION['reset_otp'], $_SESSION['reset_exp']);
}

// Cho phép bấm "Hủy" để làm lại từ đầu
if (isset($_GET['clear'])) {
    clearResetSession();
    header("Location: forgot-password.php");
    exit;
}

$step = 1;

// Nếu đang có OTP còn hạn thì vào bước 2
if (!empty($_SESSION['reset_user']) && !empty($_SESSION['reset_otp']) && !empty($_SESSION['reset_exp'])) {
    if (time() <= $_SESSION['reset_exp']) {
        $step = 2;
    } else {
        clearResetSession();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // BƯỚC 1: Gửi mã OTP
    if (isset($_POST['send_code'])) {
        $username = trim($_POST['username'] ?? "");

        if ($username === "") {
            $error = "Vui lòng nhập tên đăng nhập!";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
            if (!$stmt) {
                $error = "Lỗi hệ thống. Vui lòng thử lại!";
            } else {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) === 1) {
                    $otp = random_int(100000, 999999);
                    $_SESSION['reset_user'] = $username;
                    $_SESSION['reset_otp']  = (string)$otp;
                    $_SESSION['reset_exp']  = time() + 600; // 10 phút

                    $step = 2;

                    // DEMO: hiển thị OTP
                    $success = "Mã xác minh của bạn là: <b>$otp</b> (hết hạn sau 10 phút).";
                } else {
                    $error = "Tên đăng nhập không tồn tại!";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    // BƯỚC 2: Xác minh OTP + đổi mật khẩu
    if (isset($_POST['reset_password'])) {
        $username = trim($_POST['username'] ?? "");
        $otpInput = trim($_POST['otp'] ?? "");
        $newPass  = trim($_POST['new_password'] ?? "");
        $confirm  = trim($_POST['confirm_password'] ?? "");

        if ($username === "" || $otpInput === "" || $newPass === "" || $confirm === "") {
            $error = "Vui lòng nhập đầy đủ thông tin!";
        } elseif (strlen($newPass) < 6) {
            $error = "Mật khẩu mới phải ít nhất 6 ký tự!";
        } elseif ($newPass !== $confirm) {
            $error = "Xác nhận mật khẩu không khớp!";
        } elseif (empty($_SESSION['reset_user']) || empty($_SESSION['reset_otp']) || empty($_SESSION['reset_exp'])) {
            $error = "Phiên đặt lại mật khẩu không hợp lệ. Vui lòng gửi mã lại.";
            $step = 1;
        } elseif (time() > $_SESSION['reset_exp']) {
            $error = "Mã xác minh đã hết hạn. Vui lòng gửi mã lại.";
            clearResetSession();
            $step = 1;
        } elseif ($username !== $_SESSION['reset_user']) {
            $error = "Username không khớp với phiên đặt lại. Vui lòng làm lại.";
            clearResetSession();
            $step = 1;
        } elseif ($otpInput !== $_SESSION['reset_otp']) {
            $error = "Mã xác minh không đúng!";
            $step = 2;
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ? LIMIT 1");
            if (!$stmt) {
                $error = "Lỗi hệ thống. Vui lòng thử lại!";
                $step = 2;
            } else {
                mysqli_stmt_bind_param($stmt, "ss", $newPass, $username);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) === 1) {
                    $success = "Đặt lại mật khẩu thành công! Bạn có thể đăng nhập lại.";
                    clearResetSession();
                    $step = 1;
                } else {
                    $error = "Không thể cập nhật mật khẩu. Vui lòng thử lại!";
                    $step = 2;
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quên mật khẩu - Huyền Fitness</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="auth-bg"></div>
<div class="auth-overlay"></div>

<main class="auth-wrap">
  <section class="auth-card" style="grid-template-columns:1fr;">
    <div class="auth-right">
      <h1>Quên mật khẩu</h1>
      <p class="sub">Nhập tài khoản để nhận mã xác minh và đặt lại mật khẩu</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert" style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.22);color:#166534;">
          <?= $success /* có HTML <b> */ ?>
        </div>
      <?php endif; ?>

      <?php if ($step === 1): ?>
        <!-- STEP 1 -->
        <form method="post" class="form">
          <label class="label">Tên đăng nhập</label>
          <input class="input" type="text" name="username" placeholder="vd: user1" required>
          <button class="btn" type="submit" name="send_code">GỬI MÃ XÁC MINH</button>

          <div style="margin-top:12px;">
            <a class="link" href="login.php">← Quay lại đăng nhập</a>
          </div>
        </form>

      <?php else: ?>
        <!-- STEP 2 -->
        <form method="post" class="form">
          <label class="label">Tên đăng nhập</label>
          <input class="input" type="text" name="username" value="<?= htmlspecialchars($_SESSION['reset_user'] ?? '') ?>" readonly>

          <label class="label">Mã xác minh (OTP)</label>
          <input class="input" type="text" name="otp" placeholder="Nhập mã 6 số" required>

          <label class="label">Mật khẩu mới</label>
          <input class="input" type="password" name="new_password" placeholder="Ít nhất 6 ký tự" required>

          <label class="label">Xác nhận mật khẩu mới</label>
          <input class="input" type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>

          <button class="btn" type="submit" name="reset_password">CẬP NHẬT MẬT KHẨU</button>

          <div style="margin-top:12px; display:flex; gap:14px; align-items:center; flex-wrap:wrap;">
            <a class="link" href="forgot-password.php?clear=1">↻ Gửi mã lại / Làm lại</a>
            <a class="link" href="login.php">← Quay lại đăng nhập</a>
          </div>
        </form>
      <?php endif; ?>

    </div>
  </section>
</main>

</body>
</html>
