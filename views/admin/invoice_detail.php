<section class="admin-doctors">
    <h1>Chi tiết hóa đơn #<?= (int)$invoiceView['invoice_id'] ?></h1>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box">
            <?= htmlspecialchars($errorView) ?>
        </div>
    <?php endif; ?>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Thông tin bệnh nhân</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Họ tên</div>
                <div class="stat-value"><?= htmlspecialchars($invoiceView['patient_name']) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">SĐT</div>
                <div class="stat-value"><?= htmlspecialchars($invoiceView['patient_phone'] ?? '—') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Email</div>
                <div class="stat-value"><?= htmlspecialchars($invoiceView['patient_email'] ?? '—') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Địa chỉ</div>
                <div class="stat-value"><?= htmlspecialchars($invoiceView['patient_address'] ?? '—') ?></div>
            </div>
        </div>
    </div>

    <div class="admin-panel" style="margin-top:16px;">
        <div class="panel-header">
            <h2>Thông tin khám</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Bác sĩ</label>
                <span><?= htmlspecialchars($invoiceView['doctor_name'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Ngày khám</label>
                <span><?= htmlspecialchars($invoiceView['visit_date'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Lý do khám</label>
                <span><?= htmlspecialchars($invoiceView['chief_complaint'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <label>Chẩn đoán</label>
                <span><?= htmlspecialchars($invoiceView['diagnosis'] ?? '') ?></span>
            </div>
        </div>
        <div class="info-block">
            <label>Kế hoạch điều trị</label>
            <p><?= nl2br(htmlspecialchars($invoiceView['treatment_plan'] ?? '')) ?></p>
        </div>
        <?php if (!empty($invoiceView['extra_note'])): ?>
            <div class="info-block">
                <label>Ghi chú thêm</label>
                <p><?= nl2br(htmlspecialchars($invoiceView['extra_note'])) ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($invoiceView['suggested_next_visit'])): ?>
            <div class="info-block">
                <label>Ngày hẹn tái khám dự kiến</label>
                <p><?= htmlspecialchars($invoiceView['suggested_next_visit']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-panel" style="margin-top:16px;">
        <div class="panel-header">
            <h2>Thông tin thanh toán</h2>
        </div>

        <div class="price-row">
            <span>Tổng tiền</span>
            <span><?= number_format($invoiceView['total_amount'], 0, ',', '.') ?> đ</span>
        </div>
        <div class="price-row">
            <span>Giảm trừ</span>
            <span><?= number_format($invoiceView['discount'], 0, ',', '.') ?> đ</span>
        </div>
        <div class="price-row total">
            <span>Thành tiền</span>
            <span><?= number_format($invoiceView['final_amount'], 0, ',', '.') ?> đ</span>
        </div>

        <form method="post" class="form-card" style="margin-top:16px;">
            <div class="form-row-2col">
                <div class="form-group">
                    <label>Trạng thái thanh toán</label>
                    <select name="payment_status">
                        <option value="UNPAID"  <?= ($invoiceView['payment_status'] === 'UNPAID'  ? 'selected' : '') ?>>Chưa thanh toán</option>
                        <option value="PARTIAL" <?= ($invoiceView['payment_status'] === 'PARTIAL' ? 'selected' : '') ?>>Thanh toán một phần</option>
                        <option value="PAID"    <?= ($invoiceView['payment_status'] === 'PAID'    ? 'selected' : '') ?>>Đã thanh toán</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phương thức thanh toán</label>
                    <input type="text" name="payment_method"
                           placeholder="VD: tiền mặt, chuyển khoản..."
                           value="<?= htmlspecialchars($invoiceView['payment_method'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Ghi chú trên hóa đơn</label>
                <textarea name="invoice_note" rows="3"><?= htmlspecialchars($invoiceView['invoice_note'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Lưu thay đổi</button>
                <a href="index.php?controller=admin&action=invoices" class="btn-secondary">← Quay lại danh sách</a>
            </div>
        </form>
    </div>
</section>
