<?php
require_once __DIR__ . '/../includes/header.php';
$errors = [];
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    } else {
        $user = findUserByEmailOrUsername($email);
        if (!$user) {
            $errors[] = 'Nếu email tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.'; // generic
        } else {
            $token = bin2hex(random_bytes(20));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            createPasswordResetToken($user['id'], $token, $expires);
            $link = sprintf('http://%s/weblaptop/auth/password_reset.php?token=%s', $_SERVER['HTTP_HOST'], urlencode($token));
            // In dev: show link
            set_flash('success', 'Kiểm tra email để lấy link đặt lại mật khẩu. (Link thử nghiệm: <a href="'.htmlspecialchars($link).'">Đặt lại mật khẩu</a>)');
        }
    }
    if (!empty($errors)) set_flash('error', implode('<br>', $errors));
}
?>
<div class="row justify-content-center my-5">
  <div class="col-md-6">
    <div class="card p-4">
      <h4>Quên mật khẩu</h4>
      <form method="post">
        <div class="mb-2"><label class="form-label">Nhập email đã đăng ký</label><input class="form-control" name="email" type="email" required></div>
        <button class="btn btn-primary" type="submit">Gửi hướng dẫn</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>