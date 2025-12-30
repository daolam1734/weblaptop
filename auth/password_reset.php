<?php
require_once __DIR__ . '/../includes/header.php';
$token = $_GET['token'] ?? '';
$reset = $token ? verifyPasswordResetToken($token) : null;
$errors = [];
$success = null;
if (!$reset) {
    $errors[] = 'Liên kết không hợp lệ hoặc đã hết hạn.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($errors)) set_flash('error', implode('<br>', $errors));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) $errors[] = 'Mật khẩu cần ít nhất 8 ký tự, bao gồm chữ và số.';
    if ($password !== $confirm) $errors[] = 'Mật khẩu xác nhận không khớp.';
    if (empty($errors)) {
        resetUserPassword($reset['user_id'], $password);
        markPasswordResetUsed($reset['id']);
        set_flash('success', 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay bây giờ.');
    }
}
?>
<div class="row justify-content-center my-5">
  <div class="col-md-6">
    <div class="card p-4">
      <h4>Đặt lại mật khẩu</h4>
      <?php if ($errors): ?><div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-2"><label class="form-label">Mật khẩu mới</label><input class="form-control" name="password" type="password" required></div>
        <div class="mb-2"><label class="form-label">Xác nhận mật khẩu</label><input class="form-control" name="confirm_password" type="password" required></div>
        <button class="btn btn-primary" type="submit">Đặt lại mật khẩu</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>