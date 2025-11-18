<section class="admin-doctors">
    <h1>Quản lý bác sĩ</h1>
    <p class="subtitle">
        Danh sách tài khoản có vai trò <strong>Doctor</strong>. 
        Nếu bác sĩ chưa có hồ sơ chuyên môn, hệ thống sẽ yêu cầu cập nhật.
    </p>

    <!-- Bộ lọc -->
    <form method="get" action="index.php" class="admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="doctors">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Tên bác sĩ</label>
                <input type="text" name="q" placeholder="Nhập tên..."
                       value="<?= htmlspecialchars($keywordView ?? '') ?>">
            </div>

            <div class="filter-group">
                <label>Chuyên khoa</label>
                <input type="text" name="speciality" placeholder="VD: Nha chu, chỉnh nha..."
                       value="<?= htmlspecialchars($specialityView ?? '') ?>">
            </div>

            <div class="filter-group">
                <label>Trạng thái tài khoản</label>
                <select name="status">
                    <option value="">-- Tất cả --</option>
                    <option value="1" <?= ($statusView === '1' ? 'selected' : '') ?>>Hoạt động</option>
                    <option value="0" <?= ($statusView === '0' ? 'selected' : '') ?>>Khóa</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="index.php?controller=admin&action=doctors" class="btn-secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Danh sách bác sĩ</h2>
        </div>

        <?php if (empty($doctors)): ?>
            <p>Chưa có tài khoản bác sĩ nào.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên bác sĩ</th>
                        <th>Tài khoản</th>
                        <th>Chuyên khoa</th>
                        <th>Kinh nghiệm</th>
                        <th>Hồ sơ</th>
                        <th>Liên hệ</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($doctors as $d): ?>
                    <tr>
                        <td><?= (int)$d['user_id'] ?></td>
                        <td><?= htmlspecialchars($d['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($d['username']) ?></td>
                        <td><?= htmlspecialchars($d['specialization'] ?? '—') ?></td>
                        <td>
                            <?= $d['experience_years'] !== null
                                ? (int)$d['experience_years'] . ' năm'
                                : '—' ?>
                        </td>
                        <td>
                            <?php if ($d['doctor_id'] === null): ?>
                                <span class="tag tag-warn">Chưa cấu hình</span>
                            <?php else: ?>
                                <span class="tag tag-active">Đã cấu hình</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($d['phone'] ?? 'Chưa có sđt') ?></div>
                            <div><?= htmlspecialchars($d['email'] ?? '') ?></div>
                        </td>
                        <td>
                            <?php if ((int)$d['status'] === 1): ?>
                                <span class="tag tag-active">Hoạt động</span>
                            <?php else: ?>
                                <span class="tag tag-inactive">Khóa</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['created_at']) ?></td>
                        <td>
                            <a href="index.php?controller=admin&action=editDoctor&uid=<?= (int)$d['user_id'] ?>"
                               class="btn-xs">
                                Cập nhật thông tin
                            </a>

                            <?php if ($d['doctor_id'] !== null): ?>
                                <br>
                                <a href="index.php?controller=admin&action=doctorSchedule&id=<?= (int)$d['doctor_id'] ?>" class="btn-xs">
                                    Lịch làm việc
                                </a>
                                <a href="index.php?controller=admin&action=doctorStats&id=<?= (int)$d['doctor_id'] ?>" class="btn-xs">
                                    Thống kê
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($totalPagesView) && $totalPagesView > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPagesView; $i++): ?>
                        <?php
                        $link = "index.php?controller=admin&action=doctors"
                              . "&q=" . urlencode($keywordView ?? '')
                              . "&speciality=" . urlencode($specialityView ?? '')
                              . "&status=" . urlencode($statusView ?? '')
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
