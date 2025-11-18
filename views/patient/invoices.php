<?php
// views/patient/invoices.php
?>
<section class="patient-invoices">
    <div class="section-header">
        <h2>Hóa đơn của tôi</h2>
        <div class="section-actions">
            <a href="index.php?controller=patient&action=booking" class="btn-secondary">Đặt lịch mới</a>
        </div>
    </div>
    <?php if (!empty($totalItems)): ?>
        <div class="result-meta">Hiển thị <strong><?= $startItem ?> - <?= $endItem ?></strong> trên <strong><?= $totalItems ?></strong> kết quả</div>
    <?php endif; ?>
    <form method="get" action="index.php" class="filter-form">
        <input type="hidden" name="controller" value="patient">
        <input type="hidden" name="action" value="invoices">

        <div class="filter-row">
            <div>
                <label>Từ ngày</label>
                <input type="date" name="from_date"
                    value="<?= htmlspecialchars($fromDateView ?? '') ?>">
            </div>
            <div>
                <label>Đến ngày</label>
                <input type="date" name="to_date"
                    value="<?= htmlspecialchars($toDateView ?? '') ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="index.php?controller=patient&action=invoices" class="btn-secondary">Xóa lọc</a>
            </div>
        </div>
    </form>
    <?php if (empty($invoices)): ?>
        <div class="empty-state">
            <p class="empty-title">Bạn chưa có hóa đơn</p>
            <p class="empty-desc">Mọi dịch vụ phát sinh hóa đơn sẽ được hiển thị tại đây.</p>
            <a href="index.php?controller=patient&action=booking" class="btn-primary">Đặt lịch khám</a>
        </div>
    <?php else: ?>
        <div class="table-card">
            <table class="invoices-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Ngày</th>
                        <th>Thành tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($inv['invoice_id']) ?></td>
                            <td><?= htmlspecialchars($inv['created_at']) ?></td>
                            <td><?= number_format($inv['final_amount'], 0, ',', '.') ?> VNĐ</td>
                            <td>
                                <?php
                                switch ($inv['payment_status']) {
                                    case 'PAID':
                                        $label = 'Đã thanh toán';
                                        $cls = 'badge-success';
                                        break;
                                    case 'PARTIAL':
                                        $label = 'Thanh toán một phần';
                                        $cls = 'badge-info';
                                        break;
                                    case 'UNPAID':
                                        $label = 'Chưa thanh toán';
                                        $cls = 'badge-waiting';
                                        break;
                                    default:
                                        $label = htmlspecialchars($inv['payment_status']);
                                        $cls = 'badge-muted';
                                }
                                ?>
                                <span class="badge <?= $cls ?>"><?= $label ?></span>
                            </td>
                            <td>
                                <a href="index.php?controller=patient&action=invoiceDetail&id=<?= (int)$inv['invoice_id'] ?>"
                                    class="btn btn-sm">
                                    Xem chi tiết
                                </a>
                                <?php if ($inv['payment_status'] === 'UNPAID'): ?>
                                    <a href="#" class="btn-primary">Thanh toán</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $link = "index.php?controller=patient&action=invoices"
                        . "&from_date=" . urlencode($fromDateView ?? '')
                        . "&to_date="   . urlencode($toDateView ?? '')
                        . "&page="      . $i;
                    ?>
                    <a href="<?= $link ?>" class="<?= ($i == $currentPage ? 'active' : '') ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>