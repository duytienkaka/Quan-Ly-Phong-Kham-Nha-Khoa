<?php if (!empty($successQueueView)): ?>
    <div class="alert-success">
        Bạn đã đặt lịch thành công. Số thứ tự của bạn là
        <strong>#<?= (int)$successQueueView ?></strong>.
    </div>
<?php endif; ?>
<section class="patient-appointments">
    <div class="section-header">
        <h2>Lịch hẹn của tôi</h2>
        <div class="section-actions">
            <a href="index.php?controller=patient&action=booking" class="btn-secondary">Đặt lịch mới</a>
        </div>
    </div>
    <?php if (!empty($totalItems)): ?>
        <div class="result-meta">Hiển thị <strong><?= $startItem ?> - <?= $endItem ?></strong> trên <strong><?= $totalItems ?></strong> kết quả</div>
    <?php endif; ?>
    <form method="get" action="index.php" class="filter-form">
        <input type="hidden" name="controller" value="patient">
        <input type="hidden" name="action" value="appointments">

        <div class="filter-row">
            <div>
                <label>Từ ngày</label>
                <input type="date" name="from_date" value="<?= htmlspecialchars($fromDateView ?? '') ?>">
            </div>
            <div>
                <label>Đến ngày</label>
                <input type="date" name="to_date" value="<?= htmlspecialchars($toDateView ?? '') ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary btn-large">Lọc</button>
                <a href="index.php?controller=patient&action=appointments" class="btn-secondary btn-large">Xóa lọc</a>
            </div>
        </div>
    </form>
    <?php if (empty($appointments)): ?>
        <div class="empty-state">
            <p class="empty-title">Bạn chưa có lịch hẹn nào</p>
            <p class="empty-desc">Hãy đặt lịch khám để được tư vấn và điều trị kịp thời.</p>
            <a href="index.php?controller=patient&action=booking" class="btn-primary">Đặt lịch ngay</a>
        </div>
    <?php else: ?>
        <div class="table-card">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Ngày khám</th>
                        <th>Buổi</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                            <td>
                                <?php
                                switch ($a['time_block']) {
                                    case 'MORNING':
                                        echo 'Sáng';
                                        break;
                                    case 'AFTERNOON':
                                        echo 'Chiều';
                                        break;
                                    case 'EVENING':
                                        echo 'Tối';
                                        break;
                                    default:
                                        echo htmlspecialchars($a['time_block']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'badge';
                                switch ($a['status']) {
                                    case 'WAITING':
                                        $label = 'Đang chờ';
                                        $statusClass .= ' badge-waiting';
                                        break;
                                    case 'IN_PROGRESS':
                                        $label = 'Đang khám';
                                        $statusClass .= ' badge-info';
                                        break;
                                    case 'COMPLETED':
                                        $label = 'Hoàn thành';
                                        $statusClass .= ' badge-success';
                                        break;
                                    case 'CANCELLED':
                                        $label = 'Đã hủy';
                                        $statusClass .= ' badge-muted';
                                        break;
                                    case 'NO_SHOW':
                                        $label = 'Không đến';
                                        $statusClass .= ' badge-muted';
                                        break;
                                    default:
                                        $label = htmlspecialchars($a['status']);
                                }
                                ?>
                                <span class="<?= $statusClass ?>"><?= $label ?></span>
                            </td>
                            <td><?= htmlspecialchars($a['note'] ?? '') ?></td>
                            <td>
                                <?php if ($a['status'] === 'WAITING'): ?>
                                    <form method="post"
                                        action="index.php?controller=patient&action=cancelAppointment"
                                        style="display:inline-block;"
                                        onsubmit="return confirm('Bạn chắc chắn muốn hủy lịch hẹn này?');">
                                        <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
                                        <button class="btn " style = "background-color: red;">
                                            Hủy
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
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
                $link = "index.php?controller=patient&action=appointments"
                    . "&from_date=" . urlencode($fromDateView ?? '')
                    . "&to_date="   . urlencode($toDateView ?? '')
                    . "&page="      . $i;
                ?>
                <a href="<?= $link ?>" class="<?= ($i == ($currentPage ?? 1) ? 'active' : '') ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>