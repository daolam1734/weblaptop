<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/functions.php";

$cart = $_SESSION["cart"] ?? [];
$items = [];
$subtotal = 0.0;

// Handle Voucher
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["apply_voucher"])) {
    $code = strtoupper(trim($_POST["voucher_code"] ?? ""));
    $v = getVoucherByCode($code);
    if ($v) {
        $type = ($v['discount_type'] === 'shipping') ? 'shipping' : 'product';
        
        if (!isset($_SESSION["vouchers"])) $_SESSION["vouchers"] = [];
        
        $_SESSION["vouchers"][$type] = [
            "code" => $v["code"],
            "discount_type" => $v["discount_type"],
            "discount_value" => $v["discount_value"],
            "max_discount" => $v["max_discount"],
            "min_spend" => $v["min_spend"]
        ];
        
        $msg = ($type === 'shipping') ? "mã vận chuyển" : "mã giảm giá";
        set_flash("success", "Đã áp dụng $msg: $code");
    } else {
        set_flash("danger", "Mã không hợp lệ hoặc đã hết hạn.");
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET["remove_voucher"])) {
    $type = $_GET["remove_voucher"];
    if (isset($_SESSION["vouchers"][$type])) {
        unset($_SESSION["vouchers"][$type]);
        set_flash("info", "Đã xóa mã.");
    }
    header("Location: cart.php");
    exit;
}

if ($cart) {
    $ids = implode(",", array_map("intval", array_keys($cart)));
    // Use GROUP BY to prevent duplicate product rows if there are multiple images
    $sql = "SELECT p.* FROM products p WHERE p.id IN ($ids) GROUP BY p.id";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    
    foreach ($rows as $r) {
        $r["quantity"] = $cart[$r["id"]];
        $r["item_subtotal"] = $r["quantity"] * $r["price"];
        $r["image"] = getProductImage($r["id"]);
        $items[] = $r;
    }
}

