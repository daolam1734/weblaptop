<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../functions.php';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WebLaptop - Cửa hàng laptop đơn giản</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/weblaptop/assets/css/style.css" rel="stylesheet">
</head>
<body>
<a class="skip-link visually-hidden-focusable" href="#main-content">Bỏ qua sang nội dung</a>
<!-- Top Bar -->
<div class="topbar bg-light small text-muted">
  <div class="container d-flex justify-content-between align-items-center py-1">
    <div>
      <span class="me-3">Hotline: <a href="tel:19001234">1900 1234</a></span>
      <a href="/weblaptop/returns.php" class="me-2">Chính sách đổi trả</a>
      <a href="/weblaptop/shipping.php">Giao hàng</a>
    </div>
    <div>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="/weblaptop/account.php" class="me-2">Xin chào, <?php echo htmlspecialchars(
          isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Người dùng'
        ); ?></a>
        <a href="/weblaptop/auth/logout.php" class="me-2">Đăng xuất</a>
      <?php else: ?>
        <a href="/weblaptop/auth/login.php" class="me-2">Đăng nhập</a>
        <a href="/weblaptop/auth/register.php" class="me-2">Đăng ký</a>
      <?php endif; ?>
      <a href="?lang=vi" class="me-1">VN</a> | <a href="?lang=en" class="ms-1">EN</a>
    </div>
  </div>
</div>
<?php if (function_exists('display_flash')) display_flash(); ?>

<!-- Main Header -->
<header class="site-main-header bg-white border-bottom" role="banner">
  <div class="container d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center">
      <button id="mobile-menu-toggle" class="btn btn-outline-secondary d-lg-none me-2" type="button" aria-controls="mobileMenu" aria-expanded="false">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand fw-bold text-dark me-3" href="/weblaptop">WebLaptop</a>
    </div>

    <div id="header-search" class="flex-grow-1 mx-3 search-col" role="search">
      <form action="/weblaptop/index.php" method="get" class="d-flex" role="search" aria-label="Tìm kiếm sản phẩm">
        <input id="header-search-input" name="q" class="form-control" placeholder="Tìm laptop theo tên, CPU, RAM, SSD, GPU..." aria-label="Tìm kiếm">
        <button class="btn btn-primary ms-2" type="submit">Tìm</button>
      </form>
      <div id="search-suggestions" aria-hidden="true" role="listbox"></div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <a href="/weblaptop/notifications.php" class="btn btn-light d-none d-md-inline-block" title="Thông báo" aria-label="Thông báo"><i class="bi bi-bell"></i></a>

      <div class="header-cart" id="header-cart" role="group" aria-label="Giỏ hàng">
        <a id="header-cart-btn" href="/weblaptop/cart.php" class="btn btn-light position-relative" title="Giỏ hàng" aria-haspopup="true" aria-expanded="false">
          <i class="bi bi-cart3" aria-hidden="true"></i>
          <span class="badge bg-danger" id="header-cart-count"><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></span>
        </a>
        <div id="header-cart-dropdown" class="dropdown-panel" aria-hidden="true">
          <div class="p-2">
            <?php
              $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
              if (!$cart) {
                echo '<div class="text-center text-muted p-3">Giỏ hàng trống</div>';
              } else {
                $total = 0;
                foreach ($cart as $pid => $qty) {
                  $prod = getProduct($pid);
                  if (!$prod) continue;
                  $subtotal = $prod['price'] * $qty;
                  $total += $subtotal;
                  $img = 'https://via.placeholder.com/80x60?text=No+Image';
                  // safe fetch image if function exists
                  if (function_exists('getProductImage')) {
                    $i = getProductImage($prod['id']);
                    if ($i) $img = $i;
                  }
                  echo '<div class="item">';
                  echo "<img src='".htmlspecialchars($img)."' alt='".htmlspecialchars($prod['name'])."'>";
                  echo '<div class="flex-grow-1">';
                  echo '<div class="fw-semibold">'.htmlspecialchars($prod['name']).'</div>';
                  echo '<div class="small text-muted">Số lượng: '.intval($qty).' × '.number_format($prod['price'],0,',','.').' VNĐ</div>';
                  echo '</div>';
                  echo '</div>';
                }
                echo '<div class="footer">';
                echo '<div><strong>Tổng:</strong> '.number_format($total,0,',','.').' VNĐ</div>';
                echo '<div><a href="/weblaptop/cart.php" class="btn btn-sm btn-outline-secondary me-2">Xem giỏ</a><a href="/weblaptop/checkout.php" class="btn btn-sm btn-primary">Thanh toán</a></div>';
                echo '</div>';
              }
            ?>
          </div>
        </div>
      </div>

      <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="user-menu dropdown">
          <button class="btn btn-light dropdown-toggle" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false"><?php echo htmlspecialchars(isset($_SESSION['user_name'])?$_SESSION['user_name']:'Tài khoản'); ?></button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
            <li><a class="dropdown-item" href="/weblaptop/account.php">Thông tin tài khoản</a></li>
            <li><a class="dropdown-item" href="/weblaptop/orders.php">Đơn hàng của tôi</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/weblaptop/auth/logout.php">Đăng xuất</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="/weblaptop/auth/login.php" class="btn btn-link">Đăng nhập</a>
        <a href="/weblaptop/auth/register.php" class="btn btn-primary">Đăng ký</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Navigation (desktop) -->
