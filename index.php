<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/functions.php';

$products = getProducts();
?>
<div class="row">
  <aside class="col-md-3 sidebar">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
  </aside>
  <main class="col-md-9">
    <div class="row g-3">
      <?php foreach ($products as $p): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 product-card">
            <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
              <p class="card-text text-truncate"><?php echo htmlspecialchars($p['description']); ?></p>
              <p class="mt-auto"><strong>$<?php echo number_format($p['price'],2); ?></strong></p>
              <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-primary">Xem</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>