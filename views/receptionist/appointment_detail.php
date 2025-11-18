<?php

/** @var array $appointmentView */
/** @var array $doctorsView */
$ap = $appointmentView;

// format ngày giờ
$ts  = strtotime($ap['appointment_date']);
$day = date('d/m/Y', $ts);
$time = date('H:i', $ts);

// map status ra text + màu (class)
$status = $ap['status'];
function rc_status_label($st)
{
    switch ($st) {
        case 'WAITING':
            return 'Chờ khám';
        case 'IN_PROGRESS':
            return 'Đang khám';
        case 'COMPLETED':
            return 'Hoàn thành';
        case 'CANCELLED':
            return 'Đã hủy';
        case 'NO_SHOW':
            return 'Không đến';
        default:
            return $st;
    }
}
function rc_status_class($st)
{
    switch ($st) {
        case 'WAITING':
            return 'tag tag-pending';
        case 'IN_PROGRESS':
            return 'tag tag-inprogress';
        case 'COMPLETED':
            return 'tag tag-done';
        case 'CANCELLED':
            return 'tag tag-canceled';
        case 'NO_SHOW':
            return 'tag tag-noshow';
        default:
            return 'tag';
    }
}
?>

<section class="rc-appointment-detail">

    <!-- ====== PANEL TỔNG QUAN LỊCH HẸN + BỆNH NHÂN ====== -->
    <div class="rc-panel rc-panel-summary">
        <div class="rc-panel-header">
            <h2>Chi tiết lịch hẹn #<?= (int)$ap['appointment_id'] ?></h2>
            <?php if (!empty($ap['queue_number'])): ?>
                <span class="rc-badge-queue">
                    Số thứ tự: <strong>#<?= (int)$ap['queue_number'] ?></strong>
                </span>
            <?php endif; ?>
        </div>

        <p class="rc-appointment-desc">
            Lịch hẹn của
            <strong><?= htmlspecialchars($ap['patient_name']) ?></strong>
            ngày <strong><?= $day ?></strong>.
            Trạng thái hiện tại:
            <span class="<?= rc_status_class($status) ?>"><?= rc_status_label($status) ?></span>.
        </p>

        <div class="rc-info-grid">
            <!-- Cột: Thông tin lịch hẹn -->
            <div class="rc-info-block">
                <h3>Thông tin lịch hẹn</h3>
                <div class="info-item">
                    <label>Ngày khám:</label>
                    <span><?= $day ?></span>
                </div>
                <div class="info-item">
                    <label>Số thứ tự:</label>
                    <span><?= !empty($ap['queue_number']) ? '#' . (int)$ap['queue_number'] : 'Chưa có' ?></span>
                </div>
                <div class="info-item">
                    <label>Bác sĩ phụ trách:</label>
                    <span><?= htmlspecialchars($ap['doctor_name'] ?? 'Chưa gán') ?></span>
                </div>
                <div class="info-item info-note">
                    <label>Ghi chú từ bệnh nhân / lễ tân:</label>
                    <span><?= nl2br(htmlspecialchars($ap['note'] ?? 'Không có')) ?></span>
                </div>
            </div>

            <!-- Cột: Thông tin bệnh nhân -->
            <div class="rc-info-block">
                <h3>Thông tin bệnh nhân</h3>
                <div class="info-item">
                    <label>Họ tên:</label>
                    <span><?= htmlspecialchars($ap['patient_name']) ?></span>
                </div>
                <div class="info-item">
                    <label>Số điện thoại:</label>
                    <span><?= htmlspecialchars($ap['patient_phone'] ?? 'Chưa cập nhật') ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?= htmlspecialchars($ap['patient_email'] ?? 'Chưa cập nhật') ?></span>
                </div>
                <div class="info-item">
                    <label>Địa chỉ:</label>
                    <span><?= htmlspecialchars($ap['patient_address'] ?? 'Chưa cập nhật') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== PANEL GÁN BÁC SĨ & CẬP NHẬT TRẠNG THÁI (GIỮ CODE CŨ) ====== -->
    <div class="rc-panel">
        <div class="rc-panel-header">
            <h2>Gán bác sĩ & Cập nhật trạng thái</h2>
        </div>

        <form method="post"
            action="index.php?controller=receptionist&action=appointmentDetail&id=<?= (int)$ap['appointment_id'] ?>"
            class="form-card">

            <input type="hidden" name="action_type" value="update_main">

            <div class="form-row-2col">
                <div class="form-group">
                    <label>Bác sĩ phụ trách</label>
                    <select name="doctor_id">
                        <option value="0">-- Chưa gán bác sĩ --</option>
                        <?php foreach ($doctorsView as $d): ?>
                            <option value="<?= (int)$d['doctor_id'] ?>"
                                <?= (!empty($ap['doctor_id']) && $ap['doctor_id'] == $d['doctor_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <?php $st = $ap['status']; ?>
                    <select name="status">
                        <option value="WAITING" <?= $st === 'WAITING'    ? 'selected' : '' ?>>Chờ khám</option>
                        <option value="IN_PROGRESS" <?= $st === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám</option>
                        <option value="COMPLETED" <?= $st === 'COMPLETED'  ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="CANCELLED" <?= $st === 'CANCELLED'  ? 'selected' : '' ?>>Đã hủy</option>
                        <option value="NO_SHOW" <?= $st === 'NO_SHOW'    ? 'selected' : '' ?>>Không đến</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Xác nhận cập nhật và quay lại
                </button>
                <a href="index.php?controller=receptionist&action=appointments" class="btn-secondary">
                    Hủy / Quay lại
                </a>
            </div>
        </form>
    </div>
</section>