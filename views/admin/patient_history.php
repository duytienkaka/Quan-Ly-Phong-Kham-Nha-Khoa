<section class="admin-doctors">
    <h1>Lịch sử khám: <?= htmlspecialchars($patientView['full_name']) ?></h1>
    <p class="subtitle">
        Tài khoản: <?= htmlspecialchars($patientView['username'] ?? '—') ?> ·
        SĐT: <?= htmlspecialchars($patientView['phone'] ?? '—') ?> ·
        Email: <?= htmlspecialchars($patientView['email'] ?? '—') ?>
    </p>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Thông tin tổng quan</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Ngày sinh</div>
                <div class="stat-value">
                    <?= htmlspecialchars($patientView['date_of_birth'] ?? '—') ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Địa chỉ</div>
                <div class="stat-value">
                    <?= htmlspecialchars($patientView['address'] ?? '—') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-panel" style="margin-top:16px;">
        <div class="panel-header">
            <h2>Lịch sử khám chi tiết</h2>
        </div>

        <?php if (empty($recordsView)): ?>
            <p>Bệnh nhân chưa có lần khám nào.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ngày khám</th>
                        <th>Bác sĩ</th>
                        <th>Lý do khám</th>
                        <th>Chẩn đoán</th>
                        <th>Kế hoạch điều trị</th>
                        <th>Hẹn tái khám</th>
                        <th>Hóa đơn</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recordsView as $r): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($r['visit_date']) ?>
                            <?php if (!empty($r['appointment_date'])): ?>
                                <br><small>(Đặt lịch: <?= htmlspecialchars($r['appointment_date']) ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['doctor_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['chief_complaint'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['diagnosis'] ?? '') ?></td>
                        <td>
                            <?= nl2br(htmlspecialchars($r['treatment_plan'] ?? '')) ?>
                            <?php if (!empty($r['extra_note'])): ?>
                                <br><small>Ghi chú: <?= nl2br(htmlspecialchars($r['extra_note'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['suggested_next_visit'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($r['invoice_id'])): ?>
                                Mã HĐ: #<?= (int)$r['invoice_id'] ?><br>
                                Số tiền: <?= number_format($r['final_amount'], 0, ',', '.') ?> đ<br>
                                Trạng thái: <?= htmlspecialchars($r['payment_status']) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="form-actions" style="margin-top:12px;">
            <a href="index.php?controller=admin&action=patients" class="btn-secondary">← Quay lại danh sách bệnh nhân</a>
        </div>
    </div>
</section>
