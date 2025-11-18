<section class="admin-doctors">
    <h1>Quản lý hóa đơn</h1>
    <p class="subtitle">
        Xem và quản lý các hóa đơn thanh toán của bệnh nhân.
    </p>

    <!-- Bộ lọc -->
    <form method="get" action="index.php" class="admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="invoices">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Tìm kiếm</label>
                <input type="text" name="q"
                       placeholder="Tên bệnh nhân, SĐT hoặc mã hóa đơn..."
                       value="<?= htmlspecialchars($keywordView ?? '') ?>">
            </div>

            <div class="filter-group">
                <label>Trạng thái thanh toán</label>
                <select name="status">
                    <option value="">-- Tất cả --</option>
                    <option value="UNPAID"  <?= ($statusView === 'UNPAID'  ? 'selected' : '') ?>>Chưa thanh toán</option>
                    <option value="PARTIAL" <?= ($statusView === 'PARTIAL' ? 'selected' : '') ?>>Thanh toán một phần</option>
                    <option value="PAID"    <?= ($statusView === 'PAID'    ? 'selected' : '') ?>>Đã thanh toán</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Từ ngày</label>
                <input type="date" name="from" value="<?= htmlspecialchars($fromView ?? '') ?>">
            </div>
            <div class="filter-group">
                <label>Đến ngày</label>
                <input type="date" name="to" value="<?= htmlspecialchars($toView ?? '') ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="index.php?controller=admin&action=invoices" class="btn-secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Danh sách hóa đơn</h2>
        </div>

        <?php if (empty($invoices)): ?>
            <p>Chưa có hóa đơn nào hoặc không tìm thấy theo điều kiện lọc.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Ngày lập</th>
                        <th>Bệnh nhân</th>
                        <th>Bác sĩ</th>
                        <th>Thành tiền</th>
                        <th>Trạng thái</th>
                        <th>PTTT</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td>#<?= (int)$inv['invoice_id'] ?></td>
                        <td><?= htmlspecialchars($inv['created_at']) ?></td>
                        <td>
                            <?= htmlspecialchars($inv['patient_name']) ?><br>
                            <small><?= htmlspecialchars($inv['patient_phone'] ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($inv['doctor_name'] ?? '—') ?></td>
                        <td><?= number_format($inv['final_amount'], 0, ',', '.') ?> đ</td>
                        <td>
                            <?php if ($inv['payment_status'] === 'PAID'): ?>
                                <span class="tag tag-active">Đã thanh toán</span>
                            <?php elseif ($inv['payment_status'] === 'PARTIAL'): ?>
                                <span class="tag tag-warn">Thanh toán một phần</span>
                            <?php else: ?>
                                <span class="tag tag-inactive">Chưa thanh toán</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($inv['payment_method'] ?? '—') ?></td>
                        <td>
                            <a href="index.php?controller=admin&action=invoiceDetail&id=<?= (int)$inv['invoice_id'] ?>"
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
                        $link = "index.php?controller=admin&action=invoices"
                              . "&q=" . urlencode($keywordView ?? '')
                              . "&status=" . urlencode($statusView ?? '')
                              . "&from=" . urlencode($fromView ?? '')
                              . "&to=" . urlencode($toView ?? '')
                              . "&page=" . $i;
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
