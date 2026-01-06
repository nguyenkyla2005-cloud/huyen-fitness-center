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

    return !empty($row['role']) ? $row['role'] : $defaultRole;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? "");
    $email    = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");
    $confirm  = trim($_POST['confirm_password'] ?? "");

    if ($username === "" || $email === "" || $password === "" || $confirm === "") {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } elseif (strlen($username) < 3) {
        $error = "Tên đăng nhập phải ít nhất 3 ký tự!";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải ít nhất 6 ký tự!";
    } elseif ($password !== $confirm) {
        $error = "Xác nhận mật khẩu không khớp!";
    } else {
        // kiểm tra username tồn tại
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $exists = $res && mysqli_num_rows($res) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = "Tên đăng nhập đã tồn tại!";
        } else {
            $role = getRoleLikeUser1($conn);

            // INSERT USER
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
                $password,   // ← FIX QUAN TRỌNG
                $role
            );

            if (mysqli_stmt_execute($stmt2)) {
                $success = "Đăng ký thành công! Bạn có thể đăng nhập.";
            } else {
                $error = "Không thể tạo tài khoản!";
            }
            mysqli_stmt_close($stmt2);
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký - Huyền Fitness</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>

<main class="auth-wrap">
  <section class="auth-card" style="grid-template-columns:1fr;">
    <div class="auth-right">
      <h1>Đăng ký</h1>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="post" class="form">
        <label>Email</label>
        <input class="input" type="email" name="email" required>

        <label>Tên đăng nhập</label>
        <input class="input" type="text" name="username" required>

        <label>Mật khẩu</label>
        <input class="input" type="password" name="password" required>

        <label>Xác nhận mật khẩu</label>
        <input class="input" type="password" name="confirm_password" required>

        <button class="btn" type="submit">TẠO TÀI KHOẢN</button>
        <a class="link" href="login.php">← Quay lại đăng nhập</a>
      </form>
    </div>
  </section>
</main>

</body>
</html>
