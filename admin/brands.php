<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions.php';

if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_brand']) || isset($_POST['update_brand']))) {
    $name = trim($_POST['name']);
    $brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
    $logo = $_POST['existing_logo'] ?? '';

    // Handle Logo Upload
    if (!empty($_FILES['logo_file']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/brands/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileExt = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
        
        if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $uploadDir . $newFileName)) {
            $logo = '/uploads/brands/' . $newFileName;
        }
    } elseif (!empty($_POST['logo_url'])) {
        $logo = $_POST['logo_url'];
    }

    if ($brand_id > 0) {
        // Update
        $stmt = $pdo->prepare("UPDATE brands SET name = ?, logo = ? WHERE id = ?");
        $stmt->execute([$name, $logo, $brand_id]);
        set_flash("success", "Cập nhật thương hiệu thành công.");
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO brands (name, logo) VALUES (?, ?)");
        $stmt->execute([$name, $logo]);
        set_flash("success", "Thêm thương hiệu thành công.");
    }
    header("Location: brands.php"); exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->execute([$id]);
    set_flash("success", "Xóa thương hiệu thành công.");
    header("Location: brands.php"); exit;
}

$brands = $pdo->query("SELECT * FROM brands ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-wrapper">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Quản Lý Thương Hiệu</h4>
                <p class="text-muted small mb-0">Quản lý các đối tác và thương hiệu sản phẩm đang kinh doanh.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold" id="form-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm thương hiệu mới</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" id="brand-form">
                            <input type="hidden" name="brand_id" id="brand_id" value="0">
                            <input type="hidden" name="existing_logo" id="existing_logo" value="">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Tên thương hiệu</label>
                                <input type="text" name="name" id="brand_name" class="form-control" placeholder="VD: Apple, Dell, ASUS" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Logo (Tải lên hoặc URL)</label>
                                <ul class="nav nav-pills mb-3 small" id="logo-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1 px-3" data-bs-toggle="pill" data-bs-target="#upload-tab" type="button">Tải lên</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#url-tab" type="button">Link URL</button>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="upload-tab">
                                        <input type="file" name="logo_file" class="form-control" accept="image/*">
                                    </div>
                                    <div class="tab-pane fade" id="url-tab">
                                        <input type="text" name="logo_url" id="brand_logo_url" class="form-control" placeholder="https://...">
                                    </div>
                                </div>
                                <div id="logo-preview-container" class="mt-3 d-none">
                                    <label class="form-label small text-muted">Logo hiện tại:</label>
                                    <div class="bg-light p-2 rounded text-center border">
                                        <img id="logo-preview" src="" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="add_brand" id="submit-btn" class="btn btn-primary py-2 fw-bold">
                                    <i class="bi bi-save me-2"></i>Lưu thương hiệu
                                </button>
                                <button type="button" id="cancel-btn" class="btn btn-light border py-2 fw-bold d-none">
                                    Hủy bỏ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-award me-2 text-primary"></i>Danh sách thương hiệu</h6>
                        <span class="badge bg-light text-dark border"><?php echo count($brands); ?> thương hiệu</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small fw-bold" style="width: 80px;">ID</th>
                                    <th class="py-3 text-muted small fw-bold">Logo</th>
                                    <th class="py-3 text-muted small fw-bold">Tên thương hiệu</th>
                                    <th class="py-3 text-muted small fw-bold text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($brands as $b): ?>
                                    <tr>
                                        <td class="ps-4 text-muted">#<?php echo $b['id']; ?></td>
                                        <td>
                                            <?php if ($b['logo']): ?>
                                                <div class="bg-white border rounded p-1 d-inline-block">
                                                    <img src="<?php echo htmlspecialchars($b['logo']); ?>" height="30" style="max-width: 80px; object-fit: contain;">
                                                </div>
                                            <?php else: ?>
                                                <div class="bg-light border rounded p-1 d-inline-block text-muted x-small px-2">No Logo</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><div class="fw-bold text-dark"><?php echo htmlspecialchars($b['name']); ?></div></td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-light border edit-brand-btn" 
                                                        data-id="<?php echo $b['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($b['name']); ?>"
                                                        data-logo="<?php echo htmlspecialchars($b['logo']); ?>"
                                                        title="Chỉnh sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="?delete=<?php echo $b['id']; ?>" 
                                                   class="btn btn-sm btn-light border text-danger" 
                                                   onclick="return confirm('Xóa thương hiệu này?')"
                                                   title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($brands)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-award fs-1 d-block mb-2"></i>
                                            Chưa có thương hiệu nào.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-brand-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const logo = this.getAttribute('data-logo');

        document.getElementById('form-title').innerHTML = '<i class="bi bi-pencil-square me-2 text-warning"></i>Sửa thương hiệu';
        document.getElementById('brand_id').value = id;
        document.getElementById('brand_name').value = name;
        document.getElementById('existing_logo').value = logo;
        document.getElementById('submit-btn').name = 'update_brand';
        document.getElementById('submit-btn').innerHTML = '<i class="bi bi-check-circle me-2"></i>Cập nhật';
        document.getElementById('cancel-btn').classList.remove('d-none');

        if (logo) {
            document.getElementById('logo-preview').src = logo;
            document.getElementById('logo-preview-container').classList.remove('d-none');
            
            // If it's a URL, switch to URL tab
            if (logo.startsWith('http')) {
                const urlTab = document.querySelector('[data-bs-target="#url-tab"]');
                bootstrap.Tab.getInstance(urlTab).show();
                document.getElementById('brand_logo_url').value = logo;
            }
        } else {
            document.getElementById('logo-preview-container').classList.add('d-none');
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancel-btn').addEventListener('click', function() {
    document.getElementById('form-title').innerHTML = '<i class="bi bi-plus-circle me-2 text-primary"></i>Thêm thương hiệu mới';
    document.getElementById('brand_id').value = '0';
    document.getElementById('brand-form').reset();
    document.getElementById('existing_logo').value = '';
    document.getElementById('submit-btn').name = 'add_brand';
    document.getElementById('submit-btn').innerHTML = '<i class="bi bi-save me-2"></i>Lưu thương hiệu';
    document.getElementById('logo-preview-container').classList.add('d-none');
    this.classList.add('d-none');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
