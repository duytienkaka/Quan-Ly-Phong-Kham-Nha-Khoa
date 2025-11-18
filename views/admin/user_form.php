<?php
$mode = $mode ?? 'create';
$isEdit = ($mode === 'edit');

$formAction = $isEdit
    ? "index.php?controller=admin&action=editUser&id=" . urlencode($old['user_id'])
    : "index.php?controller=admin&action=createUser";

$title = $isEdit ? 'Chỉnh sửa tài khoản' : 'Thêm tài khoản mới';
?>

<section class="admin-user-form">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p class="subtitle">
        <?= $isEdit ? 'Cập nhật thông tin tài khoản.' : 'Tạo tài khoản cho admin, lễ tân, bác sĩ hoặc bệnh nhân.' ?>
    </p>

    <?php if (!empty($error)): ?>
        <div class="form-error-box">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $formAction ?>" class="form-card">
        <div class="form-row-2col">
            <div class="form-group">
                <label>Tên đăng nhập <span class="required">*</span></label>
                <input type="text" name="username" required
                    value="<?= htmlspecialchars($old['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Họ và tên <span class="required">*</span></label>
                <input type="text" name="full_name" required
                    value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row-2col">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone"
                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row-2col">
            <div class="form-group">
                <label>Vai trò</label>
                <select name="role">
                    <option value="admin" <?= (($old['role'] ?? '') === 'admin' ? 'selected' : '') ?>>Admin</option>
                    <option value="receptionist" <?= (($old['role'] ?? '') === 'receptionist' ? 'selected' : '') ?>>Lễ tân</option>
                    <option value="doctor" <?= (($old['role'] ?? '') === 'doctor' ? 'selected' : '') ?>>Bác sĩ</option>
                    <option value="patient" <?= (($old['role'] ?? '') === 'patient' ? 'selected' : '') ?>>Bệnh nhân</option>
                </select>
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="1" <?= (($old['status'] ?? 1) == 1 ? 'selected' : '') ?>>Hoạt động</option>
                    <option value="0" <?= (($old['status'] ?? 1) == 0 ? 'selected' : '') ?>>Khóa</option>
                </select>
            </div>
        </div>

        <?php if (!$isEdit): ?>
            <div class="form-row-2col">
                <div class="form-group">
                    <label>Mật khẩu <span class="required">*</span></label>
                    <input type="text" name="password" required
                        value="<?= htmlspecialchars($old['password'] ?? '123456') ?>">
                    <small class="hint">Hiện tại đang lưu dạng plain-text, sau này có thể chuyển sang mã hóa.</small>
                </div>
                <div class="form-group"></div>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <?= $isEdit ? 'Cập nhật' : 'Lưu tài khoản' ?>
            </button>
            <a href="index.php?controller=admin&action=users" class="btn-secondary">Hủy</a>
        </div>
    </form>
</section>