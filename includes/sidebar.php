<?php
// Sidebar with static categories and dynamic latest products
if (!isset($pdo)) require_once __DIR__ . '/../config/database.php';
$latest = $pdo->query("SELECT id, name FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<div class="mb-3">
  <div class="card">
    <div class="card-header bg-primary text-white">Danh mục</div>
    <ul class="list-group list-group-flush">
      <li class="list-group-item">Ultrabook</li>
      <li class="list-group-item">Gaming</li>
      <li class="list-group-item">Doanh nghiệp</li>
      <li class="list-group-item">Sinh viên</li>
    </ul>
  </div>
</div>

<div class="mb-3">
  <div class="card">
    <div class="card-header">Sản phẩm mới</div>
    <ul class="list-group list-group-flush">
      <?php foreach ($latest as $l): ?>
        <li class="list-group-item"><a href="/weblaptop/product.php?id=<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['name']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>