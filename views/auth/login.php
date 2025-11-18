<?php
?>
<div class="auth-wrapper">
    <div class="auth-container-split">
        <!-- Left Side - Form Container -->
        <div class="auth-form-side">
            <div class="form-toggle">
                <button type="button" class="toggle-btn active" data-form="login">Đăng Nhập</button>
                <button type="button" class="toggle-btn" data-form="register">Đăng Ký</button>
            </div>

            <!-- Login Form -->
            <div class="auth-form login-form active-form">
                <div class="form-header">
                    <h2>Chào mừng quay lại</h2>
                    <p>Đăng nhập để quản lý lịch khám và hồ sơ sức khỏe của bạn</p>
                </div>

                <?php if (!empty($error) && (strpos($_POST['action_type'] ?? '', 'register') === false)): ?>
                    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="index.php?controller=auth&action=login">
                    <input type="hidden" name="action_type" value="login">
                    
                    <div class="form-row">
                        <label for="login-username">Tên đăng nhập</label>
                        <input type="text" id="login-username" name="username" placeholder="Nhập tên đăng nhập" required>
                    </div>

                    <div class="form-row">
                        <label for="login-password">Mật khẩu</label>
                        <input type="password" id="login-password" name="password" placeholder="Nhập mật khẩu" required>
                    </div>

                    <div class="form-remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ tôi</label>
                    </div>

                    <button type="submit" class="btn-primary btn-large btn-full">Đăng Nhập</button>
                </form>

                <div class="form-footer">
                    <p>Chưa có tài khoản? <button type="button" class="link-btn" onclick="switchForm('register')">Đăng ký ngay</button></p>
                </div>
            </div>

            <!-- Register Form -->
            <div class="auth-form register-form">
                <div class="form-header">
                    <h2>Tạo tài khoản mới</h2>
                    <p>Đăng ký để bắt đầu đặt lịch khám nha khoa</p>
                </div>

                <?php if (!empty($error) && (strpos($_POST['action_type'] ?? '', 'register') !== false)): ?>
                    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="index.php?controller=auth&action=login">
                    <input type="hidden" name="action_type" value="register">

                    <div class="form-row">
                        <label for="register-username">Tên đăng nhập</label>
                        <input type="text" id="register-username" name="username" placeholder="Chọn tên đăng nhập" required>
                    </div>

                    <div class="form-row">
                        <label for="register-password">Mật khẩu</label>
                        <input type="password" id="register-password" name="password" placeholder="Mật khẩu ít nhất 6 ký tự" required>
                    </div>

                    <div class="form-row">
                        <label for="register-password2">Nhập lại mật khẩu</label>
                        <input type="password" id="register-password2" name="password2" placeholder="Nhập lại mật khẩu" required>
                    </div>

                    <div class="form-agree">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">Tôi đồng ý với <a href="#">điều khoản sử dụng</a></label>
                    </div>

                    <button type="submit" class="btn-primary btn-large btn-full">Đăng Ký</button>
                </form>

                <div class="form-footer">
                    <p>Đã có tài khoản? <button type="button" class="link-btn" onclick="switchForm('login')">Đăng nhập</button></p>
                </div>
            </div>
        </div>

        <!-- Right Side - Image/Illustration -->
        <div class="auth-image-side">
            <div class="image-content">
                <div class="image-icon">🦷</div>
                <h3>Nha Khoa Smile</h3>
                <p>Chăm sóc răng miệng chuyên nghiệp</p>
                <ul class="feature-list">
                    <li>✓ Đặt lịch khám dễ dàng</li>
                    <li>✓ Quản lý hồ sơ sức khỏe</li>
                    <li>✓ Theo dõi lịch sử điều trị</li>
                    <li>✓ Xem hóa đơn trực tuyến</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function switchForm(formType) {
    const loginForm = document.querySelector('.login-form');
    const registerForm = document.querySelector('.register-form');
    const loginBtn = document.querySelector('[data-form="login"]');
    const registerBtn = document.querySelector('[data-form="register"]');

    if (formType === 'register') {
        loginForm.classList.remove('active-form');
        registerForm.classList.add('active-form');
        loginBtn.classList.remove('active');
        registerBtn.classList.add('active');
    } else {
        registerForm.classList.remove('active-form');
        loginForm.classList.add('active-form');
        registerBtn.classList.remove('active');
        loginBtn.classList.add('active');
    }
}

// Toggle buttons
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        switchForm(this.dataset.form);
    });
});
</script>
