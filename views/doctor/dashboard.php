<section class="dc-dashboard">
    <h1>Xin chào, bác sĩ <?= htmlspecialchars($userView['full_name'] ?? '') ?></h1>
    <p class="dc-subtitle">Tổng quan lịch hẹn trong ngày hôm nay.</p>

    <div class="dc-stats-grid">
        <div class="dc-stat-card waiting">
            <span class="label">Chờ khám</span>
            <span class="value"><?= (int)$statsView['WAITING'] ?></span>
        </div>
        <div class="dc-stat-card inprogress">
            <span class="label">Đang khám</span>
            <span class="value"><?= (int)$statsView['IN_PROGRESS'] ?></span>
        </div>
        <div class="dc-stat-card done">
            <span class="label">Hoàn thành</span>
            <span class="value"><?= (int)$statsView['COMPLETED'] ?></span>
        </div>
        <div class="dc-stat-card cancel">
            <span class="label">Đã hủy</span>
            <span class="value"><?= (int)$statsView['CANCELLED'] ?></span>
        </div>
        <div class="dc-stat-card noshow">
            <span class="label">Không đến</span>
            <span class="value"><?= (int)$statsView['NO_SHOW'] ?></span>
        </div>
    </div>

    <div class="dc-panel">
        <div class="dc-panel-header">
            <h2>Lịch hẹn hôm nay</h2>
        </div>

        <?php if (empty($appointmentsTodayView)): ?>
            <div class="empty-state">
                <h3>Hôm nay không có lịch hẹn</h3>
                <p>Bạn có thể xem lịch hẹn trong những ngày tới.</p>
            </div>
        <?php else: ?>
            <table class="dc-table">
                <thead>
                    <tr>
                        <th>Giờ</th>
                        <th>Bệnh nhân</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointmentsTodayView as $a): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($a['appointment_date'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($a['patient_name']) ?></strong><br>
                                <small><?= htmlspecialchars($a['patient_phone'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php
                                    $st = $a['status'];
                                    if ($st === 'WAITING')      echo '<span class="tag tag-waiting">Chờ khám</span>';
                                    elseif ($st === 'IN_PROGRESS') echo '<span class="tag tag-inprogress">Đang khám</span>';
                                    elseif ($st === 'COMPLETED')   echo '<span class="tag tag-done">Hoàn thành</span>';
                                    elseif ($st === 'CANCELLED')   echo '<span class="tag tag-cancel">Đã hủy</span>';
                                    elseif ($st === 'NO_SHOW')     echo '<span class="tag tag-noshow">Không đến</span>';
                                    else                           echo '<span class="tag">'.htmlspecialchars($st).'</span>';
                                ?>
                            </td>
                            <td><?= htmlspecialchars($a['note'] ?? '') ?></td>
                            <td>
                                <a href="index.php?controller=doctor&action=appointmentDetail&id=<?= (int)$a['appointment_id'] ?>" class="btn-xs">Khám</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