<nav class="site-nav bg-white border-top" role="navigation" aria-label="Chức năng chính">
  <div class="container">
    <ul class="nav-list d-flex gap-2 align-items-center py-2">
      <li class="nav-item"><a class="nav-link" href="/weblaptop">Trang chủ</a></li>
      <li class="nav-item nav-dropdown">
        <button class="nav-link btn btn-link dropdown-toggle" aria-expanded="false" aria-controls="catMenu">Danh mục</button>
        <div id="catMenu" class="dropdown-menu p-3">
          <div class="row g-2">
            <div class="col-6 col-md-3"><a class="dropdown-item" href="/weblaptop?category=van-phong">Laptop Văn Phòng</a></div>
            <div class="col-6 col-md-3"><a class="dropdown-item" href="/weblaptop?category=gaming">Laptop Gaming</a></div>
            <div class="col-6 col-md-3"><a class="dropdown-item" href="/weblaptop?category=mong-nhe">Laptop Mỏng Nhẹ</a></div>
            <div class="col-6 col-md-3"><a class="dropdown-item" href="/weblaptop?category=do-hoa">Laptop Đồ Họa</a></div>
          </div>
        </div>
      </li>
      <li class="nav-item"><a class="nav-link" href="/weblaptop/brands.php">Thương hiệu</a></li>
      <li class="nav-item"><a class="nav-link" href="/weblaptop/sale.php">Khuyến mãi</a></li>
      <li class="nav-item"><a class="nav-link" href="/weblaptop/blog.php">Tin tức</a></li>
      <li class="nav-item"><a class="nav-link" href="/weblaptop/contact.php">Hỗ trợ</a></li>
    </ul>
  </div>
</nav>

<!-- Mobile offcanvas menu (controlled by JS) -->
<div id="mobileMenu" class="mobile-offcanvas" aria-hidden="true">
  <div class="mobile-offcanvas-header d-flex align-items-center justify-content-between p-3 border-bottom">
    <strong>Menu</strong>
    <button id="mobileMenuClose" class="btn-close" aria-label="Đóng"></button>
  </div>
  <div class="mobile-offcanvas-body p-3">
    <a href="/weblaptop" class="d-block py-2">Trang chủ</a>
    <a href="/weblaptop?category=van-phong" class="d-block py-2">Laptop Văn Phòng</a>
    <a href="/weblaptop?category=gaming" class="d-block py-2">Laptop Gaming</a>
    <a href="/weblaptop/brands.php" class="d-block py-2">Thương hiệu</a>
    <a href="/weblaptop/sale.php" class="d-block py-2">Khuyến mãi</a>
    <a href="/weblaptop/contact.php" class="d-block py-2">Hỗ trợ</a>
    <div class="mt-3">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="/weblaptop/account.php" class="d-block py-2">Tài khoản</a>
        <a href="/weblaptop/auth/logout.php" class="d-block py-2">Đăng xuất</a>
      <?php else: ?>
        <a href="/weblaptop/auth/login.php" class="d-block py-2">Đăng nhập</a>
        <a href="/weblaptop/auth/register.php" class="d-block py-2">Đăng ký</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
  <div class="hero-banner mb-4">
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="https://via.placeholder.com/1200x350?text=Affordable+Laptops" class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Mẫu mới nhất</h5>
            <p>Laptop hiệu năng cao cho công việc và giải trí.</p>
            <a href="/weblaptop" class="btn btn-primary">Mua ngay</a>
          </div>
        </div>
        <div class="carousel-item">
          <img src="https://via.placeholder.com/1200x350?text=Gaming+Series" class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5>Dòng Gaming</h5>
            <p>Card đồ họa mạnh và màn hình tốc độ cao.</p>
            <a href="/weblaptop" class="btn btn-primary">Khám phá</a>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Trước</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Sau</span>
      </button>
    </div>
  </div>
<?php endif; ?>

<div class="container">