<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() == PHP_SESSION_NONE) session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['admin_logged_in'] = $user['username'];
        header('Location: products.php'); exit;
    } else {
      $error = 'Sai tên đăng nhập hoặc mật khẩu';
    }
}
?>
<div class="row justify-content-center">
  <div class="col-md-6">
      <h2>Đăng nhập quản trị</h2>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
          <label>Tên đăng nhập</label>
          <input class="form-control" name="username" required>
      </div>
      <div class="mb-3">
          <label>Mật khẩu</label>
          <input type="password" class="form-control" name="password" required>
      </div>
        <button class="btn btn-primary">Đăng nhập</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>