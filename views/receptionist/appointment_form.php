<section class="rc-dashboard">
    <h1>Tạo lịch hẹn mới</h1>
    <p class="rc-subtitle">
        Tạo lịch hẹn thủ công giúp người mới chưa có tài khoản.
    </p>

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
        <form method="post" class="form-card">
            <?php $mode = $_POST['patient_mode'] ?? 'existing'; ?>

            <div class="form-group">
                <label>Đối tượng bệnh nhân</label>
                <div class="radio-row">
                    <label>
                        <input type="radio" name="patient_mode" value="existing"
                            <?= $mode === 'existing' ? 'checked' : '' ?>>
                        Chọn bệnh nhân đã có trong hệ thống
                    </label>
                    <label>
                        <input type="radio" name="patient_mode" value="new"
                            <?= $mode === 'new' ? 'checked' : '' ?>>
                        Bệnh nhân mới (chưa có hồ sơ)
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Bệnh nhân có sẵn</label>
                <select name="patient_id">
                    <option value="">-- Chọn bệnh nhân --</option>
                    <?php foreach ($patientsView as $p): ?>
                        <option value="<?= (int)$p['patient_id'] ?>"
                            <?= (!empty($_POST['patient_id']) && $_POST['patient_id'] == $p['patient_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['full_name']) ?>
                            (<?= htmlspecialchars($p['phone'] ?? '') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="hint">
                    Nếu khách chưa từng đến khám, hãy chọn mục "Bệnh nhân mới" rồi nhập thông tin bên dưới.
                </small>
            </div>

            <div id="newPatientPanel" class="rc-subpanel <?= $mode === 'new' ? 'visible' : 'hidden' ?>">
                <h3>Bệnh nhân mới (tùy chọn)</h3>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Họ và tên (mới)</label>
                        <input type="text" name="new_full_name"
                            value="<?= htmlspecialchars($_POST['new_full_name'] ?? '') ?>"
                            placeholder="Nhập nếu là bệnh nhân mới">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại (mới)</label>
                        <input type="text" name="new_phone"
                            value="<?= htmlspecialchars($_POST['new_phone'] ?? '') ?>"
                            placeholder="Nhập nếu là bệnh nhân mới">
                    </div>
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Email (mới)</label>
                        <input type="email" name="new_email"
                            value="<?= htmlspecialchars($_POST['new_email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ (mới)</label>
                        <input type="text" name="new_address"
                            value="<?= htmlspecialchars($_POST['new_address'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Ghi chú hồ sơ bệnh nhân (mới)</label>
                    <textarea name="new_note" rows="2"><?= htmlspecialchars($_POST['new_note'] ?? '') ?></textarea>
                </div>
            </div>

            <hr class="rc-divider">

            <div class="form-row-2col">
                <div class="form-group">
                    <label>Ngày khám <span class="required">*</span></label>
                    <input type="date" name="date" required
                        value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>">
                </div>
                <div class="form-group">
                    <label>Giờ khám <span class="required">*</span></label>
                    <input type="time" name="time" required
                        value="<?= htmlspecialchars($_POST['time'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Trạng thái ban đầu</label>
                <?php $st = $_POST['status'] ?? 'WAITING'; ?>
                <select name="status">
                    <option value="WAITING" <?= $st === 'WAITING'     ? 'selected' : '' ?>>Chờ duyệt (WAITING)</option>
                    <option value="IN_PROGRESS" <?= $st === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám (IN_PROGRESS)</option>
                    <option value="COMPLETED" <?= $st === 'COMPLETED'   ? 'selected' : '' ?>>Hoàn thành (COMPLETED)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ghi chú lịch hẹn</label>
                <textarea name="note" rows="3"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Lưu lịch hẹn</button>
                <a href="index.php?controller=receptionist&action=appointments" class="btn-secondary">
                    Hủy
                </a>
            </div>
        </form>
    </div>

    <script>
        (function() {
            const radios = document.querySelectorAll('input[name="patient_mode"]');
            const panel = document.getElementById('newPatientPanel');
            if (!panel || radios.length === 0) return;

            function update() {
                const sel = document.querySelector('input[name="patient_mode"]:checked');
                if (sel && sel.value === 'new') {
                    panel.classList.add('visible');
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                    panel.classList.remove('visible');
                }
            }
            radios.forEach(r => r.addEventListener('change', update));
            update();
        })();
    </script>
</section>
