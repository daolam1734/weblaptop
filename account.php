<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';

// 1. Check login
if (empty($_SESSION['user_id'])) {
    set_flash('warning', 'Vui lòng đăng nhập để xem thông tin tài khoản.');
    header('Location: /weblaptop/auth/login.php?next=/weblaptop/account.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = findUserById($user_id);

// 2. Handle Add Address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $label = trim($_POST['label'] ?? 'Nhà riêng');
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    $errors = [];
    if (!$recipient_name) $errors[] = 'Tên người nhận không được để trống.';
    if (!$phone) $errors[] = 'Số điện thoại không được để trống.';
    if (!$address_line1) $errors[] = 'Địa chỉ không được để trống.';

    if (empty($errors)) {
        global $pdo;
        
        // If this is set as default, unset other defaults
        if ($is_default) {
            $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }

        $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, label, recipient_name, phone, address_line1, city, district, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $label, $recipient_name, $phone, $address_line1, $city, $district, $is_default])) {
            set_flash('success', 'Thêm địa chỉ mới thành công.');
            header('Location: account.php');
            exit;
        } else {
            $errors[] = 'Có lỗi xảy ra, vui lòng thử lại.';
        }
    }
    if (!empty($errors)) set_flash('error', implode('<br>', $errors));
}

// 3. Handle Delete Address
if (isset($_GET['delete_address'])) {
    $addr_id = (int)$_GET['delete_address'];
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$addr_id, $user_id])) {
        set_flash('success', 'Đã xóa địa chỉ.');
    }
    header('Location: account.php');
    exit;
}

$addresses = getUserAddresses($user_id);

