<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order info
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email,
           ua.recipient_name, ua.phone as customer_phone, ua.address_line1, ua.district, ua.city
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN user_addresses ua ON o.address_id = ua.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash("error", "Không tìm thấy đơn hàng.");
    header("Location: orders.php");
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT oi.*
    FROM order_items oi
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --primary-dark: #1e293b;
        --accent-blue: #3b82f6;
        --text-main: #334155;
        --text-light: #64748b;
        --bg-light: #f8fafc;
    }

    .status-badge { 
        padding: 0.4rem 0.8rem; 
        border-radius: 9999px; 
        font-size: 0.75rem; 
        font-weight: 700; 
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-dang_cho { background: #fef3c7; color: #92400e; }
    .status-da_xac_nhan { background: #e0f2fe; color: #075985; }
    .status-dang_xu_ly { background: #f3e8ff; color: #6b21a8; }
    .status-da_gui { background: #e0e7ff; color: #3730a3; }
    .status-da_giao { background: #dcfce7; color: #166534; }
    .status-hoan_thanh { background: #dcfce7; color: #166534; }
    .status-huy { background: #fee2e2; color: #991b1b; }

    .card-modern {
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        border-radius: 1.25rem;
        overflow: hidden;
    }

    .timeline-modern { position: relative; padding-left: 30px; }
    .timeline-modern::before { content: ''; position: absolute; left: 7px; top: 5px; bottom: 5px; width: 2px; background: #f1f5f9; }
    .timeline-item { position: relative; margin-bottom: 25px; }
    .timeline-dot { position: absolute; left: -30px; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 3px solid #e2e8f0; z-index: 1; }
    .timeline-item.active .timeline-dot { border-color: var(--accent-blue); background: var(--accent-blue); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
    .timeline-content { background: var(--bg-light); padding: 1rem; border-radius: 1rem; border: 1px solid #f1f5f9; }
    
    .table-modern thead th { 
        background: var(--bg-light); 
        border-bottom: 2px solid #f1f5f9; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        color: var(--text-light); 
        padding: 1rem 1.5rem; 
    }
</style>

<div class="admin-wrapper">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="orders.php" class="text-decoration-none text-muted">Đơn hàng</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold">#<?php echo $order['id']; ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Chi Tiết Đơn Hàng #<?php echo $order['id']; ?></h4>
                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                        <?php 
                        $status_map = [
                            'dang_cho' => 'Chờ xác nhận',
                            'da_xac_nhan' => 'Đã xác nhận',
                            'dang_xu_ly' => 'Đang xử lý',
                            'da_gui' => 'Đang giao hàng',
                            'da_giao' => 'Đã giao hàng',
                            'hoan_thanh' => 'Hoàn thành',
                            'huy' => 'Đã hủy'
                        ];
                        echo $status_map[$order['order_status']] ?? $order['order_status'];
                        ?>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border border-secondary border-opacity-10 shadow-sm btn-sm px-4 rounded-pill fw-bold">
                        <i class="bi bi-printer me-2"></i> In Hóa Đơn
                    </button>
                    <?php if ($order['order_status'] === 'da_xac_nhan'): ?>
                    <button class="btn btn-primary shadow-sm btn-sm px-4 rounded-pill fw-bold">
                        <i class="bi bi-truck me-2"></i> Bắt Đầu Giao
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card card-modern mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom border-secondary border-opacity-10">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>Danh sách sản phẩm</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end pe-4">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-laptop fs-4 text-muted"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($it['product_name']); ?></div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">SKU: <?php echo htmlspecialchars($it['sku'] ?: 'N/A'); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-medium"><?php echo number_format($it['unit_price']); ?>₫</td>
                                        <td class="text-center fw-bold text-dark"><?php echo $it['quantity']; ?></td>
                                        <td class="text-end pe-4 fw-bold text-primary"><?php echo number_format($it['subtotal']); ?>₫</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light bg-opacity-30 p-4 border-top border-secondary border-opacity-10">
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính:</span>
                                    <span class="fw-bold text-dark"><?php echo number_format($order['subtotal']); ?>₫</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span class="fw-bold text-dark"><?php echo number_format($order['shipping_fee']); ?>₫</span>
                                </div>
                                <?php if ($order['shipping_discount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span class="small fw-medium">Voucher vận chuyển:</span>
                                    <span class="small fw-bold">-<?php echo number_format($order['shipping_discount']); ?>₫</span>
                                </div>
                                <?php endif; ?>
                                <?php if ($order['discount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span class="small fw-medium">Giảm giá voucher:</span>
                                    <span class="small fw-bold">-<?php echo number_format($order['discount']); ?>₫</span>
                                </div>
                                <?php endif; ?>
                                <div class="border-top my-3 border-secondary border-opacity-10"></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark h6 mb-0">Tổng thanh toán:</span>
                                    <span class="fw-bold text-primary h4 mb-0"><?php echo number_format($order['total']); ?>₫</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="card card-modern border-0">
                    <div class="card-header bg-white py-3 border-bottom border-secondary border-opacity-10">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử xử lý</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-modern">
                            <div class="timeline-item active">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="small fw-bold text-dark">Đặt hàng thành công</div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar-event me-1"></i><?php echo date('H:i, d/m/Y', strtotime($order['created_at'])); ?></div>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.8rem;">Đơn hàng đã được ghi nhận vào hệ thống.</div>
                                </div>
                            </div>
                            <?php if($order['order_status'] != 'dang_cho'): ?>
                            <div class="timeline-item active">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="small fw-bold text-dark">Đã được xác nhận</div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar-check me-1"></i><?php echo date('H:i, d/m/Y', strtotime($order['updated_at'])); ?></div>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.8rem;">Nhân viên kho đã kiểm tra và chuẩn bị hàng.</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Customer Info -->
                <div class="card card-modern mb-4 border-0">
                    <div class="card-header bg-white py-3 border-bottom border-secondary border-opacity-10">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person me-2 text-primary"></i>Khách hàng</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="customer-avatar me-2" style="width: 50px; height: 50px; border-radius: 15px; background: var(--bg-light); border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--primary-dark);">
                                <?php echo strtoupper(substr($order['customer_name'] ?: 'K', 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['customer_name'] ?: 'Khách vãng lai'); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($order['customer_email'] ?: 'N/A'); ?></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">Địa chỉ giao hàng</label>
                            <div class="p-3 bg-light bg-opacity-50 rounded-4 border border-secondary border-opacity-10">
                                <div class="small fw-bold text-dark mb-1"><?php echo htmlspecialchars($order['recipient_name']); ?></div>
                                <div class="small text-primary fw-bold mb-2"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                <div class="small text-muted lh-base">
                                    <?php echo htmlspecialchars($order['address_line1']); ?><br>
                                    <?php echo htmlspecialchars($order['district']); ?>, <?php echo htmlspecialchars($order['city']); ?>
                                </div>
                            </div>
                        </div>

                        <?php if($order['notes']): ?>
                        <div class="mb-4">
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">Ghi chú từ khách</label>
                            <div class="small text-muted p-3 bg-light rounded-4 border border-secondary border-opacity-10">
                                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div>
                            <label class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">Phương thức thanh toán</label>
                            <div class="d-flex align-items-center p-2 rounded-3 bg-light border border-secondary border-opacity-10">
                                <div class="bg-white text-primary rounded-circle shadow-sm p-2 me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div class="small fw-bold text-dark">
                                    <?php echo $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Thanh toán trực tuyến (VNPAY)'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Status Update -->
                <div class="card card-modern border-0 bg-primary bg-opacity-10 shadow-none" style="border: 1px dashed var(--accent-blue) !important;">
                    <div class="card-header bg-transparent py-3 border-bottom border-primary border-opacity-10">
                        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Thao tác nhanh</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="update_order_status.php" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Trạng thái mới</label>
                                <select name="status" class="form-select border-0 shadow-sm rounded-3">
                                    <option value="dang_cho" <?php echo $order['order_status'] == 'dang_cho' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                    <option value="da_xac_nhan" <?php echo $order['order_status'] == 'da_xac_nhan' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                    <option value="dang_xu_ly" <?php echo $order['order_status'] == 'dang_xu_ly' ? 'selected' : ''; ?>>Đang xử lý</option>
                                    <option value="da_gui" <?php echo $order['order_status'] == 'da_gui' ? 'selected' : ''; ?>>Đang giao hàng</option>
                                    <option value="da_giao" <?php echo $order['order_status'] == 'da_giao' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                    <option value="hoan_thanh" <?php echo $order['order_status'] == 'hoan_thanh' ? 'selected' : ''; ?>>Hoàn thành</option>
                                    <option value="huy" <?php echo $order['order_status'] == 'huy' ? 'selected' : ''; ?>>Hủy đơn hàng</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Ghi chú nội bộ</label>
                                <textarea name="note" class="form-control border-0 shadow-sm rounded-3" rows="2" placeholder="VD: Khách hẹn giao sau 18h..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-pill shadow">
                                <i class="bi bi-save me-2"></i> Cập Nhật
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
