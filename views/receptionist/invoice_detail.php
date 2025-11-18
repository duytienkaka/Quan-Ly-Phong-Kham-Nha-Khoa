<section class="rc-dashboard">
    <h1>Chi tiết hóa đơn #<?= (int)$invoiceView['invoice_id'] ?></h1>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box">
            <?= htmlspecialchars($errorView) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successView)): ?>
        <div class="form-success-box">
            <?= htmlspecialchars($successView) ?>
        </div>
    <?php endif; ?>

    <!-- Thông tin bệnh nhân -->
    <div class="rc-panel">
        <div class="rc-panel-header">
            <h2>Bệnh nhân</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Họ tên</label>
                <span><?= htmlspecialchars($invoiceView['patient_name']) ?></span>
            </div>
            <div class="info-item">
                <label>SĐT</label>
                <span><?= htmlspecialchars($invoiceView['patient_phone'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Email</label>
                <span><?= htmlspecialchars($invoiceView['patient_email'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Địa chỉ</label>
                <span><?= htmlspecialchars($invoiceView['patient_address'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <!-- Thông tin khám -->
    <div class="rc-panel" style="margin-top: 12px;">
        <div class="rc-panel-header">
            <h2>Thông tin khám</h2>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Bác sĩ</label>
                <span><?= htmlspecialchars($invoiceView['doctor_name'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Ngày khám</label>
                <span>
                    <?php
                    if (!empty($invoiceView['visit_date'])) {
                        echo date('H:i d/m/Y', strtotime($invoiceView['visit_date']));
                    } else {
                        echo '—';
                    }
                    ?>
                </span>
            </div>
        </div>

        <div class="info-block">
            <label>Lý do khám</label>
            <p><?= htmlspecialchars($invoiceView['chief_complaint'] ?? '—') ?></p>
        </div>
        <div class="info-block">
            <label>Chẩn đoán</label>
            <p><?= htmlspecialchars($invoiceView['diagnosis'] ?? '—') ?></p>
        </div>
        <div class="info-block">
            <label>Kế hoạch điều trị</label>
            <p><?= nl2br(htmlspecialchars($invoiceView['treatment_plan'] ?? '—')) ?></p>
        </div>
    </div>

    <!-- Thông tin hóa đơn -->
    <div class="rc-panel" style="margin-top: 12px;">
        <div class="rc-panel-header">
            <h2>Thông tin hóa đơn</h2>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Ngày lập hóa đơn</label>
                <span>
                    <?php
                    echo date('H:i d/m/Y', strtotime($invoiceView['created_at']));
                    ?>
                </span>
            </div>
            <div class="info-item">
                <label>Trạng thái thanh toán</label>
                <span>
                    <?php
                    $ps = $invoiceView['payment_status'];
                    if ($ps === 'UNPAID')  echo '<span class="tag tag-pending">Chưa thanh toán</span>';
                    elseif ($ps === 'PAID')    echo '<span class="tag tag-done">Đã thanh toán</span>';
                    elseif ($ps === 'PARTIAL') echo '<span class="tag tag-inprogress">Thanh toán một phần</span>';
                    else                      echo '<span class="tag">' . htmlspecialchars($ps) . '</span>';
                    ?>
                </span>
            </div>
            <div class="info-item">
                <label>Phương thức thanh toán</label>
                <span><?= htmlspecialchars($invoiceView['payment_method'] ?? '—') ?></span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Tổng tiền</label>
                <span><?= number_format($invoiceView['total_amount'], 0, ',', '.') ?> đ</span>
            </div>
            <div class="info-item">
                <label>Giảm giá</label>
                <span><?= number_format($invoiceView['discount'], 0, ',', '.') ?> đ</span>
            </div>
            <div class="info-item">
                <label>Khách phải trả</label>
                <span><strong><?= number_format($invoiceView['final_amount'], 0, ',', '.') ?> đ</strong></span>
            </div>
        </div>

        <div class="info-block">
            <label>Ghi chú hóa đơn</label>
            <p><?= nl2br(htmlspecialchars($invoiceView['note'] ?? '')) ?></p>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Lưu cập nhật</button>
        <a href="index.php?controller=receptionist&action=invoices" class="btn-secondary">
            ← Quay lại danh sách
        </a>
    </div>
    </form>
    </div>
</section>