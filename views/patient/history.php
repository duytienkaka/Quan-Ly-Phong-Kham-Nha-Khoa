<?php
// views/patient/history.php
?>
<section class="patient-history">
    <div class="section-header">
        <h2>Lịch sử khám</h2>
        <div class="section-actions">
            <a href="index.php?controller=patient&action=booking" class="btn-secondary">Đặt lịch mới</a>
        </div>
    </div>
    <?php if (!empty($totalItems)): ?>
        <div class="result-meta">Hiển thị <strong><?= $startItem ?> - <?= $endItem ?></strong> trên <strong><?= $totalItems ?></strong> kết quả</div>
    <?php endif; ?>
    <form method="get" action="index.php" class="filter-form">
        <input type="hidden" name="controller" value="patient">
        <input type="hidden" name="action" value="history">

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
                <button type="submit" class="btn-primary btn-large">Lọc</button>
                <a href="index.php?controller=patient&action=history" class="btn-secondary btn-large">Xóa lọc</a>
            </div>
        </div>
    </form>
    <?php if (empty($records)): ?>
        <div class="empty-state">
            <p class="empty-title">Bạn chưa có lịch sử khám</p>
            <p class="empty-desc">Sau khi khám, hồ sơ và chẩn đoán sẽ được lưu tại đây.</p>
            <a href="index.php?controller=patient&action=booking" class="btn-primary">Đặt lịch khám</a>
        </div>
    <?php else: ?>
        <div class="table-card">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Ngày khám</th>
                        <th>Lý do khám</th>
                        <th>Chẩn đoán</th>
                        <th>Kế hoạch điều trị</th>
                        <th>Ghi chú</th>
                        <th>Tái khám</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['visit_date'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['chief_complaint'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['diagnosis'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['treatment_plan'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['extra_note'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['suggested_next_visit'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $link = "index.php?controller=patient&action=history"
                    . "&from_date=" . urlencode($fromDateView ?? '')
                    . "&to_date="   . urlencode($toDateView ?? '')
                    . "&page="      . $i;
                ?>
                <a href="<?= $link ?>" class="<?= ($i == ($currentPage ?? 1) ? 'active' : '') ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>