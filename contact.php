<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
}
?>

<style>
    :root {
        --tet-red: #C62222;
        --tet-gold: #D4AF37;
        --tet-dark: #2d3436;
    }
    
    .contact-hero {
        background: linear-gradient(rgba(198, 34, 34, 0.9), rgba(139, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        color: #fff;
        text-align: center;
        border-radius: 0 0 50px 50px;
        margin-bottom: -50px;
    }

    .contact-methods {
        margin-top: -50px;
        position: relative;
        z-index: 10;
    }

    .method-card {
        background: #fff;
        border: none;
        border-radius: 25px;
        padding: 40px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        height: 100%;
        box-shadow: 0 15px 45px rgba(0,0,0,0.05);
    }

    .method-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 25px 60px rgba(198, 34, 34, 0.15);
    }

    .method-icon {
        width: 80px;
        height: 80px;
        background: var(--tet-soft-bg);
        color: var(--tet-red);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        font-size: 2rem;
        margin-bottom: 25px;
        transition: 0.3s;
    }

    .method-card:hover .method-icon {
        background: var(--tet-red);
        color: #fff;
        transform: rotate(15deg);
    }

    .contact-main-section {
        padding: 100px 0;
    }

    .form-container {
        background: #fff;
        border-radius: 30px;
        padding: 50px;
        box-shadow: 0 20px 70px rgba(0,0,0,0.06);
    }

    .map-container {
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 20px 70px rgba(0,0,0,0.06);
        height: 100%;
        min-height: 500px;
    }

    .form-floating > .form-control:focus, .form-floating > .form-control:not(:placeholder-shown) {
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }

    .form-control {
        border-radius: 12px;
        border: 2px solid #f0f0f0;
        padding: 12px 20px;
    }

    .form-control:focus {
        border-color: var(--tet-red);
        box-shadow: none;
    }

    .btn-submit {
        background: var(--tet-red);
        color: #fff;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
        width: 100%;
    }

    .btn-submit:hover {
        background: var(--tet-dark-red);
        transform: scale(1.02);
        box-shadow: 0 10px 25px rgba(198, 34, 34, 0.3);
    }

    .business-hours {
        background: var(--tet-soft-bg);
        border-radius: 20px;
        padding: 25px;
        margin-top: 30px;
    }

    .hour-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
        color: #555;
    }

    .hour-item.active {
        color: var(--tet-red);
        font-weight: 700;
    }

    .badge-online {
        background: #27ae60;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(39, 174, 96, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(39, 174, 96, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(39, 174, 96, 0); }
    }
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <h1 class="display-3 fw-black mb-3">Kết Nối Với GrowTech</h1>
        <p class="lead opacity-75">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn 24/7</p>
    </div>
</section>

<!-- Method Cards -->
<section class="contact-methods">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="method-card">
                    <div class="method-icon"><i class="bi bi-geo-alt"></i></div>
                    <h3>Địa Chỉ</h3>
                    <p class="text-muted">123 Nguyễn Thiện Thành, Phường Hòa Thuận, tỉnh Vĩnh Long</p>
                    <a href="#map" class="text-danger fw-bold text-decoration-none">Tìm đường đi <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="method-card">
                    <div class="method-icon"><i class="bi bi-telephone-inbound"></i></div>
                    <h3>Hotline</h3>
                    <p class="text-muted">Hỗ trợ kỹ thuật & Mua hàng nhanh chóng</p>
                    <h4 class="text-danger fw-black">1900 1234</h4>
                    <span class="small text-success fw-bold"><span class="badge-online"></span> Đang trực tuyến</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="method-card">
                    <div class="method-icon"><i class="bi bi-chat-dots"></i></div>
                    <h3>Hỗ Trợ Trực Tuyến</h3>
                    <p class="text-muted">Chat với đội ngũ hỗ trợ qua Zalo hoặc growtech@gmail.com</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-3">Zalo</a>
                        <a href="mailto:growtech@gmail.com" class="btn btn-outline-danger btn-sm rounded-pill px-3">Email</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Contact Section -->
<section class="contact-main-section">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-6">
                <div class="form-container">
                    <div class="mb-4">
                        <h2 class="fw-black mb-2">Gửi Tin Nhắn</h2>
                        <p class="text-muted">Nếu bạn có thắc mắc gì, đừng ngần ngại để lại lời nhắn.</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-4 p-4 mb-4">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                                <div>
                                    <h5 class="alert-heading fw-bold">Gửi tin nhắn thành công!</h5>
                                    <p class="mb-0">GrowTech sẽ phản hồi cho bạn qua Email sớm nhất có thể.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Họ tên" required>
                                    <label for="name">Họ và tên</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                    <label for="email">Địa chỉ Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Chủ đề">
                                    <label for="subject">Chủ đề (Nội dung cần hỗ trợ)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-4">
                                    <textarea class="form-control" placeholder="Để lại lời nhắn" id="message" name="message" style="height: 150px" required></textarea>
                                    <label for="message">Nội dung chi tiết</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-submit">
                                    Gửi Yêu Cầu <i class="bi bi-send-fill ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="business-hours shadow-sm">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-danger"></i> Giờ làm việc</h5>
                        <div class="hour-item active">
                            <span>Thứ 2 - Thứ 6:</span>
                            <span>08:00 - 21:00</span>
                        </div>
                        <div class="hour-item">
                            <span>Thứ 7 - CN:</span>
                            <span>08:30 - 20:00</span>
                        </div>
                        <div class="hour-item text-danger fw-bold small mt-2">
                            <span>* Nghỉ lễ Tết theo quy định</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Area -->
            <div class="col-lg-6" id="map">
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps?q=123%20Nguy%E1%BB%85n%20Thi%E1%BB%87n%20Th%C3%A0nh%2C%20H%C3%B2a%20Thu%E1%BA%ADn%2C%20V%C3%A9nh%20Long&output=embed" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


