<section class="rc-dashboard">
    <h1>Chi tiết lịch hẹn #<?= (int)$appointmentView['appointment_id'] ?></h1>

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

    <div class="rc-panel">
        <div class="rc-panel-header">
            <h2>Thông tin bệnh nhân</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Họ tên</label>
                <span><?= htmlspecialchars($appointmentView['patient_name']) ?></span>
            </div>
            <div class="info-item">
                <label>SĐT</label>
                <span><?= htmlspecialchars($appointmentView['patient_phone'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Email</label>
                <span><?= htmlspecialchars($appointmentView['patient_email'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Địa chỉ</label>
                <span><?= htmlspecialchars($appointmentView['patient_address'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <div class="rc-panel" style="margin-top:12px;">
        <div class="rc-panel-header">
            <h2>Thông tin lịch hẹn</h2>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Ngày / giờ</label>
                <span>
                    <?php
                        $dt = strtotime($appointmentView['appointment_date']);
                        echo date('H:i d/m/Y', $dt);
                    ?>
                </span>
            </div>
            <div class="info-item">
                <label>Bác sĩ</label>
                <span><?= htmlspecialchars($appointmentView['doctor_name'] ?? 'Chưa gán') ?></span>
            </div>
            <div class="info-item">
                <label>Trạng thái hiện tại</label>
                <span>
                    <?php
                        $st = $appointmentView['status'];
                        if     ($st === 'WAITING')     echo '<span class="tag tag-pending">Chờ duyệt</span>';
                        elseif ($st === 'IN_PROGRESS') echo '<span class="tag tag-inprogress">Đang khám</span>';
                        elseif ($st === 'COMPLETED')   echo '<span class="tag tag-done">Hoàn thành</span>';
                        elseif ($st === 'CANCELLED')   echo '<span class="tag tag-canceled">Đã hủy</span>';
                        elseif ($st === 'NO_SHOW')     echo '<span class="tag tag-noshow">Không đến</span>';
                        else                           echo '<span class="tag">Không xác định</span>';
                    ?>
                </span>
            </div>
        </div>

        <div class="info-block">
            <label>Ghi chú</label>
            <p><?= nl2br(htmlspecialchars($appointmentView['note'] ?? '')) ?></p>
        </div>
    </div>

    <!-- Combined update panel: Gán bác sĩ + Cập nhật trạng thái -->
    <div class="rc-panel" style="margin-top:12px;">
        <div class="rc-panel-header">
            <h2>Gán bác sĩ &amp; Cập nhật trạng thái</h2>
        </div>

        <form id="bulkUpdateForm" method="post" class="form-card">
            <input type="hidden" name="appointment_id" value="<?= (int)$appointmentView['appointment_id'] ?>">

            <div class="panel-row-2col">
                <div>
                    <div class="form-group">
                        <label>Bác sĩ phụ trách</label>
                        <select name="doctor_id" id="doctorSelect">
                            <option value="">-- Chưa gán --</option>
                            <?php foreach ($doctorsView as $d): ?>
                                <option value="<?= (int)$d['doctor_id'] ?>"
                                    <?= (isset($appointmentView['doctor_id']) && $appointmentView['doctor_id'] == $d['doctor_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <?php $stNow = $appointmentView['status']; ?>
                        <select name="status" id="statusSelect">
                            <option value="WAITING"     <?= $stNow === 'WAITING'     ? 'selected' : '' ?>>Chờ duyệt (WAITING)</option>
                            <option value="IN_PROGRESS" <?= $stNow === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám (IN_PROGRESS)</option>
                            <option value="COMPLETED"   <?= $stNow === 'COMPLETED'   ? 'selected' : '' ?>>Hoàn thành (COMPLETED)</option>
                            <option value="CANCELLED"   <?= $stNow === 'CANCELLED'   ? 'selected' : '' ?>>Đã hủy (CANCELLED)</option>
                            <option value="NO_SHOW"     <?= $stNow === 'NO_SHOW'     ? 'selected' : '' ?>>Không đến (NO_SHOW)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="margin-top:8px;">
                <button id="bulkConfirmBtn" type="submit" class="btn-primary">Xác nhận cập nhật và quay lại</button>
                <a href="index.php?controller=receptionist&action=appointments" class="btn-secondary">Hủy / Quay lại</a>
            </div>
        </form>
    </div>

    <!-- Hủy lịch -->
    <div class="rc-panel" style="margin-top:12px;">
        <div class="rc-panel-header">
            <h2>Hủy lịch hẹn</h2>
        </div>

        <form method="post" class="form-card" onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch hẹn này?');">
            <input type="hidden" name="action_type" value="cancel">
            <div class="form-group">
                <label>Lý do hủy</label>
                <textarea name="cancel_reason" rows="2"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-secondary" style="border-color:#ef4444;color:#b91c1c;">
                    Hủy lịch hẹn
                </button>
                <a href="index.php?controller=receptionist&action=appointments" class="btn-secondary">
                    ← Quay lại danh sách
                </a>
            </div>
        </form>
    </div>
</section>
