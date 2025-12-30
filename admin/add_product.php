<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
if (session_status() == PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $image = $_POST['image'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $image, $stock]);
    header('Location: products.php'); exit;
}
?>
<div class="row">
  <div class="col-md-8">
    <h2>Thêm sản phẩm</h2>
    <form method="post">
      <div class="mb-3"><label>Tên</label><input class="form-control" name="name" required></div>
      <div class="mb-3"><label>Mô tả</label><textarea class="form-control" name="description"></textarea></div>
      <div class="mb-3"><label>Giá</label><input type="number" step="0.01" class="form-control" name="price" required></div>
      <div class="mb-3"><label>Số lượng</label><input type="number" class="form-control" name="stock" required></div>
      <div class="mb-3"><label>URL ảnh</label><input class="form-control" name="image" placeholder="https://... hoặc /weblaptop/uploads/your.jpg"></div>
      <button class="btn btn-success">Thêm</button>
      <a href="products.php" class="btn btn-secondary">Quay lại</a>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>