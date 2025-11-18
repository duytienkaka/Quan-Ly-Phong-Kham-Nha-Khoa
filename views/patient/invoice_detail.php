<section class="patient-section">
    <h2>Chi tiết hóa đơn #<?= (int)$invoiceView['invoice_id'] ?></h2>

    <div class="invoice-card">
        <div class="invoice-header">
            <div>
                <div class="invoice-label">Ngày lập</div>
                <div class="invoice-value">
                    <?= htmlspecialchars($invoiceView['created_at']) ?>
                </div>
            </div>
            <div>
                <div class="invoice-label">Trạng thái thanh toán</div>
                <div class="invoice-value">
                    <?php
                        $st = $invoiceView['payment_status'];
                        if ($st === 'PAID')      echo '<span class="tag tag-active">Đã thanh toán</span>';
                        elseif ($st === 'PARTIAL') echo '<span class="tag tag-warn">Thanh toán một phần</span>';
                        else                     echo '<span class="tag tag-inactive">Chưa thanh toán</span>';
                    ?>
                </div>
            </div>
            <div>
                <div class="invoice-label">Phương thức</div>
                <div class="invoice-value">
                    <?= htmlspecialchars($invoiceView['payment_method'] ?? 'Chưa cập nhật') ?>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <h3>Thông tin khám</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Bác sĩ phụ trách</label>
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
                    <label>Ghi chú thêm của bác sĩ</label>
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

        <div class="invoice-footer">
            <h3>Thanh toán</h3>
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

            <?php if (!empty($invoiceView['invoice_note'])): ?>
                <div class="info-block">
                    <label>Ghi chú trên hóa đơn</label>
                    <p><?= nl2br(htmlspecialchars($invoiceView['invoice_note'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="invoice-actions">
                <a href="index.php?controller=patient&action=invoices" class="btn-secondary">
                    ← Quay lại danh sách hóa đơn
                </a>
            </div>
        </div>
    </div>
</section>
