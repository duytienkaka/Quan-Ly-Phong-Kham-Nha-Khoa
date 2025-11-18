<?php
?>
<section class="contact-page">
    <div class="contact-list">
        <h2>Liên hệ với chúng tôi</h2>
        <p class="section-subtitle">Chúng tôi luôn sẵn sàng trả lời mọi câu hỏi của bạn</p>

        <div style="margin-top:28px; display:grid; gap:20px;">
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
                    <p><a href="tel:0123456789" style="color:var(--primary);font-weight:600;">0123 456 789</a></p>
                </div>
            </div>
            <div class="contact-item">
                <span class="contact-icon">✉️</span>
                <div>
                    <p class="contact-label">Email</p>
                    <p><a href="mailto:info@nhakhoasmile.com" style="color:var(--primary);font-weight:600;">info@nhakhoasmile.com</a></p>
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

    <div style="display:flex;flex-direction:column;gap:20px;">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.48203496957!2d106.70027307488247!3d10.774344789374291!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f471fae0893%3A0x4a0c6395cc27f990!2zMTIzIE5ndXnDqsyDbiBIdcOqzKMsIELhur9uIE5naGUsIFF14bqt4bqr1p4gMSwgVGjDoG5oIHBo4buRIEjhu5MgQ2jDrSBNaW5oLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1763453217083!5m2!1svi!2s" width="100%" height="340" style="border:0;border-radius:var(--radius);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

        <div style="background:var(--surface);padding:24px;border-radius:var(--radius);box-shadow:var(--shadow-sm);">
            <h3 style="margin-bottom:16px;color:var(--text);font-size:18px;">Gửi yêu cầu của bạn</h3>
            <form method="post" action="index.php?controller=home&action=contact" class="contact-form-grid">
                <input type="hidden" name="action_type" value="contact_submit">
                <div class="form-row">
                    <label>Họ tên *</label>
                    <input type="text" name="name" required placeholder="Nhập họ tên">
                </div>
                <div class="form-row">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="Nhập email">
                </div>
                <div class="form-row" style="grid-column: 1 / -1;">
                    <label>Điện thoại</label>
                    <input type="tel" name="phone" placeholder="Nhập số điện thoại">
                </div>
                <div class="form-row" style="grid-column: 1 / -1;">
                    <label>Nội dung *</label>
                    <textarea name="message" rows="4" required placeholder="Viết nội dung tin nhắn của bạn..."></textarea>
                </div>
                <div class="form-row" style="grid-column: 1 / -1; margin-top:8px;">
                    <button type="submit" class="btn-primary btn-large">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</section>
