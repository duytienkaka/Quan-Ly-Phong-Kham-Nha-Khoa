<section class="dc-appointments">
    <h1>Lịch hẹn của tôi</h1>
    <p class="dc-subtitle">Quản lý lịch hẹn khám bệnh của bạn.</p>

    <form method="get" action="index.php" class="dc-filter-form">
        <input type="hidden" name="controller" value="doctor">
        <input type="hidden" name="action" value="appointments">

        <div class="form-group">
            <label>Ngày khám</label>
            <input type="date" name="date" value="<?= htmlspecialchars($dateView ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <option value="">-- Tất cả --</option>
                <option value="WAITING" <?= ($statusView ?? '') === 'WAITING' ? 'selected' : '' ?>>Chờ khám</option>
                <option value="IN_PROGRESS" <?= ($statusView ?? '') === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám</option>
                <option value="COMPLETED" <?= ($statusView ?? '') === 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="CANCELLED" <?= ($statusView ?? '') === 'CANCELLED' ? 'selected' : '' ?>>Đã hủy</option>
                <option value="NO_SHOW" <?= ($statusView ?? '') === 'NO_SHOW' ? 'selected' : '' ?>>Không đến</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tìm kiếm</label>
            <input type="text" name="q" placeholder="Tên bệnh nhân / SĐT..." value="<?= htmlspecialchars($keywordView ?? '') ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Tìm kiếm</button>
            <a href="index.php?controller=doctor&action=appointments" class="btn-secondary">Xóa lọc</a>
        </div>
    </form>

    <div class="dc-panel">
        <div class="dc-panel-header">
            <h2>Danh sách lịch hẹn</h2>
            <span class="dc-panel-note">Tổng <?= (int)($totalRowsView ?? 0) ?> lịch hẹn</span>
        </div>

        <?php if (empty($appointmentsView)): ?>
            <div class="empty-state">
                <h3>Không có lịch hẹn</h3>
                <p>Không tìm thấy lịch hẹn nào phù hợp với tiêu chí tìm kiếm.</p>
            </div>
        <?php else: ?>
            <table class="dc-table">
                <thead>
                    <tr>
                        <th>Giờ khám</th>
                        <th>Bệnh nhân</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointmentsView as $a): ?>
                        <tr>
                            <td><?= date('H:i d/m', strtotime($a['appointment_date'])) ?></td>
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

            <?php if (!empty($totalPagesView) && $totalPagesView > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPagesView; $i++): ?>
                        <a href="index.php?controller=doctor&action=appointments&date=<?= urlencode($dateView ?? '') ?>&status=<?= urlencode($statusView ?? '') ?>&q=<?= urlencode($keywordView ?? '') ?>&page=<?= $i ?>" class="<?= ($i == $currentPage ? 'active' : '') ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
