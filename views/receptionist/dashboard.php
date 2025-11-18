<section class="rc-dashboard">
    <h1>Dashboard lễ tân</h1>
    <p class="rc-subtitle">
        Tổng quan lịch hẹn trong ngày và tình trạng bệnh nhân.
    </p>

    <div class="rc-stats-row">
        <div class="rc-stat-card rc-stat-total">
            <div class="rc-stat-label">Tổng lịch hẹn hôm nay</div>
            <div class="rc-stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
        </div>
        <div class="rc-stat-card rc-stat-pending">
            <div class="rc-stat-label">Chờ khám (WAITING)</div>
            <div class="rc-stat-value"><?= (int)($stats['waiting'] ?? 0) ?></div>
        </div>
        <div class="rc-stat-card rc-stat-in-progress">
            <div class="rc-stat-label">Đang khám (IN_PROGRESS)</div>
            <div class="rc-stat-value"><?= (int)($stats['in_progress'] ?? 0) ?></div>
        </div>
        <div class="rc-stat-card rc-stat-done">
            <div class="rc-stat-label">Hoàn thành (COMPLETED)</div>
            <div class="rc-stat-value"><?= (int)($stats['completed'] ?? 0) ?></div>
        </div>
        <div class="rc-stat-card rc-stat-canceled">
            <div class="rc-stat-label">Đã hủy (CANCELLED)</div>
            <div class="rc-stat-value"><?= (int)($stats['cancelled'] ?? 0) ?></div>
        </div>
        <div class="rc-stat-card rc-stat-noshow">
            <div class="rc-stat-label">Không đến (NO_SHOW)</div>
            <div class="rc-stat-value"><?= (int)($stats['no_show'] ?? 0) ?></div>
        </div>
    </div>

    <div class="rc-toolbar">
        <a href="index.php?controller=receptionist&action=createAppointment" class="btn-primary">
            + Tạo lịch hẹn mới
        </a>

        <div class="rc-toolbar-right">
            <form method="get" action="index.php" class="rc-toolbar-form">
                <input type="hidden" name="controller" value="receptionist">
                <input type="hidden" name="action" value="appointments">
                <input type="date" name="date" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
                <button type="submit" class="btn-secondary">Xem lịch ngày khác</button>
            </form>
        </div>
    </div>

    <div class="rc-panel">
        <div class="rc-panel-header">
            <h2>Lịch hẹn hôm nay</h2>
            <span class="rc-panel-note">Hiển thị tối đa 20 lịch hẹn gần nhất trong ngày.</span>
        </div>

        <?php if (empty($appointmentsToday)): ?>
            <p>Hôm nay chưa có lịch hẹn nào.</p>
        <?php else: ?>
            <div class="table-wrap">
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>Giờ</th>
                        <th>Bệnh nhân</th>
                        <th>Bác sĩ</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appointmentsToday as $a): ?>
                    <tr>
                        <td>
                            <?php
                                $dt = strtotime($a['appointment_date']);
                                echo date('H:i d/m', $dt);
                            ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($a['patient_name']) ?></strong><br>
                            <small><?= htmlspecialchars($a['patient_phone'] ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($a['doctor_name'] ?? 'Chưa gán') ?></td>
                        <td>
                            <?php
                                $st = $a['status'];
                                if ($st === 'WAITING')           echo '<span class="tag tag-pending">Chờ duyệt</span>';
                                elseif ($st === 'IN_PROGRESS')   echo '<span class="tag tag-inprogress">Đang khám</span>';
                                elseif ($st === 'COMPLETED')     echo '<span class="tag tag-done">Hoàn thành</span>';
                                elseif ($st === 'CANCELLED')     echo '<span class="tag tag-canceled">Đã hủy</span>';
                                elseif ($st === 'NO_SHOW')       echo '<span class="tag tag-noshow">Không đến</span>';
                                else                             echo '<span class="tag">'.htmlspecialchars($st).'</span>';
                            ?>
                        </td>
                        <td><?= htmlspecialchars($a['note'] ?? '') ?></td>
                        <td>
                            <a href="index.php?controller=receptionist&action=appointmentDetail&id=<?= (int)$a['appointment_id'] ?>"
                               class="btn-xs">
                                Xem / xử lý
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</section>
