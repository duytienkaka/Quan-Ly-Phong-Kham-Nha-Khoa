<?php
// views/layouts/public_layout.php
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dental Clinic'; ?></title>
    <link rel="stylesheet" href="/dental_clinic/public/css/public.css?v=<?= filemtime(__DIR__.'/../../public/css/public.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="logo">Nha Khoa <span>Smile</span></div>
    <nav class="main-nav">
        <a href="index.php">Trang chủ</a>
        <a href="index.php?controller=home&action=services">Dịch vụ</a>
        <a href="index.php?controller=home&action=doctors">Đội ngũ bác sĩ</a>
        <a href="index.php?controller=home&action=contact">Liên hệ</a>
        <a href="index.php?controller=auth&action=login" class="btn-login">Đăng nhập</a>
    </nav>
</header>

<main class="site-main">
    <?php
    if (isset($view)) {
        include $view;
    }
    ?>
</main>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3 class="footer-title">Về chúng tôi</h3>
            <div class="footer-logo">Nha Khoa <span>Smile</span></div>
            <p class="footer-description">Phòng khám nha khoa uy tín hàng đầu với đội ngũ bác sĩ giàu kinh nghiệm và công nghệ hiện đại. Chúng tôi cam kết mang lại cho bạn dịch vụ chất lượng tốt nhất.</p>
            <div class="social-links">
                <a href="#" title="Facebook" class="social-icon">f</a>
                <a href="#" title="Instagram" class="social-icon">📷</a>
                <a href="#" title="Zalo" class="social-icon">Z</a>
            </div>
        </div>

        <!-- Services Section -->
        <div class="footer-section">
            <h3 class="footer-title">Dịch vụ</h3>
            <ul class="footer-links">
                <li><a href="#">Khám và vệ sinh</a></li>
                <li><a href="#">Tẩy trắng răng</a></li>
                <li><a href="#">Trám và điều trị</a></li>
                <li><a href="#">Cấy ghép implant</a></li>
                <li><a href="#">Chỉnh nha</a></li>
                <li><a href="#">Điều trị nội nha</a></li>
            </ul>
        </div>

        <!-- Quick Links Section -->
        <div class="footer-section">
            <h3 class="footer-title">Liên kết nhanh</h3>
            <ul class="footer-links">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="#">Đội ngũ bác sĩ</a></li>
                <li><a href="#">Bảng giá</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Câu hỏi thường gặp</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="footer-section">
            <h3 class="footer-title">Liên hệ</h3>
            <div class="contact-info">
                <div class="contact-item">
                    <span class="contact-icon">📍</span>
                    <div>
                        <p class="contact-label">Địa chỉ</p>
                        <p>123 Đường Nguyễn Huệ, Quận 1, TP.HCM</p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <div>
                        <p class="contact-label">Điện thoại</p>
                        <p><a href="tel:0123456789">0123 456 789</a></p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <div>
                        <p class="contact-label">Email</p>
                        <p><a href="mailto:info@nhakhoasmile.com">info@nhakhoasmile.com</a></p>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">🕐</span>
                    <div>
                        <p class="contact-label">Giờ làm việc</p>
                        <p>T2-T6: 08:00 - 20:00<br>T7-CN: 09:00 - 18:00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; <?= date('Y'); ?> Nha Khoa Smile. Tất cả các quyền được bảo lưu.</p>
        <div class="footer-bottom-links">
            <a href="#">Điều khoản sử dụng</a>
            <span>|</span>
            <a href="#">Chính sách bảo mật</a>
            <span>|</span>
            <a href="#">Liên hệ</a>
        </div>
    </div>
</footer>

<script src="/dental_clinic/public/js/public.js"></script>
</body>
</html>
