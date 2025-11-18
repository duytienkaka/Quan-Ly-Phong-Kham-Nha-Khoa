<section class="rc-dashboard">
    <h1>Quản lý hóa đơn</h1>
    <p class="rc-subtitle">
        Xem và cập nhật trạng thái thanh toán cho hóa đơn.
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

    <form method="get" action="index.php" class="rc-toolbar rc-toolbar-form">
        <input type="hidden" name="controller" value="receptionist">
        <input type="hidden" name="action" value="invoices">

        <div>
            <label>Ngày tạo</label><br>
            <input type="date" name="date"
                   value="<?= htmlspecialchars($dateView ?? '') ?>">
        </div>

        <div>
            <label>Trạng thái thanh toán</label><br>
            <?php $st = $statusView ?? ''; ?>
            <select name="status">
                <option value="">-- Tất cả --</option>
                <option value="UNPAID"  <?= $st === 'UNPAID'  ? 'selected' : '' ?>>Chưa thanh toán</option>
                <option value="PAID"    <?= $st === 'PAID'    ? 'selected' : '' ?>>Đã thanh toán</option>
                <option value="PARTIAL" <?= $st === 'PARTIAL' ? 'selected' : '' ?>>Thanh toán một phần</option>
            </select>
        </div>

        <div>
            <label>Tìm kiếm</label><br>
            <input type="text" name="q"
                   placeholder="Mã HĐ / tên BN / SĐT..."
                   value="<?= htmlspecialchars($keywordView ?? '') ?>">
        </div>

        <div class="rc-toolbar-actions">
            <button type="submit" class="btn-primary">Lọc</button>
            <a href="index.php?controller=receptionist&action=invoices" class="btn-secondary">
                Xóa lọc
            </a>
        </div>
    </form>

    <div class="rc-panel" style="margin-top: 12px;">
        <div class="rc-panel-header">
            <h2>Danh sách hóa đơn</h2>
            <span class="rc-panel-note">
                Tổng <?= (int)($totalRowsView ?? 0) ?> hóa đơn.
            </span>
        </div>

        <?php if (empty($invoicesView)): ?>
            <p>Không có hóa đơn nào theo điều kiện lọc.</p>
        <?php else: ?>
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Ngày tạo</th>
                        <th>Bệnh nhân</th>
                        <th>Bác sĩ</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật thanh toán</th>
                        <th>Xem chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($invoicesView as $inv): ?>
                    <tr>
                        <td>#<?= (int)$inv['invoice_id'] ?></td>
                        <td>
                            <?php
                                $dt = strtotime($inv['created_at']);
                                echo date('H:i d/m/Y', $dt);
                            ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($inv['patient_name']) ?></strong><br>
                            <small><?= htmlspecialchars($inv['patient_phone'] ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($inv['doctor_name'] ?? '—') ?></td>
                        <td>
                            <?php
                                echo number_format($inv['final_amount'], 0, ',', '.') . ' đ';
                                if ((float)$inv['discount'] > 0) {
                                    echo '<br><small>Giảm: '
                                         . number_format($inv['discount'], 0, ',', '.')
                                         . ' đ</small>';
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                $ps = $inv['payment_status'];
                                if     ($ps === 'UNPAID')  echo '<span class="tag tag-pending">Chưa thanh toán</span>';
                                elseif ($ps === 'PAID')    echo '<span class="tag tag-done">Đã thanh toán</span>';
                                elseif ($ps === 'PARTIAL') echo '<span class="tag tag-inprogress">Thanh toán một phần</span>';
                                else                      echo '<span class="tag">'.htmlspecialchars($ps).'</span>';
                            ?>
                        </td>

                        <!-- Form cập nhật thanh toán cho từng hóa đơn -->
                        <td>
                            <form method="post"
                                  action="index.php?controller=receptionist&action=invoices"
                                  class="inline-form">
                                <input type="hidden" name="invoice_id"
                                       value="<?= (int)$inv['invoice_id'] ?>">

                                <select name="payment_status" class="small-select">
                                    <option value="UNPAID"  <?= $ps === 'UNPAID'  ? 'selected' : '' ?>>UNPAID</option>
                                    <option value="PAID"    <?= $ps === 'PAID'    ? 'selected' : '' ?>>PAID</option>
                                    <option value="PARTIAL" <?= $ps === 'PARTIAL' ? 'selected' : '' ?>>PARTIAL</option>
                                </select>

                                <select name="payment_method" class="small-select">
                                    <option value="">-- Chọn --</option>
                                    <option value="cash" <?= ($inv['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Tiền mặt</option>
                                    <option value="pay" <?= ($inv['payment_method'] ?? '') === 'pay' ? 'selected' : '' ?>>Chuyển khoản</option>
                                </select>

                                <button type="submit" class="btn-xs">Lưu</button>
                            </form>
                        </td>

                        <td>
                            <a href="index.php?controller=receptionist&action=invoiceDetail&id=<?= (int)$inv['invoice_id'] ?>"
                               class="btn-xs">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($totalPagesView) && $totalPagesView > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPagesView; $i++): ?>
                        <?php
                            $link = "index.php?controller=receptionist&action=invoices"
                                  . "&status=" . urlencode($statusView ?? '')
                                  . "&date="   . urlencode($dateView ?? '')
                                  . "&q="      . urlencode($keywordView ?? '')
                                  . "&page="   . $i;
                        ?>
                        <a href="<?= $link ?>" class="<?= ($i == $currentPage ? 'active' : '') ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
