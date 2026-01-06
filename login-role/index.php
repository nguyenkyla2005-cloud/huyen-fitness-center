<?php
require 'db.php';

$showSuccess = false;
$trialErrors = [];

function hf_base_url(): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $pos = strpos($script, '/BTL/');
    if ($pos !== false) return substr($script, 0, $pos + 4);
    return '';
}

function hf_asset_url(string $path): string {
    $path = trim($path);
    if ($path === '') return '';
    if (preg_match('#^(https?:)?//#i', $path)) return $path;
    if (substr($path, 0, 1) === '/') return $path;
    if (strpos($path, 'uploads/') === 0) {
        $base = rtrim(hf_base_url(), '/');
        return ($base !== '' ? $base : '') . '/' . $path;
    }
    return $path;
}

function hf_table_exists(mysqli $conn, string $table): bool {
    $t = mysqli_real_escape_string($conn, $table);
    $rs = @mysqli_query($conn, "SHOW TABLES LIKE '{$t}'");
    if (!$rs) return false;
    $ok = mysqli_num_rows($rs) > 0;
    mysqli_free_result($rs);
    return $ok;
}

// Lấy gallery thêm từ DB (giữ 9 ảnh cũ + thêm ảnh mới)
$gallery_extra = [];
if (isset($conn) && $conn && hf_table_exists($conn, 'site_gallery')) {
    $rs = @mysqli_query($conn, "SELECT image,caption FROM site_gallery WHERE is_active=1 ORDER BY id DESC");
    if ($rs) {
        while ($row = mysqli_fetch_assoc($rs)) $gallery_extra[] = $row;
        mysqli_free_result($rs);
    }
}

