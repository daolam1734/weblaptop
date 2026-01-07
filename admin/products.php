<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

$stmt = $pdo->query("
  SELECT p.*, c.name as category_name, b.name as brand_name 
  FROM products p 
  LEFT JOIN categories c ON p.category_id = c.id 
  LEFT JOIN brands b ON p.brand_id = b.id 
  ORDER BY p.id DESC
");
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --primary-dark: #1e293b;
        --accent-blue: #3b82f6;
        --text-main: #334155;
        --text-light: #64748b;
        --bg-light: #f8fafc;
        --shopee-orange: #ee4d2d;
    }
    .product-section { background: #fff; padding: 24px; border-radius: 1.25rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); }
    
    .filter-card {
        background: #fff;
        border-radius: 1.25rem;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        margin-bottom: 24px;
        overflow: hidden;
    }
    
    .table-modern thead th { 
        background: var(--bg-light); 
        border-bottom: 2px solid #f1f5f9; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        color: var(--text-light); 
        padding: 1rem 1.5rem; 
    }
    .table-modern tbody td { 
        padding: 1.25rem 1.5rem; 
        vertical-align: middle; 
        font-size: 0.9rem; 
        border-bottom: 1px solid #f1f5f9; 
        color: var(--text-main);
    }
    
    .product-img { width: 48px; height: 48px; object-fit: contain; border-radius: 12px; background: #fff; padding: 2px; transition: transform 0.2s; }
    .product-img:hover { transform: scale(1.1); }
    .product-name-link { transition: color 0.1s; font-weight: 700; color: var(--primary-dark); text-decoration: none; }
    .product-name-link:hover { color: var(--accent-blue); }
    
    .status-badge { 
        padding: 0.4rem 0.8rem; 
        border-radius: 9999px; 
        font-size: 0.7rem; 
        font-weight: 700; 
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .stock-badge { 
        padding: 0.3rem 0.6rem; 
        border-radius: 8px; 
        font-size: 0.75rem; 
        font-weight: 700; 
    }
    .stock-good { background: #dcfce7; color: #166534; }
    .stock-low { background: #fef3c7; color: #92400e; }
    .stock-out { background: #fee2e2; color: #991b1b; }

    .btn-action { 
        width: 38px; 
        height: 38px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 12px; 
        background: #fff;
        color: var(--text-main);
        border: 1px solid #e2e8f0;
        transition: all 0.2s; 
    }
    .btn-action:hover { 
        background: var(--bg-light);
        color: var(--accent-blue);
        border-color: var(--accent-blue);
        transform: translateY(-2px);
    }
    
    .btn-action.btn-delete:hover {
        color: #ef4444;
        border-color: #fee2e2;
        background: #fff5f5;
    }

    .search-input-group {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.3s;
    }
    .search-input-group:focus-within {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
</style>

<div class="admin-wrapper">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Quản Lý Sản Phẩm</h4>
                <p class="text-muted small mb-0">Quản lý danh mục hàng hóa, giá bán và cập nhật tồn kho GrowTech.</p>
            </div>
            <a href="add_product.php" class="btn btn-primary px-4 py-2 shadow-sm rounded-pill fw-bold">
                <i class="bi bi-plus-lg me-2"></i> Thêm sản phẩm
            </a>
        </div>

        <!-- Filter Card -->
        <div class="filter-card border-0">
            <div class="p-4">
                <form class="row g-3 align-items-end" method="GET">
                    <div class="col-md-4">
                        <label class="form-label x-small fw-bold text-uppercase text-muted mb-2" style="letter-spacing: 0.05em;">Tìm kiếm sản phẩm</label>
                        <div class="input-group search-input-group">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-0 shadow-none ps-0" placeholder="Tên sản phẩm, SKU..." value="<?php echo $_GET['search'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label x-small fw-bold text-uppercase text-muted mb-2" style="letter-spacing: 0.05em;">Danh mục</label>
                        <select name="category" class="form-select bg-light border-0 rounded-3 shadow-none">
                            <option value="">Tất cả danh mục</option>
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
                            foreach ($categories as $cat) {
                                $selected = (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '';
                                echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label x-small fw-bold text-uppercase text-muted mb-2" style="letter-spacing: 0.05em;">Tình trạng kho</label>
                        <select name="stock" class="form-select bg-light border-0 rounded-3 shadow-none">
                            <option value="">Tất cả</option>
                            <option value="low" <?php echo (isset($_GET['stock']) && $_GET['stock'] == 'low') ? 'selected' : ''; ?>>Sắp hết hàng (< 10)</option>
                            <option value="out" <?php echo (isset($_GET['stock']) && $_GET['stock'] == 'out') ? 'selected' : ''; ?>>Đã hết hàng</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <i class="bi bi-filter me-1"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="product-section p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Sản phẩm</th>
                            <th>Danh mục / Thương hiệu</th>
                            <th>Giá bán</th>
                            <th>Tình trạng</th>
                            <th>Kho</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): 
                            $img = getProductImage($p['id']);
                            $stock_class = 'stock-good';
                            $stock_text = 'Còn hàng';
                            if ($p['stock'] <= 0) { $stock_class = 'stock-out'; $stock_text = 'Hết hàng'; }
                            elseif ($p['stock'] < 10) { $stock_class = 'stock-low'; $stock_text = 'Sắp hết'; }
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3">
                                        <div class="bg-light rounded-3 p-1 border">
                                            <img src="<?php echo htmlspecialchars($img); ?>" class="product-img" onerror="this.src='../assets/images/no-image.png'">
                                        </div>
                                        <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger p-1 border border-white" style="width: 18px; height: 18px;"><i class="bi bi-lightning-fill" style="font-size: 10px;"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="../product.php?id=<?php echo $p['id']; ?>" target="_blank" class="product-name-link d-block text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($p['name']); ?></a>
                                        <div class="small text-muted" style="font-size: 11px;">SKU: <span class="fw-bold"><?php echo htmlspecialchars($p['sku'] ?: 'N/A'); ?></span></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0" style="font-size: 0.85rem;"><?php echo htmlspecialchars($p['category_name']); ?></div>
                                <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($p['brand_name']); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary mb-0"><?php echo number_format($p['price']); ?>₫</div>
                                <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                                    <div class="text-muted text-decoration-line-through" style="font-size: 11px;"><?php echo number_format($p['sale_price']); ?>₫</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($p['is_active']) && $p['is_active']): ?>
                                    <span class="status-badge bg-light text-dark border">
                                        <i class="bi bi-circle-fill me-1 text-success" style="font-size: 6px;"></i> Hoạt động
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge bg-light text-dark border">
                                        <i class="bi bi-circle-fill me-1 text-secondary" style="font-size: 6px;"></i> Tạm ẩn
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="stock-badge <?php echo $stock_class; ?> d-inline-block">
                                    <?php echo $p['stock']; ?> <span class="x-small fw-normal opacity-75 ms-1"><?php echo $stock_text; ?></span>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn-action" title="Sửa sản phẩm"><i class="bi bi-pencil-square"></i></a>
                                    <a href="delete_product.php?id=<?php echo $p['id']; ?>" class="btn-action btn-delete" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">Hiển thị <b><?php echo count($products); ?></b> sản phẩm</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link rounded-pill" href="#">Trước</a></li>
                            <li class="page-item active"><a class="page-link mx-1 rounded-pill" href="#">1</a></li>
                            <li class="page-item"><a class="page-link rounded-pill" href="#">Sau</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
