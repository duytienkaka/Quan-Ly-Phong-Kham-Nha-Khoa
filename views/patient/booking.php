<?php
// views/patient/booking.php

// Giữ lại giá trị cũ nếu có lỗi
$appointmentDateValue = $appointmentDateOld ?? '';
$timeBlockValue       = $timeBlockOld ?? '';
$noteValue            = $noteOld ?? '';
?>
<section class="patient-booking">
    <div class="section-header">
        <h2>Đặt lịch khám</h2>
        <div class="section-actions">
            <a href="index.php?controller=patient&action=appointments" class="btn-secondary">Xem lịch của tôi</a>
        </div>
    </div>

    <p class="section-subtitle">Vui lòng chọn ngày và buổi khám mong muốn. Chúng tôi sẽ sắp xếp bác sĩ phù hợp.</p>

    <?php if (!empty($error)): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="booking-card">
        <form method="post" action="index.php?controller=patient&action=saveBooking" class="booking-form">
            <div class="form-grid">
                <div class="form-row">
                    <label>Ngày khám <span class="required">*</span></label>
                    <input type="date" name="appointment_date" value="<?= htmlspecialchars($appointmentDateValue) ?>" required>
                </div>

                <div class="form-row">
                    <label>Buổi khám <span class="required">*</span></label>
                    <select name="time_block" required>
                        <option value="">-- Chọn buổi --</option>
                        <option value="MORNING"   <?= $timeBlockValue === 'MORNING'   ? 'selected' : '' ?>>Sáng</option>
                        <option value="AFTERNOON" <?= $timeBlockValue === 'AFTERNOON' ? 'selected' : '' ?>>Chiều</option>
                        <option value="EVENING"   <?= $timeBlockValue === 'EVENING'   ? 'selected' : '' ?>>Tối</option>
                    </select>
                </div>

                <div class="form-row form-full">
                    <label>Ghi chú / Lý do khám</label>
                    <textarea name="note" rows="3" placeholder="Ví dụ: Đau răng hàm trên, muốn khám định kỳ"><?= htmlspecialchars($noteValue) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary btn-large">Xác nhận đặt lịch</button>
                <a href="index.php?controller=patient&action=dashboard" class="btn-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</section>
