<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProduct($id);
if (!$product) {
  echo '<div class="alert alert-danger">Không tìm thấy sản phẩm.</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $qty = max(1, (int)$_POST['quantity']);
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    header('Location: cart.php');
    exit;
}
?>
<div class="row">
  <aside class="col-md-3 sidebar">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
  </aside>
  <main class="col-md-9">
    <div class="row">
      <div class="col-md-6">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
      </div>
      <div class="col-md-6">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        <p><strong>Giá:</strong> $<?php echo number_format($product['price'],2); ?></p>
        <p><strong>Số lượng:</strong> <?php echo (int)$product['stock']; ?></p>

        <form method="post" class="d-flex gap-2 align-items-center">
          <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" class="form-control w-auto">
          <button class="btn btn-success">Thêm vào giỏ</button>
        </form>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>