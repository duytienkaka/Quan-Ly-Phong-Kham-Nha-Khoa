<?php
$showForm = !empty($needProfile);
$isEditing = $isEditing ?? false;
$showInfo = !$isEditing;
?>

<?php if ($showForm): ?>
<div class="alert-warning">
    <div class="alert-icon">⚠️</div>
    <div class="alert-content">
        <h3>Hoàn thiện thông tin cá nhân</h3>
        <p>Vui lòng bổ sung thông tin để chúng tôi hỗ trợ bạn tốt hơn.</p>
    </div>
</div>
<?php endif; ?>

<!-- Profile Form -->
<?php if ($showForm): ?>
<section class="profile-section">
    <h2>Bổ sung thông tin cá nhân</h2>
    
    <form method="post" action="index.php?controller=patient&action=saveProfile" class="profile-form">
        <div class="form-grid">
            <div class="form-row">
                <label>Họ và tên <span class="required">*</span></label>
                <input type="text" name="full_name"
                       value="<?= htmlspecialchars($patient['full_name'] ?? $user['full_name'] ?? $user['username']) ?>"
                       required>
            </div>

            <div class="form-row">
                <label>Giới tính</label>
                <select name="gender">
                    <option value="">-- Chọn --</option>
                    <option value="M" <?= (isset($patient['gender']) && $patient['gender'] === 'M') ? 'selected' : '' ?>>Nam</option>
                    <option value="F" <?= (isset($patient['gender']) && $patient['gender'] === 'F') ? 'selected' : '' ?>>Nữ</option>
                    <option value="O" <?= (isset($patient['gender']) && $patient['gender'] === 'O') ? 'selected' : '' ?>>Khác</option>
                </select>
            </div>

            <div class="form-row">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth"
                       value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>">
            </div>

            <div class="form-row">
                <label>Số điện thoại <span class="required">*</span></label>
                <input type="tel" name="phone"
                       value="<?= htmlspecialchars($patient['phone'] ?? '') ?>"
                       placeholder="Nhập số điện thoại">
            </div>

            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($patient['email'] ?? $user['email'] ?? '') ?>">
            </div>

            <div class="form-row">
                <label>Địa chỉ <span class="required">*</span></label>
                <input type="text" name="address"
                       value="<?= htmlspecialchars($patient['address'] ?? '') ?>"
                       placeholder="Nhập địa chỉ">
            </div>
        </div>

        <div class="form-row form-full">
            <label>Ghi chú (dị ứng thuốc, bệnh nền...)</label>
            <textarea name="note" rows="4"><?= htmlspecialchars($patient['note'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-primary btn-large">Lưu thông tin</button>
    </form>
</section>
<?php else: ?>

<section class="patient-dashboard">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Xin chào, <?= htmlspecialchars($patient['full_name'] ?? $user['full_name'] ?? $user['username']) ?>!</h1>
            <p>Chào mừng bạn quay lại Nha Khoa Smile</p>
        </div>
        <button class="edit-profile-btn" onclick="window.location.href='index.php?controller=patient&action=edit'">
            ✎ Sửa thông tin
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <p class="stat-label">Lịch hẹn sắp tới</p>
                <p class="stat-number"><?= htmlspecialchars($upcomingCount ?? 0) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <p class="stat-label">Lịch sử khám</p>
                <p class="stat-number"><?= htmlspecialchars($recordsCount ?? 0) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <p class="stat-label">Hóa đơn chưa thanh toán</p>
                <p class="stat-number"><?= htmlspecialchars($unpaidInvoicesCount ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Patient Information -->
    <div class="info-section">
        <h2>Thông tin cá nhân</h2>
        <div class="info-grid">
            <div class="info-card">
                <span class="info-label">Họ và tên</span>
                <span class="info-value"><?= htmlspecialchars($patient['full_name'] ?? 'Chưa cập nhật') ?></span>
            </div>
            <div class="info-card">
                <span class="info-label">Giới tính</span>
                <span class="info-value">
                    <?php
                    $gender = $patient['gender'] ?? '';
                    $genderText = [
                        'M' => 'Nam',
                        'F' => 'Nữ',
                        'O' => 'Khác'
                    ];
                    echo htmlspecialchars($genderText[$gender] ?? 'Chưa cập nhật');
                    ?>
                </span>
            </div>
            <div class="info-card">
                <span class="info-label">Ngày sinh</span>
                <span class="info-value">
                    <?php
                    $dob = $patient['date_of_birth'] ?? '';
                    echo !empty($dob) ? date('d/m/Y', strtotime($dob)) : 'Chưa cập nhật';
                    ?>
                </span>
            </div>
            <div class="info-card">
                <span class="info-label">Số điện thoại</span>
                <span class="info-value"><?= htmlspecialchars($patient['phone'] ?? 'Chưa cập nhật') ?></span>
            </div>
            <div class="info-card">
                <span class="info-label">Email</span>
                <span class="info-value"><?= htmlspecialchars($patient['email'] ?? $user['email'] ?? 'Chưa cập nhật') ?></span>
            </div>
            <div class="info-card">
                <span class="info-label">Địa chỉ</span>
                <span class="info-value"><?= htmlspecialchars($patient['address'] ?? 'Chưa cập nhật') ?></span>
            </div>
        </div>
            
        <?php if (!empty($patient['note'])): ?>
        <div class="note-card">
            <h3>Ghi chú sức khỏe</h3>
            <p><?= htmlspecialchars($patient['note']) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="actions-section">
        <h2>Các tác vụ nhanh</h2>
        <div class="actions-grid">
            <a href="index.php?controller=patient&action=booking" class="action-card">
                <div class="action-icon">📅</div>
                <h3>Đặt lịch khám</h3>
                <p>Chọn ngày giờ phù hợp</p>
            </a>
            <a href="index.php?controller=patient&action=appointments" class="action-card">
                <div class="action-icon">📋</div>
                <h3>Lịch hẹn của tôi</h3>
                <p>Xem lịch khám sắp tới</p>
            </a>
            <a href="index.php?controller=patient&action=history" class="action-card">
                <div class="action-icon">📂</div>
                <h3>Lịch sử điều trị</h3>
                <p>Xem các lần khám trước</p>
            </a>
            <a href="index.php?controller=patient&action=invoices" class="action-card">
                <div class="action-icon">💳</div>
                <h3>Hóa đơn</h3>
                <p>Xem chi tiết thanh toán</p>
            </a>
        </div>
    </div>
</section>

<?php endif; ?>
