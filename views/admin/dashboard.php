<section class="admin-dashboard">
    <h1>Dashboard tổng quan</h1>
    <p class="subtitle">Xem nhanh tình hình hoạt động của phòng khám.</p>

    <!-- Main Stats -->
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Bệnh nhân</h3>
            <p class="stat-number"><?= number_format($totalPatients) ?></p>
            <span class="stat-label">Tổng số bệnh nhân</span>
        </div>

        <div class="stat-card">
            <h3>Bác sĩ</h3>
            <p class="stat-number"><?= number_format($totalDoctors) ?></p>
            <span class="stat-label">Bác sĩ đang hoạt động</span>
        </div>

        <div class="stat-card">
            <h3>Lịch hẹn</h3>
            <p class="stat-number"><?= number_format($totalAppointments) ?></p>
            <span class="stat-label">Tổng lịch hẹn</span>
        </div>

        <div class="stat-card">
            <h3>Doanh thu</h3>
            <p class="stat-number">
                <?= number_format($totalRevenue, 0, ',', '.') ?> VNĐ
            </p>
            <span class="stat-label">Đã thanh toán</span>
        </div>
    </div>

    <!-- Secondary Stats Row -->
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Hôm nay</h3>
            <p class="stat-number"><?= number_format($todayAppointments) ?></p>
            <span class="stat-label">Lịch hẹn</span>
        </div>

        <div class="stat-card">
            <h3>Sắp tới</h3>
            <p class="stat-number"><?= number_format($upcomingAppointments) ?></p>
            <span class="stat-label">7 ngày tới</span>
        </div>

        <div class="stat-card">
            <h3>Chưa thanh toán</h3>
            <p class="stat-number"><?= number_format($unpaidInvoices) ?></p>
            <span class="stat-label">Hóa đơn</span>
        </div>

        <div class="stat-card">
            <h3>Tổng hóa đơn</h3>
            <p class="stat-number"><?= number_format($totalInvoices) ?></p>
            <span class="stat-label">Phát hành</span>
        </div>
    </div>

    <!-- Charts & Tables Grid -->
    <div class="dashboard-grid">
        <!-- Chart: Monthly Revenue -->
        <div class="admin-panel">
            <div class="panel-header">
                <h2>Doanh thu theo tháng</h2>
            </div>
            <div class="chart-placeholder">
                <canvas id="revenueChart" style="max-height: 280px;"></canvas>
            </div>
        </div>

        <!-- Chart: Appointment Status -->
        <div class="admin-panel">
            <div class="panel-header">
                <h2>Trạng thái lịch hẹn</h2>
            </div>
            <div class="chart-placeholder">
                <canvas id="statusChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Doctors & Recent Patients -->
    <div class="dashboard-grid">
        <div class="admin-panel">
            <div class="panel-header">
                <h2>Bác sĩ hàng đầu</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên bác sĩ</th>
                        <th>Lịch hẹn</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($topDoctors)): ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: #6b7280; padding: 16px;">Chưa có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topDoctors as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['full_name']) ?></td>
                            <td style="text-align: right; font-weight: 700; color: #0ea5e9;"><?= $d['count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-panel">
            <div class="panel-header">
                <h2>Bệnh nhân mới (7 ngày)</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên bệnh nhân</th>
                        <th>Ngày đăng ký</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($newPatients)): ?>
                    <tr>
                        <td colspan="2" style="text-align: center; color: #6b7280; padding: 16px;">Chưa có bệnh nhân mới</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($newPatients as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['full_name']) ?></td>
                            <td style="color: #6b7280; font-size: 13px;">
                                <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Appointments -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2>Lịch hẹn mới nhất</h2>
        </div>

        <?php if (empty($recentAppointments)): ?>
            <p>Chưa có lịch hẹn nào.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã lịch hẹn</th>
                        <th>Bệnh nhân</th>
                        <th>Ngày</th>
                        <th>Buổi</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentAppointments as $a): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($a['appointment_id']) ?></td>
                        <td><?= htmlspecialchars($a['patient_name']) ?></td>
                        <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                        <td>
                            <?php
                            switch ($a['time_block']) {
                                case 'MORNING':   echo 'Sáng'; break;
                                case 'AFTERNOON': echo 'Chiều'; break;
                                case 'EVENING':   echo 'Tối'; break;
                                default:          echo htmlspecialchars($a['time_block']);
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            switch ($a['status']) {
                                case 'WAITING':    echo '<span class="tag tag-waiting">Đang chờ</span>'; break;
                                case 'IN_PROGRESS':echo '<span class="tag tag-progress">Đang khám</span>'; break;
                                case 'COMPLETED':  echo '<span class="tag tag-success">Hoàn thành</span>'; break;
                                case 'CANCELLED':  echo '<span class="tag tag-cancelled">Đã hủy</span>'; break;
                                case 'NO_SHOW':    echo '<span class="tag tag-noshow">Không đến</span>'; break;
                                default:           echo htmlspecialchars($a['status']);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Revenue Chart - Line chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    const revenueData = <?= json_encode($monthlyRevenue) ?>;
    const labels = revenueData.map(d => d['month']);
    const data = revenueData.map(d => parseFloat(d['revenue']));
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: data,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#0ea5e9',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12, weight: 'bold' }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' đ';
                        }
                    }
                }
            }
        }
    });
}

// Status Chart - Doughnut chart
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    const statusData = <?= json_encode($appointmentStatus) ?>;
    const statusMap = {
        'WAITING': 'Đang chờ',
        'IN_PROGRESS': 'Đang khám',
        'COMPLETED': 'Hoàn thành',
        'CANCELLED': 'Đã hủy',
        'NO_SHOW': 'Không đến'
    };
    const colors = {
        'WAITING': '#fef3c7',
        'IN_PROGRESS': '#bfdbfe',
        'COMPLETED': '#bbf7d0',
        'CANCELLED': '#fecaca',
        'NO_SHOW': '#e5e7eb'
    };
    const textColors = {
        'WAITING': '#854d0e',
        'IN_PROGRESS': '#1e40af',
        'COMPLETED': '#166534',
        'CANCELLED': '#991b1b',
        'NO_SHOW': '#374151'
    };
    
    const labels = statusData.map(s => statusMap[s['status']] || s['status']);
    const counts = statusData.map(s => s['count']);
    const bgColors = statusData.map(s => colors[s['status']] || '#e5e7eb');
    const txtColors = statusData.map(s => textColors[s['status']] || '#374151');
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: bgColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12, weight: '600' }
                    }
                }
            }
        }
    });
}
</script>