// Fetch available vouchers for the "Select Voucher" modal
$available_vouchers = $pdo->query("SELECT * FROM vouchers WHERE is_active = 1 AND (start_date <= NOW() OR start_date IS NULL) AND (end_date >= NOW() OR end_date IS NULL) AND (usage_limit IS NULL OR usage_count < usage_limit) ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . "/includes/header.php";
?>

<style>
    :root {
        --primary-red: #C62222;
        --secondary-gold: #FFD700;
        --bg-gray: #f4f6f8;
    }
    
    body { background-color: var(--bg-gray); color: #333; }

    /* Breadcrumb */
    .breadcrumb-nav { background: #fff; border-bottom: 1px solid #eee; padding: 12px 0; margin-bottom: 25px; }
    .breadcrumb { margin-bottom: 0; font-size: 14px; }
    .breadcrumb-item a { color: #6c757d; text-decoration: none; }
    .breadcrumb-item a:hover { color: var(--primary-red); }

    .cart-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    /* Main Cart Area */
    .cart-main-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
    
    .cart-table-header { 
        display: grid; 
        grid-template-columns: 50px 1fr 150px 150px 50px; 
        padding: 15px 20px; 
        background: #fff; 
        border-bottom: 1px solid #eee; 
        font-weight: 600; 
        font-size: 14px; 
        color: #666;
    }

    .cart-item { 
        display: grid; 
        grid-template-columns: 50px 1fr 150px 150px 50px; 
        padding: 20px; 
        align-items: center; 
        border-bottom: 1px solid #f8f9fa; 
        transition: background 0.2s;
    }
    .cart-item:hover { background: #fcfcfc; }
    .cart-item:last-child { border-bottom: none; }

    .item-info { display: flex; align-items: center; gap: 15px; }
    .item-img { width: 80px; height: 80px; object-fit: contain; border: 1px solid #eee; border-radius: 4px; padding: 5px; background: #fff; }
    .item-details { display: flex; flex-direction: column; gap: 4px; }
    .item-name { font-weight: 600; color: #333; text-decoration: none; font-size: 15px; line-height: 1.4; }
    .item-name:hover { color: var(--primary-red); }
    .item-price { font-size: 14px; color: #666; }

    /* Qty Control */
    .qty-box { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 4px; width: fit-content; margin: 0 auto; overflow: hidden; }
    .qty-btn { border: none; background: #f8f9fa; width: 30px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
    .qty-btn:hover { background: #eee; }
    .qty-input { width: 45px; height: 32px; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; text-align: center; font-size: 13px; font-weight: 600; }

    .item-subtotal { font-weight: 700; color: var(--primary-red); text-align: right; font-size: 15px; }
    
    .remove-btn { border: none; background: none; color: #ccc; cursor: pointer; transition: 0.2s; font-size: 18px; }
    .remove-btn:hover { color: var(--primary-red); }

    /* Selection */
    .form-check-input { width: 1.25rem; height: 1.25rem; cursor: pointer; border-color: #ddd; }
    .form-check-input:checked { background-color: var(--primary-red); border-color: var(--primary-red); }

    /* Sidebar Summary */
    .summary-card { background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 100px; }
    .summary-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; border-bottom: 2px solid #f8f9fa; padding-bottom: 12px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #555; }
    .summary-total { border-top: 1px dashed #eee; padding-top: 15px; margin-top: 15px; font-weight: 800; font-size: 20px; color: var(--primary-red); }
    
    .btn-checkout { 
        display: block; 
        width: 100%; 
        background: var(--primary-red); 
        color: #fff; 
        border: none; 
        border-radius: 6px; 
        padding: 15px; 
        font-weight: 700; 
        text-transform: uppercase; 
        text-decoration: none; 
        text-align: center; 
        margin-top: 25px; 
        transition: 0.3s;
    }
    .btn-checkout:not(.disabled):hover { background: #a51b1b; color: #fff; transform: translateY(-2px); }
    .btn-checkout.disabled { background: #ccc; cursor: not-allowed; }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .cart-table-header { display: none; }
        .cart-item { 
            grid-template-columns: 40px 1fr 40px; 
            grid-template-rows: auto auto; 
            gap: 15px; 
            padding: 15px;
        }
        .item-info { grid-column: 2 / 3; }
        .qty-box { grid-column: 2 / 3; margin: 0; }
        .item-subtotal { grid-column: 2 / 3; text-align: left; }
        .remove-btn { grid-column: 3 / 4; grid-row: 1 / 2; }
    }

    .voucher-input-group { border: 1px solid #ddd; border-radius: 6px; overflow: hidden; margin-top: 15px; }
    .voucher-input-group .form-control { border: none; font-size: 14px; }
    .voucher-input-group .btn { border-radius: 0; background: #333; color: #fff; font-weight: 600; font-size: 13px; }

</style>

<!-- Breadcrumb -->
<div class="breadcrumb-nav">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <h1 class="cart-title"><i class="bi bi-cart3"></i> Giỏ hàng của bạn</h1>
    
    <?php if (empty($items)): ?>
        <div class="text-center bg-white rounded shadow-sm py-5 px-3">
            <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-cart-2130356-1800917.png" height="200" alt="">
            <h4 class="mt-4 fw-bold">Giỏ hàng của bạn đang trống</h4>
            <p class="text-muted">Hãy lấp đầy nó bằng những chiếc laptop tuyệt vời nhé!</p>
            <a href="search.php" class="btn btn-danger px-4 py-2 mt-2" style="background-color: var(--primary-red); border-radius: 4px;">Mua ngay</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Left: Cart Items -->
            <div class="col-lg-8">
                <div class="cart-main-card">
                    <div class="cart-table-header">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" checked>
                        </div>
                        <div>Sản phẩm</div>
                        <div class="text-center">Số lượng</div>
                        <div class="text-end">Tạm tính</div>
                        <div></div>
                    </div>

                    <div id="cart-items-list">
                        <?php foreach ($items as $it): ?>
                        <div class="cart-item" data-id="<?php echo $it['id']; ?>" data-price="<?php echo $it['price']; ?>">
                            <div class="form-check">
                                <input class="form-check-input item-checkbox" type="checkbox" value="<?php echo $it['id']; ?>" checked>
                            </div>
                            <div class="item-info">
                                <img src="<?php echo htmlspecialchars($it['image']); ?>" alt="" class="item-img">
                                <div class="item-details">
                                    <a href="product.php?id=<?php echo $it['id']; ?>" class="item-name"><?php echo htmlspecialchars($it['name']); ?></a>
                                    <div class="item-price"><?php echo number_format($it['price'], 0, ',', '.'); ?> đ</div>
                                </div>
                            </div>
                            <div class="qty-control-col">
                                <div class="qty-box">
                                    <button class="qty-btn" onclick="ajaxUpdate(<?php echo $it['id']; ?>, -1)"><i class="bi bi-dash"></i></button>
                                    <input type="number" class="qty-input" value="<?php echo $it['quantity']; ?>" readonly id="qty-<?php echo $it['id']; ?>">
                                    <button class="qty-btn" onclick="ajaxUpdate(<?php echo $it['id']; ?>, 1)"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                            <div class="item-subtotal" id="subtotal-<?php echo $it['id']; ?>">
                                <?php echo number_format($it['item_subtotal'], 0, ',', '.'); ?> đ
                            </div>
                            <div class="text-end">
                                <button class="remove-btn" onclick="ajaxRemove(<?php echo $it['id']; ?>)"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <a href="index.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Quay lại mua sắm</a>
                    <button class="btn btn-link text-muted small p-0 text-decoration-none" onclick="ajaxClear()">Xóa toàn bộ giỏ hàng</button>
                </div>
            </div>

            <!-- Right: Summary -->
            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="summary-title">Thanh toán</h5>
                    
                    <div class="summary-row">
                        <span>Số lượng sản phẩm:</span>
                        <span id="summary-count">0</span>
                    </div>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span id="summary-subtotal">0 đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span id="summary-shipping">30.000 đ</span>
                    </div>

                    <div id="voucher-row" class="summary-row text-success" style="display: none;">
                        <span>Giảm giá sản phẩm (<span id="voucher-code-display"></span>):</span>
                        <span id="summary-discount">-0 đ</span>
                    </div>

                    <div id="shipping-voucher-row" class="summary-row text-success" style="display: none;">
                        <span>Giảm phí ship (<span id="shipping-voucher-code-display"></span>):</span>
                        <span id="summary-shipping-discount">-0 đ</span>
                    </div>

                    <hr>
                    
                    <form action="cart.php" method="POST" class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold mb-0">Mã giảm giá / Vận chuyển</label>
                            <a href="#" class="small text-decoration-none text-primary" data-bs-toggle="modal" data-bs-target="#voucherModal">Chọn mã <i class="bi bi-chevron-right"></i></a>
                        </div>
                        <div class="voucher-input-group d-flex">
                            <input type="text" name="voucher_code" class="form-control" placeholder="Nhập mã ưu đãi..." value="">
                            <button type="submit" name="apply_voucher" class="btn">ÁP DỤNG</button>
                        </div>
                    </form>

                    <?php if (!empty($_SESSION['vouchers'])): ?>
                        <div class="applied-vouchers mb-3">
                            <?php foreach ($_SESSION['vouchers'] as $type => $v): ?>
                                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded mb-1 border">
                                    <span class="small text-success fw-bold">
                                        <i class="bi bi-tag-fill"></i> <?php echo $v['code']; ?> 
                                        (<?php echo $type === 'shipping' ? 'Ship' : 'Sản phẩm'; ?>)
                                    </span>
                                    <a href="cart.php?remove_voucher=<?php echo $type; ?>" class="text-danger"><i class="bi bi-x-circle-fill"></i></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-total">
                        <div class="d-flex justify-content-between">
                            <span>Tổng cộng:</span>
                            <span id="summary-total">0 đ</span>
                        </div>
                        <div class="small fw-normal text-muted text-end mt-1" style="font-size: 11px;">(Đã bao gồm VAT nếu có)</div>
                    </div>

                    <a href="javascript:void(0)" class="btn-checkout" id="checkout-btn" onclick="proceedToCheckout()">
                        Đặt hàng ngay
                    </a>
                </div>

                <!-- Benefits -->
                <div class="p-3 mt-4" style="background: #fff8f6; border-radius: 8px; border: 1px solid #ffccbc;">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-truck-flatbed text-danger"></i>
                        <div class="small"><strong class="text-danger">MIỄN PHÍ VẬN CHUYỂN</strong><br>Cho đơn hàng từ 10 triệu đồng.</div>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-shield-check text-danger"></i>
                        <div class="small"><strong class="text-danger">AN TÂM MUA SẮM</strong><br>Bảo hành chính hãng toàn quốc 24 tháng.</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Voucher Selection Modal -->
<div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="voucherModalLabel">Ưu đãi dành cho bạn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($available_vouchers)): ?>
                    <div class="p-4 text-center text-muted">Hiện không có mã giảm giá nào khả dụng.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($available_vouchers as $v): ?>
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-shrink-0 bg-light p-2 rounded text-danger" style="width: 50px; text-align: center;">
                                        <i class="bi <?php echo $v['discount_type'] === 'shipping' ? 'bi-truck' : 'bi-ticket-perforated'; ?> fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($v['code']); ?></div>
                                        <div class="small text-muted">
                                            <?php 
                                            if ($v['discount_type'] === 'percentage') echo "Giảm " . (int)$v['discount_value'] . "%";
                                            elseif ($v['discount_type'] === 'fixed') echo "Giảm " . number_format($v['discount_value'], 0, ',', '.') . " đ";
                                            else echo "Giảm ship " . number_format($v['discount_value'], 0, ',', '.') . " đ";
                                            ?>
                                            <br>Đơn tối thiểu: <?php echo number_format($v['min_spend'], 0, ',', '.'); ?> đ
                                        </div>
                                    </div>
                                    <form action="cart.php" method="POST">
                                        <input type="hidden" name="voucher_code" value="<?php echo $v['code']; ?>">
                                        <button type="submit" name="apply_voucher" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold">Dùng</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const appliedVouchers = <?php echo json_encode($_SESSION['vouchers'] ?? []); ?>;

// Checkbox Logic
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.checked = checked;
    });
    calculateTotals();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('item-checkbox')) {
        const all = document.querySelectorAll('.item-checkbox');
        const checked = document.querySelectorAll('.item-checkbox:checked');
        const selectAll = document.getElementById('selectAll');
        if (selectAll) selectAll.checked = all.length === checked.length;
        calculateTotals();
    }
});

function calculateTotals() {
    let subtotal = 0;
    let count = 0;
    const checked = document.querySelectorAll('.item-checkbox:checked');
    
    checked.forEach(cb => {
        const row = cb.closest('.cart-item');
        const id = row.dataset.id;
        const price = parseInt(row.dataset.price);
        const qty = parseInt(document.getElementById('qty-' + id).value);
        subtotal += price * qty;
        count += qty;
    });

    let shipping = subtotal >= 10000000 ? 0 : 30000;
    let productDiscount = 0;
    let shippingDiscount = 0;
    
    // Product Voucher
    if (appliedVouchers.product) {
        let v = appliedVouchers.product;
        if (subtotal >= parseInt(v.min_spend)) {
            if (v.discount_type === 'fixed') {
                productDiscount = parseInt(v.discount_value);
            } else if (v.discount_type === 'percentage') {
                productDiscount = subtotal * (parseInt(v.discount_value) / 100);
                if (v.max_discount) productDiscount = Math.min(productDiscount, parseInt(v.max_discount));
            }
            productDiscount = Math.min(productDiscount, subtotal);
            document.getElementById('voucher-row').style.display = 'flex';
            document.getElementById('voucher-code-display').innerText = v.code;
            document.getElementById('summary-discount').innerText = '-' + Math.round(productDiscount).toLocaleString('vi-VN') + ' đ';
        } else {
            document.getElementById('voucher-row').style.display = 'none';
        }
    } else {
        document.getElementById('voucher-row').style.display = 'none';
    }

    // Shipping Voucher
    if (appliedVouchers.shipping) {
        let v = appliedVouchers.shipping;
        if (subtotal >= parseInt(v.min_spend)) {
            shippingDiscount = Math.min(parseInt(v.discount_value), shipping);
            document.getElementById('shipping-voucher-row').style.display = 'flex';
            document.getElementById('shipping-voucher-code-display').innerText = v.code;
            document.getElementById('summary-shipping-discount').innerText = '-' + Math.round(shippingDiscount).toLocaleString('vi-VN') + ' đ';
        } else {
            document.getElementById('shipping-voucher-row').style.display = 'none';
        }
    } else {
        document.getElementById('shipping-voucher-row').style.display = 'none';
    }

    const total = subtotal + shipping - productDiscount - shippingDiscount;

    document.getElementById('summary-count').innerText = count;
    document.getElementById('summary-subtotal').innerText = subtotal.toLocaleString('vi-VN') + ' đ';
    document.getElementById('summary-shipping').innerText = shipping.toLocaleString('vi-VN') + ' đ';
    document.getElementById('summary-total').innerText = Math.max(0, Math.round(total)).toLocaleString('vi-VN') + ' đ';

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checked.length > 0) {
        checkoutBtn.classList.remove('disabled');
    } else {
        checkoutBtn.classList.add('disabled');
    }
}

// Initial calculation
if (document.querySelector('.item-checkbox')) {
    calculateTotals();
}

function ajaxUpdate(id, delta) {
    const input = document.getElementById('qty-' + id);
    let newQty = parseInt(input.value) + delta;
    if (newQty < 1) return;

    const fd = new FormData();
    fd.append('action', 'update');
    fd.append('id', id);
    fd.append('qty', newQty);

    fetch('cart_api.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = newQty;
            const price = parseInt(input.closest('.cart-item').dataset.price);
            document.getElementById('subtotal-' + id).innerText = (price * newQty).toLocaleString('vi-VN') + ' đ';
            calculateTotals();
            
            // Sync header badge
            const badge = document.querySelector('.cart-badge');
            if (badge) badge.innerText = data.cart_count;
            if (document.getElementById('header-cart-dropdown')) {
                document.getElementById('header-cart-dropdown').innerHTML = data.dropdown_html;
            }
        } else {
            alert(data.message);
        }
    });
}

function ajaxRemove(id) {
    if (!confirm('Xóa sản phẩm này khỏi giỏ hàng?')) return;
    
    const fd = new FormData();
    fd.append('action', 'remove');
    fd.append('id', id);

    fetch('cart_api.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function ajaxClear() {
    if (!confirm('Dọn sạch giỏ hàng?')) return;
    
    const fd = new FormData();
    fd.append('action', 'clear');

    fetch('cart_api.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function proceedToCheckout() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) return;
    
    const selectedIds = Array.from(checked).map(cb => cb.value).join(',');
    window.location.href = 'checkout.php?selected=' + selectedIds;
}
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