if (isset($_POST['register_trial'])) {

    // BẮT BUỘC đăng nhập role user mới được đăng ký
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'user') {
        // chuyển sang trang login + quay lại index sau khi login
        $redirect = urlencode("../index.php#trial");
        header("Location: login/login.php?redirect={$redirect}");
        exit;
    }

    $name  = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Chuẩn hoá để kiểm tra trùng (tránh khác format: "090 123" vs "090123")
    $email_norm = strtolower(trim($email));
    $phone_norm = preg_replace('/\D+/', '', $phone); // chỉ giữ số

    // Lưu DB (trial_registrations)
    $trialErrors = [];
    if ($name === '') $trialErrors[] = 'Vui lòng nhập họ tên.';
    if ($phone === '') $trialErrors[] = 'Vui lòng nhập số điện thoại.';
    if ($phone !== '' && $phone_norm === '') $trialErrors[] = 'Số điện thoại không hợp lệ.';
    if ($email_norm !== '' && !filter_var($email_norm, FILTER_VALIDATE_EMAIL)) $trialErrors[] = 'Email không hợp lệ.';

    // Check trùng: mỗi email và SĐT chỉ được đăng ký 1 lần
    if (empty($trialErrors) && isset($conn) && $conn) {
        $phoneExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'.',''),'(',''),')',''),'+','')";
        if ($email_norm !== '') {
            $chk = mysqli_prepare($conn, "SELECT id FROM trial_registrations WHERE {$phoneExpr}=? OR (email IS NOT NULL AND email<>'' AND LOWER(email)=?) LIMIT 1");
            if ($chk) {
                mysqli_stmt_bind_param($chk, 'ss', $phone_norm, $email_norm);
                mysqli_stmt_execute($chk);
                mysqli_stmt_store_result($chk);
                if (mysqli_stmt_num_rows($chk) > 0) {
                    $trialErrors[] = 'Email hoặc SĐT này đã đăng ký tập thử trước đó. Mỗi email/SĐT chỉ được đăng ký 1 lần.';
                }
                mysqli_stmt_close($chk);
            }
        } else {
            $chk = mysqli_prepare($conn, "SELECT id FROM trial_registrations WHERE {$phoneExpr}=? LIMIT 1");
            if ($chk) {
                mysqli_stmt_bind_param($chk, 's', $phone_norm);
                mysqli_stmt_execute($chk);
                mysqli_stmt_store_result($chk);
                if (mysqli_stmt_num_rows($chk) > 0) {
                    $trialErrors[] = 'SĐT này đã đăng ký tập thử trước đó. Mỗi SĐT chỉ được đăng ký 1 lần.';
                }
                mysqli_stmt_close($chk);
            }
        }
    }

    if (empty($trialErrors)) {
        $userId = $_SESSION['user']['id'] ?? null;

        if ($userId === null) {
            $stmt = mysqli_prepare($conn, "INSERT INTO trial_registrations (user_id, fullname, email, phone, created_at) VALUES (NULL, ?, NULLIF(?, ''), ?, NOW())");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sss', $name, $email_norm, $phone_norm);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $showSuccess = (bool)$ok;
                if (!$ok) {
                    // 1062 = duplicate key (khi unique index đã bật)
                    if (mysqli_errno($conn) == 1062) {
                        $trialErrors[] = 'Email hoặc SĐT này đã đăng ký tập thử trước đó. Mỗi email/SĐT chỉ được đăng ký 1 lần.';
                    } else {
                        $trialErrors[] = 'Không thể lưu đăng ký. Vui lòng thử lại.';
                    }
                }
            } else {
                $trialErrors[] = 'Lỗi hệ thống. Vui lòng thử lại.';
            }
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO trial_registrations (user_id, fullname, email, phone, created_at) VALUES (?, ?, NULLIF(?, ''), ?, NOW())");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'isss', $userId, $name, $email_norm, $phone_norm);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $showSuccess = (bool)$ok;
                if (!$ok) {
                    if (mysqli_errno($conn) == 1062) {
                        $trialErrors[] = 'Email hoặc SĐT này đã đăng ký tập thử trước đó. Mỗi email/SĐT chỉ được đăng ký 1 lần.';
                    } else {
                        $trialErrors[] = 'Không thể lưu đăng ký. Vui lòng thử lại.';
                    }
                }
            } else {
                $trialErrors[] = 'Lỗi hệ thống. Vui lòng thử lại.';
            }
        }
    }

}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Huyền Fitness Center</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'header.php'; ?>
<section class="hero-banner">
  <div class="hero-overlay"></div>

  <div class="hero-inner">
    <div class="hero-left">
      <p class="hero-brand"><span>HUYỀN</span>FITNESS</p>

      <h1 class="hero-title">HUYỀN FITNESS GYM</h1>
      <p class="hero-subtitle">HỆ THỐNG FITNESS CHUYÊN NGHIỆP</p>

      <div class="hero-actions">
        <a class="btn-primary" href="tapthu/tapthu.php">ĐĂNG KÝ TẬP THỬ →</a>
        <a class="btn-ghost" href="services/services.php">XEM DỊCH VỤ</a>
      </div>

      <div class="hero-badges">
        <span class="badge">1 NGÀY TẬP THỬ MIỄN PHÍ</span>
        <span class="badge badge-outline">GYM • YOGA • AEROBIC</span>
      </div>
    </div>
  </div>
