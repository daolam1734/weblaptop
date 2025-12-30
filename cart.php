<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/functions.php';
if (session_status() == PHP_SESSION_NONE) session_start();

// Actions: update quantities, remove, clear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $q) {
            $id = (int)$id; $q = max(0, (int)$q);
            if ($q === 0) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id] = $q;
        }
    }
    if (isset($_POST['clear'])) {
        unset($_SESSION['cart']);
    }
    header('Location: cart.php'); exit;
}

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0.0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $rows = $stmt->fetchAll();
    foreach ($rows as $r) {
        $r['quantity'] = $cart[$r['id']];
        $r['subtotal'] = $r['quantity'] * $r['price'];
        $total += $r['subtotal'];
        $items[] = $r;
    }
}
?>
<div class="row">
  <aside class="col-md-3 sidebar">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
  </aside>
  <main class="col-md-9">
    <h2>Giỏ hàng</h2>
    <?php if (empty($items)): ?>
      <div class="alert alert-info">Giỏ hàng của bạn trống. <a href="/weblaptop">Mua ngay</a></div>
    <?php else: ?>
      <form method="post">
      <table class="table">
        <thead><tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tổng</th></tr></thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?php echo htmlspecialchars($it['name']); ?></td>
              <td>$<?php echo number_format($it['price'],2); ?></td>
              <td><input type="number" name="qty[<?php echo $it['id']; ?>]" value="<?php echo $it['quantity']; ?>" min="0" class="form-control w-auto"></td>
              <td>$<?php echo number_format($it['subtotal'],2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="3">Tổng cộng</th><th>$<?php echo number_format($total,2); ?></th></tr>
        </tfoot>
      </table>
      <div class="d-flex gap-2">
        <button type="submit" name="update" class="btn btn-primary">Cập nhật giỏ</button>
        <button type="submit" name="clear" class="btn btn-danger">Xóa giỏ</button>
        <a href="/weblaptop" class="btn btn-secondary">Tiếp tục mua sắm</a>
      </div>
      </form>
    <?php endif; ?>
  </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>