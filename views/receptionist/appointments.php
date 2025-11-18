<section class="rc-dashboard">
    <h1>Quản lý lịch hẹn</h1>
    <p class="rc-subtitle">
        Xem và điều phối lịch hẹn theo ngày.
    </p>

    <form method="get" action="index.php" class="rc-toolbar rc-toolbar-form">
        <input type="hidden" name="controller" value="receptionist">
        <input type="hidden" name="action" value="appointments">

        <div>
            <label>Ngày</label><br>
            <input type="date" name="date"
                value="<?= htmlspecialchars($dateView ?? date('Y-m-d')) ?>">
        </div>

        <div>
            <label>Trạng thái</label><br>
            <?php $st = $statusView ?? ''; ?>
            <select name="status">
                <option value="">-- Tất cả --</option>
                <option value="WAITING" <?= $st === 'WAITING'     ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="IN_PROGRESS" <?= $st === 'IN_PROGRESS' ? 'selected' : '' ?>>Đang khám</option>
                <option value="COMPLETED" <?= $st === 'COMPLETED'   ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="CANCELLED" <?= $st === 'CANCELLED'   ? 'selected' : '' ?>>Đã hủy</option>
                <option value="NO_SHOW" <?= $st === 'NO_SHOW'     ? 'selected' : '' ?>>Không đến</option>
            </select>
        </div>

        <div>
            <label>Tìm kiếm</label><br>
            <input type="text" name="q"
                placeholder="Tên bệnh nhân / SĐT..."
                value="<?= htmlspecialchars($keywordView ?? '') ?>">
        </div>

        <div class="rc-toolbar-actions">
            <button type="submit" class="btn-primary">Lọc</button>
            <a href="index.php?controller=receptionist&action=appointments" class="btn-secondary">
                Xóa lọc
            </a>
        </div>

        <div style="margin-left:auto;">
            <a href="index.php?controller=receptionist&action=createAppointment" class="btn-primary">
                + Tạo lịch hẹn
            </a>
        </div>
    </form>

    <div class="rc-panel" style="margin-top: 12px;">
        <div class="rc-panel-header">
            <h2>Danh sách lịch hẹn</h2>
            <span class="rc-panel-note">
                Ngày <?= htmlspecialchars($dateView ?? '') ?>,
                tổng <?= (int)($totalRowsView ?? 0) ?> lịch hẹn.
            </span>
        </div>

        <?php if (empty($appointmentsView)): ?>
            <div class="empty-state">
                <div class="icon">🗓️</div>
                <h3>Không có lịch hẹn</h3>
                <p>Không tìm thấy lịch hẹn theo điều kiện lọc hiện tại.</p>
                <a href="index.php?controller=receptionist&action=createAppointment" class="btn-primary">Tạo lịch hẹn mới</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="rc-table" role="table">
                    <thead>
                        <tr>
                            <th>Ngày / Buổi</th>
                            <th>Bệnh nhân</th>
                            <th>Bác sĩ</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointmentsView as $a): ?>
                            <tr>
                                <td data-label="Ngày / Buổi">
                                    <?php
                                    $dt = strtotime($a['appointment_date']);
                                    $day = date('d/m', $dt);

                                    $block = $a['time_block'] ?? '';
                                    switch ($block) {
                                        case 'MORNING':
                                            $blockLabel = 'Sáng';
                                            break;
                                        case 'AFTERNOON':
                                            $blockLabel = 'Chiều';
                                            break;
                                        case 'EVENING':
                                            $blockLabel = 'Tối';
                                            break;
                                        default:
                                            $blockLabel = '';
                                    }

                                    echo $day;
                                    if ($blockLabel !== '') {
                                        echo ' - ' . $blockLabel;
                                    }
                                    ?>
                                </td>
                                <td data-label="Bệnh nhân">
                                    <div>
                                        <strong><?= htmlspecialchars($a['patient_name']) ?></strong>
                                        <div class="muted"><?= htmlspecialchars($a['patient_phone'] ?? '') ?></div>
                                    </div>
                                </td>
                                <td data-label="Bác sĩ"><?= htmlspecialchars($a['doctor_name'] ?? 'Chưa gán') ?></td>
                                <td data-label="Trạng thái">
                                    <?php
                                    $st2 = $a['status'];
                                    if ($st2 === 'WAITING')     echo '<span class="tag tag-pending">Chờ duyệt</span>';
                                    elseif ($st2 === 'IN_PROGRESS') echo '<span class="tag tag-inprogress">Đang khám</span>';
                                    elseif ($st2 === 'COMPLETED')   echo '<span class="tag tag-done">Hoàn thành</span>';
                                    elseif ($st2 === 'CANCELLED')   echo '<span class="tag tag-canceled">Đã hủy</span>';
                                    elseif ($st2 === 'NO_SHOW')     echo '<span class="tag tag-noshow">Không đến</span>';
                                    else                             echo '<span class="tag">' . htmlspecialchars($st2) . '</span>';
                                    ?>
                                </td>
                                <td data-label="Ghi chú"><?= htmlspecialchars($a['note'] ?? '') ?></td>
                                <td data-label="Thao tác">
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

            <?php if (!empty($totalPagesView) && $totalPagesView > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPagesView; $i++): ?>
                        <?php
                        $link = "index.php?controller=receptionist&action=appointments"
                            . "&date=" . urlencode($dateView ?? '')
                            . "&status=" . urlencode($statusView ?? '')
                            . "&q=" . urlencode($keywordView ?? '')
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