</section>
<section class="trial">
  <div class="container">

    <div class="trial-head">
      <h2>ĐĂNG KÝ TẬP THỬ MIỄN PHÍ</h2>
      <span class="trial-underline"></span>
    </div>

    <div class="trial-flex">
      <!-- FORM -->
      <div class="trial-form">
        <h3>Thông tin của bạn:</h3>
        <?php if (!empty($trialErrors)): ?>
          <div style="margin:10px 0; padding:10px; border:1px solid #f5c2c7; background:#f8d7da; color:#842029; border-radius:8px;">
            <ul style="margin:0; padding-left:18px;">
              <?php foreach ($trialErrors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <?php $canRegisterTrial = (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'user'); ?>
       <form method="post" action="">
  <?php if (!$canRegisterTrial): ?>
    <div class="trial-login-note">
      Bạn cần <a href="login/login.php?redirect=<?= urlencode('../index.php#trial') ?>">Đăng nhập</a> để đăng ký tập thử.
    </div>
  <?php endif; ?>

  <label>Họ và tên *</label>
  <input type="text" name="fullname" placeholder="Full Name" required <?= !$canRegisterTrial ? 'disabled' : '' ?>>

  <label>Email</label>
  <input type="email" name="email" placeholder="Email" <?= !$canRegisterTrial ? 'disabled' : '' ?>>

  <label>Số điện thoại *</label>
  <input type="text" name="phone" placeholder="Phone" required <?= !$canRegisterTrial ? 'disabled' : '' ?>>

  <?php if ($canRegisterTrial): ?>
    <button type="submit" name="register_trial">ĐĂNG KÝ TẬP THỬ</button>
  <?php else: ?>
    <a class="trial-login-btn" href="login/login.php?redirect=<?= urlencode('../index.php#trial') ?>">ĐĂNG NHẬP</a>
  <?php endif; ?>
</form>

      </div>

      <!-- TEXT GIỚI THIỆU -->
      <div class="trial-text">
        <h4>Xin chào, chúng tôi là...</h4>

        <h2>
          HUYỀN FITNESS<br>
          GYM &amp; YOGA
        </h2>

        <p class="trial-sub">Hệ thống Trung tâm Thể thao cao cấp</p>
        <span class="trial-underline left"></span>

        <p class="trial-desc">
          Hệ thống Huyền Fitness gồm nhiều cơ sở, trang thiết bị hiện đại,
          huấn luyện viên chuyên nghiệp, dịch vụ chăm sóc khách hàng tận tâm.
        </p>

        <a href="gioi-thieu/gioithieu.php" class="trial-more">TÌM HIỂU THÊM <span>›</span></a>
      </div>
    </div>

  </div>
</section>

<section class="services">
    <div class="container services-flex">

        <!-- IMAGE -->
        <div class="services-img">
            <img src="images/service.jpg" alt="Gym Service">
        </div>

        <!-- CONTENT -->
        <div class="services-content">
            <h2>DỊCH VỤ NỔI BẬT</h2>

            <div class="service-item">
                <div class="icon">💪</div>
                <div>
                    <h4>GYM</h4>
                    <p>Hệ thống máy tập hiện đại, đa dạng bài tập</p>
                </div>
            </div>

            <div class="service-item">
                <div class="icon">🧘</div>
                <div>
                    <h4>YOGA</h4>
                    <p>Không gian thư giãn, huấn luyện viên chuyên nghiệp</p>
                </div>
            </div>

            <div class="service-item">
                <div class="icon">🔥</div>
                <div>
                    <h4>AEROBIC</h4>
                    <p>Các lớp tập nhóm sôi động, nhiều khung giờ</p>
                </div>
            </div>
        </div>

    </div>
</section>
<section class="why">
  <div class="container">
    <h2 class="why-title">TẠI SAO NÊN CHỌN HUYỀN FITNESS?</h2>
    <span class="why-underline"></span>

    <div class="why-grid">
      <div class="why-item">
        <div class="why-icon">🏢</div>
        <p>Hệ thống phòng tập cao cấp<br>bậc nhất Hải Phòng</p>
      </div>

      <div class="why-item">
        <div class="why-icon">⭐</div>
        <p>Đa dạng bộ môn tập luyện:<br>Gym, Yoga, Aerobic...</p>
      </div>

      <div class="why-item">
        <div class="why-icon">👤</div>
        <p>Đội ngũ HLV chuyên nghiệp,<br>được đào tạo chuẩn</p>
      </div>

      <div class="why-item">
        <div class="why-icon">👍</div>
        <p>Tiện ích tuyệt vời: <br>tắm nóng/lạnh, tủ đồ...</p>
      </div>

      <div class="why-item">
        <div class="why-icon">👥</div>
        <p>Hoạt động cộng đồng hấp dẫn:<br>Yoga , Aerobic Dance...</p>
      </div>
      <div class="why-item">
        <div class="why-icon">👥</div>
        <p>CHào mừng:<br>Yoga , Aerobic Dance...</p>
      </div>
    </div>
  </div>
</section>

<section class="trainer">
    <div class="container">
        <h2 class="section-title">HUẤN LUYỆN VIÊN</h2>

        <div class="trainer-grid">
            <div class="trainer-card">
                <img src="images/trainer1.jpg">
                <h4>Nguyễn Chí Công</h4>
                <p>PT Gym</p>
            </div>

            <div class="trainer-card">
                <img src="images/trainer2.png">
                <h4>Tô Thị Huyền</h4>
                <p>PT GYM</p>
            </div>
        </div>
    </div>
</section>
<section class="news">
    <div class="container">
        <h2 class="section-title">TIN TỨC & KIẾN THỨC</h2>

       <div class="news-grid" id="news">

  <a class="news-card" href="experience/post.php?slug=5-bai-tap-giam-mo">
    <img src="images/news1.jpg" alt="5 bài tập giảm mỡ hiệu quả">
    <h4>5 bài tập giảm mỡ hiệu quả</h4>
    <p>Giúp bạn đốt mỡ nhanh, vóc dáng săn chắc.</p>
  </a>

  <a class="news-card" href="experience/post.php?slug=loi-ich-khi-tap-yoga">
    <img src="images/news2.jpg" alt="Lợi ích khi tập Yoga">
    <h4>Lợi ích khi tập Yoga</h4>
    <p>Giữ cơ thể dẻo dai và tinh thần thoải mái.</p>
  </a>

  <a class="news-card" href="experience/post.php?slug=lich-tap-cho-nguoi-moi">
    <img src="images/news3.jpg" alt="Lịch tập cho người mới">
    <h4>Lịch tập cho người mới</h4>
    <p>Hướng dẫn tập luyện khoa học và an toàn.</p>
  </a>

</div>

    </div>
</section>
<section class="gallery">
    <div class="container">
        <h2 class="section-title">HÌNH ẢNH PHÒNG TẬP</h2>

        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="images/gym1.jpg" alt="Phòng tập Gym">
            </div>
            <div class="gallery-item">
                <img src="images/gym2.jpg" alt="Khu tập luyện">
            </div>
            <div class="gallery-item">
                <img src="images/gym3.jpg" alt="Yoga Studio">
            </div>
            <div class="gallery-item">
                <img src="images/gym4.jpg" alt="Máy tập hiện đại">
            </div>
            <div class="gallery-item">
                <img src="images/gym5.jpg" alt="Không gian phòng tập">
            </div>
            <div class="gallery-item">
                <img src="images/gym6.jpg" alt="Huấn luyện viên">
            </div>
            <div class="gallery-item">
                <img src="images/gym7.jpg" alt="Huấn luyện viên">
            </div>
            <div class="gallery-item">
                <img src="images/gym8.jpg" alt="Huấn luyện viên">
            </div>
            <div class="gallery-item">
                <img src="images/gym9.jpg" alt="Huấn luyện viên">
            </div>

            <?php foreach ($gallery_extra as $g): ?>
              <div class="gallery-item">
                <img src="<?= htmlspecialchars(hf_asset_url($g['image'] ?? '')) ?>" alt="<?= htmlspecialchars($g['caption'] ?? 'Huyền Fitness') ?>">
              </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>
<?php if (!empty($showSuccess)): ?>
<div class="trial-modal-backdrop" id="trialSuccessModal">
  <div class="trial-modal">
    <div class="trial-modal-icon">✓</div>
    <h3>Đăng ký thành công!</h3>
    <p>Huyền Fitness Center đã nhận thông tin của bạn và sẽ liên hệ sớm nhất.</p>
    <button class="trial-modal-close" type="button" onclick="document.getElementById('trialSuccessModal').style.display='none'">
      Đóng
    </button>
  </div>
</div>
<?php endif; ?>
</body>
</html>
