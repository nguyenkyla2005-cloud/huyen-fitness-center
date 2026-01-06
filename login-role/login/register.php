<?php
session_start();
require '../db.php';

$error = "";
$success = "";

function getRoleLikeUser1($conn) {
    $defaultRole = "user";
    $stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE username = ? LIMIT 1");
    $u = "user1";
    mysqli_stmt_bind_param($stmt, "s", $u);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if (!empty($row['role'])) return $row['role'];
    return $defaultRole;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? "");
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? "");
    $confirm  = trim($_POST['confirm_password'] ?? "");

    if ($username === "" || $password === "" || $confirm === "") {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (strlen($username) < 3) {
        $error = "Tên đăng nhập phải ít nhất 3 ký tự!";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải ít nhất 6 ký tự!";
    } elseif ($password !== $confirm) {
        $error = "Xác nhận mật khẩu không khớp!";
    } else {
        // kiểm tra username đã tồn tại chưa
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $exists = $res && mysqli_num_rows($res) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác!";
        } else {
            $role = getRoleLikeUser1($conn); // quyền giống user1

            // insert (GIỮ password plain text để login hiện tại dùng được)
            $stmt2 = mysqli_prepare(
    $conn,
    "INSERT INTO users (username, email, password, role)
     VALUES (?, ?, ?, ?)"
);
            mysqli_stmt_bind_param(
    $stmt2,
    "ssss",
    $username,
    $email,
    $plainPassword,
    $role
);
            $ok = mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            if ($ok) {
                $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
            } else {
                $error = "Không thể tạo tài khoản. Vui lòng thử lại!";
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
  <title>Đăng ký - Huyền Fitness</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="auth-bg"></div>
<div class="auth-overlay"></div>

<main class="auth-wrap">
  <section class="auth-card" style="grid-template-columns:1fr;">
    <div class="auth-right">
      <h1>Đăng ký</h1>
      <p class="sub">Tạo tài khoản mới</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert" style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.22);color:#166534;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="form">
         <label class="label">Email</label>
        <input class="input" type="email" name="email" placeholder="vd: abc@gmail.com" required>
        <label class="label">Tên đăng nhập</label>
        <input class="input" type="text" name="username" placeholder="vd: tungdt" required>

        <label class="label">Mật khẩu</label>
        <input class="input" type="password" name="password" placeholder="Ít nhất 6 ký tự" required>

        <label class="label">Xác nhận mật khẩu</label>
        <input class="input" type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>

        <button class="btn" type="submit">TẠO TÀI KHOẢN</button>

        <div style="margin-top:12px;">
          <a class="link" href="login.php">← Quay lại đăng nhập</a>
        </div>
      </form>
    </div>
  </section>
</main>

</body>
</html>
