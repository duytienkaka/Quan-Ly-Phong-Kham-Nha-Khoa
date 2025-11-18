<section class="admin-doctors">
    <h1>Quản lý bệnh nhân</h1>
    <p class="subtitle">
        Danh sách bệnh nhân đã đăng ký tài khoản và có hồ sơ tại phòng khám.
    </p>

    <!-- Bộ lọc -->
    <form method="get" action="index.php" class="admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="patients">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Tên bệnh nhân</label>
                <input type="text" name="q" placeholder="Nhập tên bệnh nhân..."
                    value="<?= htmlspecialchars($keywordView ?? '') ?>">
            </div>

            <div class="filter-group">
                <label>Giới tính</label>
                <select name="gender">
                    <option value="">-- Tất cả --</option>
                    <option value="M" <?= ($genderView === 'M' ? 'selected' : '') ?>>Nam</option>
                    <option value="F" <?= ($genderView === 'F' ? 'selected' : '') ?>>Nữ</option>
                    <option value="O" <?= ($genderView === 'O' ? 'selected' : '') ?>>Khác</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="index.php?controller=admin&action=patients" class="btn-secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Danh sách bệnh nhân</h2>
        </div>

        <?php if (empty($patients)): ?>
            <p>Chưa có bệnh nhân nào hoặc không tìm thấy theo điều kiện lọc.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Liên hệ</th>
                        <th>Địa chỉ</th>
                        <th>Tài khoản</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?= (int)$p['patient_id'] ?></td>
                            <td><?= htmlspecialchars($p['full_name']) ?></td>
                            <td>
                                <?php
                                if ($p['gender'] === 'M') echo 'Nam';
                                elseif ($p['gender'] === 'F') echo 'Nữ';
                                elseif ($p['gender'] === 'O') echo 'Khác';
                                else echo '—';
                                ?>
                            </td>
                            <td><?= htmlspecialchars($p['date_of_birth'] ?? '—') ?></td>
                            <td>
                                <div><?= htmlspecialchars($p['phone'] ?? 'Chưa có') ?></div>
                                <div><?= htmlspecialchars($p['email'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($p['address'] ?? '') ?></td>
                            <td>
                                <?= htmlspecialchars($p['username'] ?? '—') ?>
                            </td>
                            <td>
                                <?php if (isset($p['status']) && (int)$p['status'] === 1): ?>
                                    <span class="tag tag-active">Hoạt động</span>
                                <?php elseif (isset($p['status'])): ?>
                                    <span class="tag tag-inactive">Khóa</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['note'] ?? '') ?></td>

                            <!-- Thao tác -->
                            <td>
                                <a href="index.php?controller=admin&action=patientHistory&id=<?= (int)$p['patient_id'] ?>"
                                    class="btn-xs">
                                    Lịch sử khám
                                </a>
                                <br>
                                <a href="index.php?controller=admin&action=deletePatient&id=<?= (int)$p['patient_id'] ?>"
                                    class="btn-xs"
                                    onclick="return confirm('Xóa bệnh nhân này và tài khoản liên quan? Hành động này không thể hoàn tác.');">
                                    Xóa tài khoản
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($totalPagesView) && $totalPagesView > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPagesView; $i++): ?>
                        <?php
                        $link = "index.php?controller=admin&action=patients"
                            . "&q=" . urlencode($keywordView ?? '')
                            . "&gender=" . urlencode($genderView ?? '')
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