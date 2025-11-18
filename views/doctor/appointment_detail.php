<?php
$ap = $appointmentView;
?>

<section class="dc-appointment-detail">
    <h1>Khám bệnh - lịch hẹn #<?= (int)$ap['appointment_id'] ?></h1>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box"><?= htmlspecialchars($errorView) ?></div>
    <?php endif; ?>
    <?php if (!empty($successView)): ?>
        <div class="form-success-box"><?= htmlspecialchars($successView) ?></div>
    <?php endif; ?>

    <!-- Thông tin bệnh nhân -->
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

    <!-- Thông tin lịch hẹn -->
    <div class="dc-panel" style="margin-top: 12px;">
        <div class="dc-panel-header">
            <h2>Thông tin lịch hẹn</h2>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Thời gian</label>
                <span>
                    <?php
                    $dt = strtotime($ap['appointment_date']);
                    echo date('d/m/Y', $dt);
                    ?>
                </span>
            </div>
            <div class="info-item">
                <label>Trạng thái hiện tại</label>
                <span>
                    <?php
                    $st = $ap['appointment_status'];
                    if ($st === 'WAITING') echo '<span class="tag tag-waiting">Chờ khám</span>';
                    elseif ($st === 'IN_PROGRESS') echo '<span class="tag tag-inprogress">Đang khám</span>';
                    elseif ($st === 'COMPLETED') echo '<span class="tag tag-done">Hoàn thành</span>';
                    elseif ($st === 'CANCELLED') echo '<span class="tag tag-cancel">Đã hủy</span>';
                    elseif ($st === 'NO_SHOW') echo '<span class="tag tag-noshow">Không đến</span>';
                    else echo '<span class="tag">' . htmlspecialchars($st) . '</span>';
                    ?>
                </span>
            </div>
        </div>
        <div class="info-block">
            <label>Ghi chú từ lễ tân</label>
            <p><?= nl2br(htmlspecialchars($ap['appointment_note'] ?? '')) ?></p>
        </div>
    </div>

    <!-- Form hồ sơ khám -->
    <div class="dc-panel" style="margin-top: 12px;">
        <div class="dc-panel-header">
            <h2>Hồ sơ khám hiện tại</h2>
        </div>

        <?php
        $chief = $ap['chief_complaint'] ?? '';
        $clin  = $ap['clinical_note'] ?? '';
        $diag  = $ap['diagnosis'] ?? '';
        $treat = $ap['treatment_plan'] ?? '';
        $extra = $ap['extra_note'] ?? '';
        $nextV = $ap['suggested_next_visit'] ?? '';
        ?>

        <form method="post" class="form-card">
            <input type="hidden" name="action_type" value="save_record">

            <div class="form-row-2col">
                <div class="form-group">
                    <label>Lý do khám (Chief complaint)</label>
                    <select name="chief_complaint">
                        <option value="">-- Chọn --</option>
                        <?php
                        $ccOptions = [
                            'Đau răng',
                            'Ê buốt',
                            'Lấy cao răng',
                            'Niềng răng / chỉnh nha',
                            'Tư vấn thẩm mỹ',
                            'Khám tổng quát',
                        ];
                        foreach ($ccOptions as $opt) {
                            $sel = ($chief === $opt) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($opt) . "\" $sel>" . htmlspecialchars($opt) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái lịch hẹn</label>
                    <?php $st2 = $ap['appointment_status']; ?>
                    <select name="appointment_status">
                        <option value="WAITING" <?= $st2 === 'WAITING' ? 'selected' : '' ?>>Chờ khám</option>
                        <option value="IN_PROGRESS" <?= $st2 === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám</option>
                        <option value="COMPLETED" <?= $st2 === 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                    </select>
                    <small>Bác sĩ không cần chọn ĐÃ HỦY / KHÔNG ĐẾN (lễ tân xử lý).</small>
                </div>
            </div>

            <div class="form-group">
                <label>Khám tổng quát / Ghi chú lâm sàng</label>
                <textarea name="clinical_note" rows="3"><?= htmlspecialchars($clin) ?></textarea>
            </div>

            <div class="form-row-2col">
                <div class="form-group">
                    <label>Chẩn đoán (Diagnosis)</label>
                    <select name="diagnosis">
                        <option value="">-- Chọn --</option>
                        <?php
                        $dgOptions = [
                            'Sâu răng',
                            'Viêm lợi',
                            'Viêm tủy',
                            'Rối loạn khớp thái dương hàm',
                            'Răng mọc lệch',
                            'Khác',
                        ];
                        foreach ($dgOptions as $opt) {
                            $sel = ($diag === $opt) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($opt) . "\" $sel>" . htmlspecialchars($opt) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày hẹn tái khám (nếu có)</label>
                    <input type="date" name="suggested_next_visit"
                        value="<?= htmlspecialchars($nextV) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Kế hoạch điều trị (Treatment plan)</label>
                <textarea name="treatment_plan" rows="3"><?= htmlspecialchars($treat) ?></textarea>
            </div>

            <div class="form-group">
                <label>Ghi chú thêm</label>
                <textarea name="extra_note" rows="2"><?= htmlspecialchars($extra) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Lưu hồ sơ khám</button>
                <a href="index.php?controller=doctor&action=appointments" class="btn-secondary">
                    ← Quay lại danh sách lịch hẹn
                </a>
            </div>
        </form>
    </div>

    <!-- DỊCH VỤ & HÓA ĐƠN -->
    <div class="dc-panel" style="margin-top: 12px;">
        <div class="dc-panel-header">
            <h2>Dịch vụ & Hóa đơn</h2>
        </div>

        <?php if (empty($appointmentView['record_id'])): ?>
            <p>
                Vui lòng <strong>lưu hồ sơ khám</strong> trước, sau đó bạn mới có thể tạo hóa đơn
                cho lần khám này.
            </p>
        <?php elseif (empty($servicesView)): ?>
            <p>
                Chưa có danh sách dịch vụ trong hệ thống.
                Vui lòng yêu cầu admin cấu hình ở phần "Dịch vụ / Giá".
            </p>
        <?php else: ?>
            <?php $inv = $invoiceView ?? null; ?>

            <?php if ($inv): ?>
                <p>
                    Hóa đơn hiện tại:
                    Tổng tiền <strong><?= number_format($inv['total_amount'], 0, ',', '.') ?> đ</strong>,
                    Giảm giá <strong><?= number_format($inv['discount'], 0, ',', '.') ?> đ</strong>,
                    Khách trả <strong><?= number_format($inv['final_amount'], 0, ',', '.') ?> đ</strong>.
                </p>
            <?php else: ?>
                <p>Chưa có hóa đơn cho lần khám này. Bạn có thể tạo mới bên dưới.</p>
            <?php endif; ?>

            <form method="post" class="form-card" id="invoice-form" action="index.php?controller=doctor&action=saveInvoice">
                <input type="hidden" name="action_type" value="save_invoice">

                <!-- Chọn dịch vụ + số lượng -->
                <div class="form-group">
                    <label>Chọn dịch vụ đã thực hiện</label>
                    <div class="svc-picker">
                        <select id="svc-select">
                            <option value="">-- Chọn dịch vụ --</option>
                            <?php foreach ($servicesView as $sv): ?>
                                <option
                                    value="<?= (int)$sv['service_id'] ?>"
                                    data-price="<?= (float)$sv['price'] ?>"
                                    data-unit="<?= htmlspecialchars($sv['unit'] ?? '') ?>">
                                    <?= htmlspecialchars($sv['service_name']) ?>
                                    (<?= number_format($sv['price'], 0, ',', '.') ?> đ /
                                    <?= htmlspecialchars($sv['unit'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" id="svc-qty" min="1" step="1" value="1" class="svc-qty-input"
                            placeholder="Số lượng">

                        <button type="button" class="btn-secondary bt" onclick="addServiceRow()">
                            Thêm 
                        </button>
                    </div>
                    <small>Chọn từng dịch vụ, nhập số lượng rồi bấm "Thêm".</small>
                </div>

                <!-- Bảng dịch vụ đã chọn -->
                <div class="form-group">
                    <table class="dc-table" id="selected-services-table">
                        <thead>
                            <tr>
                                <th>Dịch vụ</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="selected-services-body">
                            <!-- Các dòng dịch vụ sẽ được JS thêm vào đây -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:right;"><strong>Tạm tính:</strong></td>
                                <td colspan="2">
                                    <span id="subtotal-display">0</span> đ
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Giảm giá (đ)</label>
                        <input type="number" name="discount" id="discount-input" min="0" step="1000"
                            value="<?= $inv ? htmlspecialchars($inv['discount']) : '0' ?>"
                            oninput="recalculateTotal()">
                    </div>
                    <div class="form-group">
                        <label>Số tiền khách phải trả (sau giảm) - ước tính</label>
                        <div>
                            <span id="final-display">0</span> đ
                        </div>
                        <small>Giá trị thực tế vẫn được tính lại ở server.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ghi chú hóa đơn</label>
                    <textarea name="invoice_note" rows="2"><?= $inv ? htmlspecialchars($inv['note']) : '' ?></textarea>
                    <small>Ghi chú này kèm theo danh sách dịch vụ sẽ hiển thị cho lễ tân / bệnh nhân.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <?= $inv ? 'Cập nhật hóa đơn' : 'Tạo hóa đơn' ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>


    <!-- Lịch sử các lần khám trước -->
    <div class="dc-panel" style="margin-top: 12px;">
        <div class="dc-panel-header">
            <h2>Lịch sử khám gần đây</h2>
        </div>
        <?php if (empty($historyView)): ?>
            <p>Bệnh nhân chưa có hồ sơ khám trước đó.</p>
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
    <script>
        // Lưu dịch vụ đã chọn: {serviceId: {name, price, unit, qty}}
        const selectedServices = {};

        function formatNumber(n) {
            n = Number(n) || 0;
            return n.toLocaleString('vi-VN');
        }

        function addServiceRow() {
            const select = document.getElementById('svc-select');
            const qtyInput = document.getElementById('svc-qty');
            const svcId = select.value;

            if (!svcId) {
                alert('Vui lòng chọn dịch vụ.');
                return;
            }

            const qty = parseInt(qtyInput.value, 10);
            if (!qty || qty <= 0) {
                alert('Số lượng phải lớn hơn 0.');
                return;
            }

            const opt = select.options[select.selectedIndex];
            const name = opt.textContent || opt.innerText;
            const price = parseFloat(opt.getAttribute('data-price')) || 0;
            const unit = opt.getAttribute('data-unit') || '';

            // Nếu dịch vụ đã có, cộng dồn số lượng
            if (selectedServices[svcId]) {
                selectedServices[svcId].qty += qty;
            } else {
                selectedServices[svcId] = {
                    name,
                    price,
                    unit,
                    qty
                }
            }
        renderServiceTable();
        recalculateTotal();
        }

        function removeService(svcId) {
            if (selectedServices[svcId]) {
                delete selectedServices[svcId];
                renderServiceTable();
                recalculateTotal();
            }
        }

        function updateQty(svcId, newQty) {
            newQty = parseInt(newQty, 10) || 0;
            if (newQty <= 0) {
                // nếu <=0 thì xóa luôn
                removeService(svcId);
            } else {
                if (selectedServices[svcId]) {
                    selectedServices[svcId].qty = newQty;
                    renderServiceTable();
                    recalculateTotal();
                }
            }
        }

        function renderServiceTable() {
            const tbody = document.getElementById('selected-services-body');
            tbody.innerHTML = '';

            for (const svcId in selectedServices) {
                const item = selectedServices[svcId];
                const lineTotal = item.qty * item.price;

                const tr = document.createElement('tr');

                tr.innerHTML = `
                <td>
                    ${escapeHtml(item.name)}
                    <input type="hidden" name="quantities[${svcId}]" value="${item.qty}">
                </td>
                <td>${formatNumber(item.price)} đ / ${escapeHtml(item.unit)}</td>
                <td>
                    <input type="number"
                           min="1"
                           step="1"
                           value="${item.qty}"
                           onchange="updateQty('${svcId}', this.value)"
                           class="svc-qty-input">
                </td>
                <td>${formatNumber(lineTotal)} đ</td>
                <td>
                    <button type="button" class="btn-xs btn-danger" onclick="removeService('${svcId}')">
                        Xóa
                    </button>
                </td>
            `;

                tbody.appendChild(tr);
            }
        }

        function recalculateTotal() {
            let subtotal = 0;
            for (const svcId in selectedServices) {
                const item = selectedServices[svcId];
                subtotal += item.qty * item.price;
            }

            const discountInput = document.getElementById('discount-input');
            const discount = parseFloat(discountInput ? discountInput.value : 0) || 0;

            const final = Math.max(0, subtotal - discount);

            const subEl = document.getElementById('subtotal-display');
            const finalEl = document.getElementById('final-display');

            if (subEl) subEl.textContent = formatNumber(subtotal);
            if (finalEl) finalEl.textContent = formatNumber(final);
        }

        function escapeHtml(str) {
            if (typeof str !== 'string') return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    </script>
</section>