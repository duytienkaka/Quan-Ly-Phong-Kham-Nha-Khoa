<section class="admin-doctors">
    <h1>Thống kê bác sĩ: <?= htmlspecialchars($doctorView['doctor_name']) ?></h1>
    <p class="subtitle">
        Thống kê theo khoảng thời gian.
    </p>

    <form method="get" action="index.php" class="admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="doctorStats">
        <input type="hidden" name="id" value="<?= (int)$doctorView['doctor_id'] ?>">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Từ ngày</label>
                <input type="date" name="from" value="<?= htmlspecialchars($fromView) ?>">
            </div>
            <div class="filter-group">
                <label>Đến ngày</label>
                <input type="date" name="to" value="<?= htmlspecialchars($toView) ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Xem thống kê</button>
            </div>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Kết quả</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Tổng lịch hẹn</div>
                <div class="stat-value"><?= (int)$totalAppointmentsView ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Số ca khám (có hồ sơ)</div>
                <div class="stat-value"><?= (int)$totalVisitsView ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Doanh thu</div>
                <div class="stat-value">
                    <?= number_format($totalRevenueView, 0, ',', '.') ?> đ
                </div>
            </div>
        </div>
    </div>
</section>
