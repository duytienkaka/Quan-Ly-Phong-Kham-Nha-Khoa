<?php $ap = $appointmentView; ?>

<section class="dc-appointment-detail">
    <h1>Khám bệnh - Lịch hẹn #<?= (int)$ap['appointment_id'] ?></h1>
    <p class="dc-subtitle">Cập nhật thông tin khám và hồ sơ bệnh nhân.</p>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box"><?= htmlspecialchars($errorView) ?></div>
    <?php endif; ?>
    <?php if (!empty($successView)): ?>
        <div class="form-success-box"><?= htmlspecialchars($successView) ?></div>
    <?php endif; ?>

    <div class="dc-panel">
        <div class="dc-panel-header">
            <h2>Thông tin bệnh nhân</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Họ tên</label>
                <span><?= htmlspecialchars($ap['patient_name']) ?></span>
            </div>
            <div class="info-item">
                <label>Số điện thoại</label>
                <span><?= htmlspecialchars($ap['patient_phone'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Email</label>
                <span><?= htmlspecialchars($ap['patient_email'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Địa chỉ</label>
                <span><?= htmlspecialchars($ap['patient_address'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <div class="dc-panel">
        <div class="dc-panel-header">
            <h2>Thông tin lịch hẹn</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Ngày giờ khám</label>
                <span><?= date('H:i — d/m/Y', strtotime($ap['appointment_date'])) ?></span>
            </div>
            <div class="info-item">
                <label>Trạng thái</label>
                <span>
                    <?php
                        $st = $ap['appointment_status'];
                        if ($st === 'WAITING')      echo '<span class="tag tag-waiting">Chờ khám</span>';
                        elseif ($st === 'IN_PROGRESS') echo '<span class="tag tag-inprogress">Đang khám</span>';
                        elseif ($st === 'COMPLETED')   echo '<span class="tag tag-done">Hoàn thành</span>';
                        elseif ($st === 'CANCELLED')   echo '<span class="tag tag-cancel">Đã hủy</span>';
                        elseif ($st === 'NO_SHOW')     echo '<span class="tag tag-noshow">Không đến</span>';
                        else                           echo '<span class="tag">'.htmlspecialchars($st).'</span>';
                    ?>
                </span>
            </div>
        </div>
        <div class="info-block">
            <label>Ghi chú từ lễ tân</label>
            <p><?= nl2br(htmlspecialchars($ap['appointment_note'] ?? 'Không có')) ?></p>
        </div>
    </div>

    <div class="dc-panel">
        <div class="dc-panel-header">
            <h2>Hồ sơ khám bệnh</h2>
        </div>
        <form method="post" class="form-card">
            <input type="hidden" name="action_type" value="save_record">

            <div class="form-row-2col">
                <div class="form-group">
                    <label>Lý do khám</label>
                    <select name="chief_complaint">
                        <option value="">-- Chọn --</option>
                        <?php
                            $opts = ['Đau răng', 'Ê buốt', 'Lấy cao răng', 'Niềng răng / chỉnh nha', 'Tư vấn thẩm mỹ', 'Khám tổng quát'];
                            $chief = $ap['chief_complaint'] ?? '';
                            foreach ($opts as $opt) {
                                $sel = ($chief === $opt) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($opt) . '" ' . $sel . '>' . htmlspecialchars($opt) . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="appointment_status">
                        <option value="WAITING" <?= ($ap['appointment_status'] ?? '') === 'WAITING' ? 'selected' : '' ?>>Chờ khám</option>
                        <option value="IN_PROGRESS" <?= ($ap['appointment_status'] ?? '') === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám</option>
                        <option value="COMPLETED" <?= ($ap['appointment_status'] ?? '') === 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                    </select>
                    <small>Lưu ý: Lễ tân xử lý các trạng thái Đã hủy / Không đến.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Ghi chú lâm sàng</label>
                <textarea name="clinical_note" rows="3"><?= htmlspecialchars($ap['clinical_note'] ?? '') ?></textarea>
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label>Chẩn đoán</label>
                    <select name="diagnosis">
                        <option value="">-- Chọn --</option>
                        <?php
                            $opts = ['Sâu răng', 'Viêm lợi', 'Viêm tủy', 'Rối loạn khớp thái dương hàm', 'Răng mọc lệch', 'Khác'];
                            $diag = $ap['diagnosis'] ?? '';
                            foreach ($opts as $opt) {
                                $sel = ($diag === $opt) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($opt) . '" ' . $sel . '>' . htmlspecialchars($opt) . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày hẹn tái khám</label>
                    <input type="date" name="suggested_next_visit" value="<?= htmlspecialchars($ap['suggested_next_visit'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Kế hoạch điều trị</label>
                <textarea name="treatment_plan" rows="3"><?= htmlspecialchars($ap['treatment_plan'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Ghi chú thêm</label>
                <textarea name="extra_note" rows="2"><?= htmlspecialchars($ap['extra_note'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Lưu hồ sơ khám</button>
                <a href="index.php?controller=doctor&action=appointments" class="btn-secondary">← Quay lại</a>
            </div>
        </form>
    </div>

    <div class="dc-panel">
        <div class="dc-panel-header">
            <h2>Lịch sử khám gần đây</h2>
        </div>
        <?php if (empty($historyView)): ?>
            <div class="empty-state">
                <h3>Chưa có lịch sử khám</h3>
                <p>Bệnh nhân chưa có hồ sơ khám trước đó.</p>
            </div>
        <?php else: ?>
            <table class="dc-table">
                <thead>
                    <tr>
                        <th>Ngày khám</th>
                        <th>Lý do</th>
                        <th>Chẩn đoán</th>
                        <th>Kế hoạch điều trị</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyView as $h): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($h['visit_date'])) ?></td>
                            <td><?= htmlspecialchars($h['chief_complaint'] ?? '') ?></td>
                            <td><?= htmlspecialchars($h['diagnosis'] ?? '') ?></td>
                            <td><?= nl2br(htmlspecialchars($h['treatment_plan'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
