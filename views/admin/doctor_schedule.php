<?php
$days = [
    1 => 'Thứ 2',
    2 => 'Thứ 3',
    3 => 'Thứ 4',
    4 => 'Thứ 5',
    5 => 'Thứ 6',
    6 => 'Thứ 7',
    7 => 'Chủ nhật',
];
?>

<section class="admin-user-form">
    <h1>Lịch làm việc: <?= htmlspecialchars($doctorView['doctor_name']) ?></h1>
    <p class="subtitle">
        Để trống giờ bắt đầu/kết thúc nếu ngày đó nghỉ.
    </p>

    <?php if (!empty($errorView)): ?>
        <div class="form-error-box"><?= htmlspecialchars($errorView) ?></div>
    <?php endif; ?>

    <?php if (!empty($noticeView)): ?>
        <div class="form-success-box"><?= htmlspecialchars($noticeView) ?></div>
    <?php endif; ?>

    <form method="post" class="form-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Thứ</th>
                    <th>Giờ bắt đầu</th>
                    <th>Giờ kết thúc</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($days as $w => $label): 
                $row = $scheduleView[$w] ?? null;
            ?>
                <tr>
                    <td><?= $label ?></td>
                    <td>
                        <input type="time" name="start_time[<?= $w ?>]"
                               value="<?= $row['start_time'] ?? '' ?>">
                    </td>
                    <td>
                        <input type="time" name="end_time[<?= $w ?>]"
                               value="<?= $row['end_time'] ?? '' ?>">
                    </td>
                    <td>
                        <input type="text" name="note[<?= $w ?>]"
                               value="<?= htmlspecialchars($row['note'] ?? '') ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="form-actions" style="margin-top:12px;">
            <button type="submit" class="btn-primary">Lưu lịch</button>
            <a href="index.php?controller=admin&action=doctors" class="btn-secondary">Quay lại</a>
        </div>
    </form>
</section>
