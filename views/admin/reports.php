<section class="admin-dashboard">
    <h1>Báo cáo / Thống kê doanh thu</h1>
    <p class="subtitle">
        Xem doanh thu theo khoảng thời gian, top dịch vụ và doanh thu theo bác sĩ.
    </p>

    <!-- Bộ lọc -->
    <form method="get" action="index.php" class="admin-toolbar admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="reports">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Từ ngày</label>
                <input type="date" name="from_date" value="<?= htmlspecialchars($fromDateView) ?>">
            </div>

            <div class="filter-group">
                <label>Đến ngày</label>
                <input type="date" name="to_date" value="<?= htmlspecialchars($toDateView) ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Xem báo cáo</button>
            </div>
        </div>
    </form>

    <!-- Tổng doanh thu -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2>Tổng doanh thu</h2>
            <span class="stat-label">
                Từ <?= htmlspecialchars($fromDateView) ?> đến <?= htmlspecialchars($toDateView) ?>
            </span>
        </div>

        <p style="font-size: 24px; font-weight: 700; margin-bottom: 16px;">
            <?= number_format($totalRevenueView, 0, ',', '.') ?> đ
        </p>

        <?php if (!empty($dailyRevenueView)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyRevenueView as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['d']) ?></td>
                            <td><?= number_format($row['revenue'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Không có hóa đơn trong khoảng thời gian này.</p>
        <?php endif; ?>
    </div>

    <!-- Top dịch vụ -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2>Top dịch vụ sử dụng nhiều nhất</h2>
        </div>

        <?php if (empty($topServicesView)): ?>
            <p>Không có dữ liệu.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Dịch vụ</th>
                        <th>Đơn vị</th>
                        <th>Số lượng</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topServicesView as $svc): ?>
                        <tr>
                            <td><?= htmlspecialchars($svc['service_name']) ?></td>
                            <td><?= htmlspecialchars($svc['unit']) ?></td>
                            <td><?= (int)$svc['total_qty'] ?></td>
                            <td><?= number_format($svc['total_revenue'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Doanh thu theo bác sĩ -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2>Doanh thu theo bác sĩ</h2>
        </div>

        <?php if (empty($doctorRevenueView)): ?>
            <p>Không có dữ liệu.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Bác sĩ</th>
                        <th>Số hóa đơn</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctorRevenueView as $dr): ?>
                        <tr>
                            <td><?= htmlspecialchars($dr['doctor_name']) ?></td>
                            <td><?= (int)$dr['invoice_count'] ?></td>
                            <td><?= number_format($dr['total_revenue'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
