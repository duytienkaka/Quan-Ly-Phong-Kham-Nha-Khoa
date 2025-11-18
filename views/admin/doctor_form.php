<section class="admin-user-form">
    <h1>Cập nhật thông tin bác sĩ</h1>
    <p class="subtitle">
        Thêm/chỉnh sửa thông tin chuyên khoa, kinh nghiệm và ghi chú cho bác sĩ.
    </p>

    <?php if (!empty($error)): ?>
        <div class="form-error-box">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form-card">
        <div class="form-group">
            <label>Tài khoản</label>
            <input type="text"
                   value="<?= htmlspecialchars($old['username']) ?> (<?= htmlspecialchars($old['doctor_name']) ?>)"
                   disabled>
            <small class="hint">
                Muốn đổi tên hiển thị / email / sđt, hãy chỉnh ở phần "Người dùng / Tài khoản".
            </small>
        </div>

        <div class="form-row-2col">
            <div class="form-group">
                <label>Chuyên khoa</label>
                <input type="text" name="specialization"
                       value="<?= htmlspecialchars($old['specialization'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Số năm kinh nghiệm</label>
                <input type="number" name="experience_years" min="0"
                       value="<?= htmlspecialchars($old['experience_years'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" rows="3"><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Lưu thông tin
            </button>
            <a href="index.php?controller=admin&action=doctors" class="btn-secondary">Hủy</a>
        </div>
    </form>
</section>
