<?php
$mode   = $mode ?? 'create';
$isEdit = ($mode === 'edit');

$actionUrl = $isEdit
    ? "index.php?controller=admin&action=editService&id=" . urlencode($_GET['id'] ?? '')
    : "index.php?controller=admin&action=createService";
?>

<section class="admin-user-form">
    <h1><?= $isEdit ? 'Chỉnh sửa dịch vụ' : 'Thêm dịch vụ' ?></h1>
    <p class="subtitle">
        Quản lý thông tin dịch vụ nha khoa và đơn giá.
    </p>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box">
            <?= htmlspecialchars($errorView) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $actionUrl ?>" class="form-card">
        <div class="form-group">
            <label>Tên dịch vụ <span class="required">*</span></label>
            <input type="text" name="service_name" required
                   value="<?= htmlspecialchars($oldData['service_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Mô tả</label>
            <textarea name="description" rows="3"
                      placeholder="Mô tả ngắn về dịch vụ..."><?= htmlspecialchars($oldData['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row-2col">
            <div class="form-group">
                <label>Đơn giá (VNĐ) <span class="required">*</span></label>
                <input type="number" name="unit_price" min="0" step="1000" required
                       value="<?= htmlspecialchars($oldData['unit_price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Đơn vị tính</label>
                <input type="text" name="unit" placeholder="VD: lần, răng, hàm..."
                       value="<?= htmlspecialchars($oldData['unit'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1"
                    <?= (!isset($oldData['is_active']) || $oldData['is_active']) ? 'checked' : '' ?>>
                Đang sử dụng
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <?= $isEdit ? 'Lưu thay đổi' : 'Thêm dịch vụ' ?>
            </button>
            <a href="index.php?controller=admin&action=services" class="btn-secondary">Hủy</a>
        </div>
    </form>
</section>