require_once __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --tet-red: #d32f2f;
        --tet-gold: #ffc107;
    }
    body { background-color: #f8fafc; font-family: 'Inter', system-ui, -apple-system, sans-serif; }

    /* Sidebar Modern */
    .sidebar-modern {
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }
    .avatar-wrapper {
        background: linear-gradient(135deg, #fff 0%, #f1f5f9 100%);
        padding: 2rem 1.5rem;
    }
    .nav-link-modern {
        padding: 12px 20px;
        border-radius: 12px;
        margin: 4px 12px;
        color: #64748b;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .nav-link-modern:hover {
        background: #f1f5f9;
        color: var(--tet-red);
    }
    .nav-link-modern.active {
        background: #fff1f2;
        color: var(--tet-red);
    }

    /* Card Styling */
    .account-card {
        background: #fff;
        border-radius: 20px;
        margin-bottom: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: none;
        overflow: hidden;
    }
    .account-card-header {
        padding: 20px 25px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .account-card-body { padding: 25px; }

    .address-card {
        border: 2px solid #f1f5f9;
        border-radius: 16px;
        padding: 20px;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: #fff;
    }
    .address-card:hover {
        border-color: var(--tet-red);
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(211, 47, 47, 0.05);
    }
    .address-card.default {
        border-color: var(--tet-red);
        background: #fff1f2;
    }
    
    .btn-add-address {
        background: var(--tet-red);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 10px 24px;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(211, 47, 47, 0.2);
    }
    .btn-add-address:hover {
        background: #b71c1c;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(211, 47, 47, 0.3);
    }

    .form-control {
        border-radius: 10px;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
    }
    .form-control:focus {
        border-color: var(--tet-red);
        box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
    }

    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    }
    .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 25px;
    }
    .modal-body { padding: 25px; }
</style>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 sidebar-modern overflow-hidden">
                <div class="avatar-wrapper text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mx-auto" style="width: 90px; height: 90px;">
                            <i class="bi bi-person-fill fs-1 text-danger opacity-75"></i>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white border-3 rounded-circle" style="width: 14px; height: 14px;"></span>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                    <span class="badge bg-light text-muted fw-normal rounded-pill px-3 py-2">Thành viên hạng Đồng</span>
                </div>
                <div class="py-3 bg-white">
                    <a href="account.php" class="nav-link-modern active">
                        <i class="bi bi-person-circle fs-5"></i> Hồ sơ của tôi
                    </a>
                    <a href="orders.php" class="nav-link-modern">
                        <i class="bi bi-box-seam fs-5"></i> Đơn hàng đã mua
                    </a>
                    <a href="notifications.php" class="nav-link-modern">
                        <i class="bi bi-bell fs-5"></i> Thông báo
                    </a>
                    <hr class="mx-4 my-2 opacity-10">
                    <a href="/weblaptop/auth/logout.php" class="nav-link-modern text-danger">
                        <i class="bi bi-box-arrow-right fs-5"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Hồ Sơ Của Tôi</h3>
                <p class="text-muted mb-0">Quản lý và cập nhật thông tin cá nhân của bạn</p>
            </div>
            
            <!-- Profile Info -->
            <div class="account-card">
                <div class="account-card-header">
                    <div class="bg-primary-subtle p-2 rounded-3">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                    </div>
                    <span>Thông tin cơ bản</span>
                </div>
                <div class="account-card-body">
                    <div class="row mb-4 align-items-center">
                        <div class="col-sm-3 text-muted">Họ và tên</div>
                        <div class="col-sm-9 fw-bold text-dark fs-5"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <div class="row mb-4 align-items-center">
                        <div class="col-sm-3 text-muted">Địa chỉ Email</div>
                        <div class="col-sm-9">
                            <span class="text-dark bg-light px-3 py-2 rounded-pill"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>
                    <div class="row mb-0 align-items-center">
                        <div class="col-sm-3 text-muted">Số điện thoại</div>
                        <div class="col-sm-9">
                            <span class="text-dark"><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Địa chỉ giao hàng</h4>
                    <p class="text-muted small mb-0">Địa chỉ của bạn sẽ được tự động điền khi đặt hàng</p>
                </div>
                <button class="btn-add-address" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="bi bi-plus-lg me-2"></i>Thêm địa chỉ
                </button>
            </div>

            <?php if (empty($addresses)): ?>
                <div class="account-card p-5 text-center">
                    <div class="mb-3 opacity-25">
                        <i class="bi bi-geo-alt fs-1"></i>
                    </div>
                    <h5 class="fw-bold">Chưa có địa chỉ</h5>
                    <p class="text-muted">Vui lòng thêm địa chỉ để thuận tiện cho việc nhận hàng.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($addresses as $addr): ?>
                        <div class="col-md-6">
                            <div class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-pill <?php echo $addr['is_default'] ? 'bg-danger' : 'bg-light text-muted'; ?> px-3 py-2">
                                            <?php echo htmlspecialchars($addr['label']); ?>
                                        </span>
                                        <?php if ($addr['is_default']): ?>
                                            <span class="text-danger small fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Mặc định</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown" style="width: 32px; height: 32px; padding: 0;">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                            <li><a class="dropdown-item py-2 text-danger" href="?delete_address=<?php echo $addr['id']; ?>" onclick="return confirm('Xóa địa chỉ này?')">
                                                <i class="bi bi-trash3 me-2"></i>Xóa địa chỉ
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($addr['recipient_name']); ?></h6>
                                <div class="text-muted mb-3"><i class="bi bi-phone me-2"></i><?php echo htmlspecialchars($addr['phone']); ?></div>
                                <div class="text-dark opacity-75 small lh-base">
                                    <i class="bi bi-geo-alt me-2 text-danger"></i>
                                    <?php echo htmlspecialchars($addr['address_line1']); ?>, 
                                    <?php echo htmlspecialchars($addr['district']); ?>, 
                                    <?php echo htmlspecialchars($addr['city']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content overflow-hidden">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold text-dark">Thêm Địa Chỉ Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted uppercase">Tên người nhận</label>
                        <input type="text" name="recipient_name" class="form-control" placeholder="Họ và tên" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted uppercase">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="0xxx.xxx.xxx" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted uppercase">Địa chỉ chi tiết</label>
                        <input type="text" name="address_line1" class="form-control" placeholder="Số nhà, tên đường..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted uppercase">Quận/Huyện</label>
                        <input type="text" name="district" class="form-control" placeholder="Quận/Huyện">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tỉnh/Thành phố</label>
                        <input type="text" name="city" class="form-control" placeholder="Nhập tỉnh/thành phố">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Loại địa chỉ</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="label" id="labelHome" value="Nhà riêng" checked>
                            <label class="form-check-label" for="labelHome">Nhà riêng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="label" id="labelOffice" value="Văn phòng">
                            <label class="form-check-label" for="labelOffice">Văn phòng</label>
                        </div>
                    </div>
                </div>
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="is_default" id="is_default" checked>
                    <label class="form-check-label small" for="is_default">Đặt làm địa chỉ mặc định</label>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-toggle="modal">Trở lại</button>
                <button type="submit" name="add_address" class="btn btn-danger rounded-pill px-4">Hoàn thành</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

