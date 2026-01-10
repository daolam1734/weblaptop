<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../functions.php';

$errors = [];
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);

    // basic validation
    if ($full_name === '') $errors[] = 'Họ và tên bắt buộc.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (!preg_match('/^0[0-9]{9,10}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ.';
    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) $errors[] = 'Mật khẩu cần ít nhất 8 ký tự, bao gồm chữ và số.';
    if ($password !== $confirm) $errors[] = 'Mật khẩu xác nhận không khớp.';
    if (!$agree) $errors[] = 'Bạn cần đồng ý Điều khoản & Chính sách.';

    // existing email
    if (findUserByEmailOrUsername($email)) $errors[] = 'Email đã được sử dụng.';

    if (empty($errors)) {
        $userdata = [
            'username' => $username ?: strstr($email, '@', true),
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'phone' => $phone
        ];
        $uid = createUser($userdata);
        if ($uid) {
            // create verification token
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+2 day'));
            setEmailVerificationToken($uid, $token, $expires);
            $link = sendVerificationEmailSimulated($email, $token);

            // Welcome Notification
            createNotification(
                $uid, 
                "Chào mừng bạn đến với Growtech", 
                "Cảm ơn bạn đã tin tưởng và đăng ký tài khoản. Hãy bắt đầu mua sắm ngay hôm nay!", 
                'system'
            );

            set_flash('success', "Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản. (Link test: <a href='".htmlspecialchars($link)."' target='_blank'>Xác thực</a>)");
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Đăng ký thất bại, thử lại sau.';
        }
    }
    if (!empty($errors)) set_flash('error', implode('<br>', $errors));
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center align-items-center" style="min-height: 60vh; padding: 20px 0;">
  <div class="col-md-6 col-lg-5">
    <div class="card auth-card shadow-lg border-0 rounded-4">
      <div class="card-body p-4 p-md-5">
        <h4 class="text-center fw-bold mb-4 text-danger">Tạo tài khoản</h4>
        <?php if (!empty($errors)): ?>
             <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
              <label class="form-label text-muted">Họ và tên</label>
              <input class="form-control bg-light" name="full_name" required>
          </div>
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label text-muted">Email</label>
                  <input class="form-control bg-light" name="email" type="email" required>
              </div>
              <div class="col-md-6 mb-3">
                   <label class="form-label text-muted">Số điện thoại</label>
                   <input class="form-control bg-light" name="phone" required>
              </div>
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Tên đăng nhập <small>(Tùy chọn)</small></label>
              <input class="form-control bg-light" name="username">
          </div>
          <div class="mb-3">
              <label class="form-label text-muted">Mật khẩu</label>
              <input class="form-control bg-light" name="password" type="password" required>
              <div class="form-text small">Tối thiểu 8 ký tự, bao gồm chữ và số.</div>
          </div>
          <div class="mb-3">
               <label class="form-label text-muted">Xác nhận mật khẩu</label>
               <input class="form-control bg-light" name="confirm_password" type="password" required>
          </div>
          
          <div class="mb-4 form-check">
              <input class="form-check-input" id="agree" name="agree" type="checkbox">
              <label class="form-check-label small text-secondary" for="agree">
                  Tôi đồng ý với <a href="/weblaptop/terms.php" class="text-decoration-none fw-bold text-danger">Điều khoản & Chính sách</a>
              </label>
          </div>
          
          <button class="btn btn-danger btn-lg w-100 fw-bold mb-3" type="submit">Đăng ký</button>
        </form>
        
        <div class="text-center text-muted small">
            Đã có tài khoản? <a href="/weblaptop/auth/login.php" class="text-decoration-none fw-bold text-danger">Đăng nhập</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>