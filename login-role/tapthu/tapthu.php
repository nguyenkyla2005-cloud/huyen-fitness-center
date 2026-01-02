<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ KẾT NỐI DB
require_once '../db.php'; // sửa lại đường dẫn nếu khác

// ✅ PHÂN QUYỀN
$role = $_SESSION['user']['role'] ?? '';
$isLogged = isset($_SESSION['user']);

if (!$isLogged) {
  // Guest => đi login kèm redirect
  $redirect = urlencode("/BTL/login-role/tapthu/tapthu.php");
  header("Location: ../login/login.php?redirect={$redirect}");
  exit;
}

if ($role === 'admin') {
  // Admin không đăng ký tập thử => chuyển sang trang quản trị danh sách đăng ký
  // ✅ ĐỔI path này đúng với project admin của bạn
  header("Location: ../../admin/websites/trials.php");
  exit;
}

if ($role !== 'user') {
  // role khác (nếu có) => cho về trang chủ
  header("Location: ../index.php");
  exit;
}

$showSuccess = false;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $fullname = trim($_POST["fullname"] ?? "");
  $email    = trim($_POST["email"] ?? "");
  $phone    = trim($_POST["phone"] ?? "");

  // Chuẩn hoá để kiểm tra trùng (tránh khác format: "090 123" vs "090123")
  $email_norm = strtolower(trim($email));
  $phone_norm = preg_replace('/\D+/', '', $phone);

  if ($fullname === "") $errors[] = "Vui lòng nhập họ và tên.";
  if ($phone === "") $errors[] = "Vui lòng nhập số điện thoại.";
  if ($phone !== "" && $phone_norm === "") $errors[] = "Số điện thoại không hợp lệ.";

  // Email không bắt buộc, nhưng nếu nhập thì kiểm tra hợp lệ
  if ($email_norm !== "" && !filter_var($email_norm, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email không hợp lệ.";
  }

  // Check trùng: mỗi email và SĐT chỉ được đăng ký 1 lần
  if (empty($errors)) {
    $phoneExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'.',''),'(',''),')',''),'+','')";
    if ($email_norm !== "") {
      $chk = mysqli_prepare($conn, "SELECT id FROM trial_registrations WHERE {$phoneExpr}=? OR (email IS NOT NULL AND email<>'' AND LOWER(email)=?) LIMIT 1");
      if ($chk) {
        mysqli_stmt_bind_param($chk, "ss", $phone_norm, $email_norm);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
          $errors[] = "Email hoặc SĐT này đã đăng ký tập thử trước đó. Mỗi email/SĐT chỉ được đăng ký 1 lần.";
        }
        mysqli_stmt_close($chk);
      }
    } else {
      $chk = mysqli_prepare($conn, "SELECT id FROM trial_registrations WHERE {$phoneExpr}=? LIMIT 1");
      if ($chk) {
        mysqli_stmt_bind_param($chk, "s", $phone_norm);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);
        if (mysqli_stmt_num_rows($chk) > 0) {
          $errors[] = "SĐT này đã đăng ký tập thử trước đó. Mỗi SĐT chỉ được đăng ký 1 lần.";
        }
        mysqli_stmt_close($chk);
      }
    }
  }

  // ✅ LƯU DB nếu không có lỗi
  if (empty($errors)) {
    $userId = $_SESSION['user']['id'] ?? null; // nếu users table có cột id

    // Cho phép user_id NULL
    if ($userId === null) {
      $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO trial_registrations (user_id, fullname, email, phone, created_at)
         VALUES (NULL, ?, NULLIF(?, ''), ?, NOW())"
      );
      if (!$stmt) {
        $errors[] = "Lỗi hệ thống (prepare). Vui lòng thử lại!";
      } else {
        mysqli_stmt_bind_param($stmt, "sss", $fullname, $email_norm, $phone_norm);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) {
          $showSuccess = true;
          $_POST = [];
        } else {
          if (mysqli_errno($conn) == 1062) {
            $errors[] = "Email hoặc SĐT này đã đăng ký tập thử trước đó. Mỗi email/SĐT chỉ được đăng ký 1 lần.";
          } else {
            $errors[] = "Không thể lưu đăng ký. Vui lòng thử lại!";
          }
        }
      }
    } else {
      $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO trial_registrations (user_id, fullname, email, phone, created_at)
         VALUES (?, ?, NULLIF(?, ''), ?, NOW())"
      );
      if (!$stmt) {
        $errors[] = "Lỗi hệ thống (prepare). Vui lòng thử lại!";
      } else {
        mysqli_stmt_bind_param($stmt, "isss", $userId, $fullname, $email_norm, $phone_norm);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) {
          $showSuccess = true;
          $_POST = [];
        } else {
          if (mysqli_errno($conn) == 1062) {
            $errors[] = "Email hoặc SĐT này đã đăng ký tập thử trước đó. Mỗi email/SĐT chỉ được đăng ký 1 lần.";
          } else {
            $errors[] = "Không thể lưu đăng ký. Vui lòng thử lại!";
          }
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tập thử - Huyền Fitness</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include '../header.php'; ?>

<!-- BANNER -->
<section class="page-hero trial-hero">
  <div class="page-hero__overlay"></div>
  <div class="container page-hero__content">
    <h1>Tập thử</h1>
    <div class="breadcrumb-pill">
      <a href="../index.php">Home</a>
      <span>/</span>
      <span>Tập thử</span>
    </div>
  </div>
</section>

<!-- CONTENT -->
<main class="trial-page">
  <div class="container">
    <h2 class="trial-title">ĐĂNG KÝ TẬP THỬ</h2>

    <div class="trial-grid">
      <!-- FORM -->
      <section class="trial-card">
        <h3>Thông tin của bạn:</h3>

        <!-- ✅ HIỂN THỊ LỖI -->
        <?php if (!empty($errors)): ?>
          <div class="trial-errors" style="margin-bottom:12px; padding:10px 12px; border-radius:10px; background:#fff1f1; border:1px solid #ffd0d0; color:#8a1f1f;">
            <ul style="margin:0; padding-left:18px;">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form class="trial-form" method="post" action="">
          <label>Họ và tên *</label>
          <input type="text" name="fullname" placeholder="Full Name" required
                 value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">

          <label>Email</label>
          <input type="email" name="email" placeholder="Email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

          <label>Số điện thoại *</label>
          <input type="text" name="phone" placeholder="Phone" required
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

          <button type="submit">ĐĂNG KÝ TẬP THỬ</button>
        </form>
      </section>

      <!-- ĐỊA CHỈ -->
      <aside class="trial-info">
        <p><b>Huyền Fitness Center </b>Số 24 Thượng Đức Nam Đồ Sơn Hải Phòng</p>
        <p><b>Hotline:</b> 097 696 36 89</p>
        <p><b>Giờ mở cửa:</b> Thứ 2–7: 05:00–21:00 & CN: 15:00-19:00</p>
      </aside>
    </div>
  </div>
</main>

<!-- ✅ MODAL THÀNH CÔNG -->
<?php if ($showSuccess): ?>
  <div class="modal-backdrop" id="successModal">
    <div class="modal">
      <div class="modal-icon">✓</div>
      <h3>Đăng ký thành công!</h3>
      <p>Chúng tôi đã nhận thông tin của bạn. Huyền Fitness sẽ liên hệ sớm nhất.</p>

      <div class="modal-actions">
        <a class="btn-secondary" href="tapthu.php">Đóng</a>
        <a class="btn-primary" href="../index.php">Về trang chủ</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include '../footer.php'; ?>
</body>
</html>
