<section class="admin-doctors">
    <h1>Quản lý dịch vụ / Giá</h1>
    <p class="subtitle">
        Quản lý các dịch vụ nha khoa, đơn giá và trạng thái sử dụng.
    </p>

    <form method="get" action="index.php" class="admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="services">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Tìm dịch vụ</label>
                <input type="text" name="q" placeholder="Tên hoặc mô tả..."
                    value="<?= htmlspecialchars($keywordView ?? '') ?>">
            </div>

            <div class="filter-group">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="">-- Tất cả --</option>
                    <option value="1" <?= ($statusView === '1' ? 'selected' : '') ?>>Đang dùng</option>
                    <option value="0" <?= ($statusView === '0' ? 'selected' : '') ?>>Ngừng dùng</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="index.php?controller=admin&action=services" class="btn-secondary">Xóa lọc</a>
            </div>
        </div>
    </form>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>Danh sách dịch vụ</h2>
            <div class="admin-toolbar">
                <a href="index.php?controller=admin&action=createService" class="btn-primary">
                    + Thêm dịch vụ
                </a>
                <a href="index.php?controller=admin&action=importServices" class="btn-secondary">
                    Import dịch vụ (CSV)
                </a>
            </div>

        </div>

        <?php if (empty($services)): ?>
            <p>Chưa có dịch vụ nào hoặc không tìm thấy theo điều kiện lọc.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên dịch vụ</th>
                        <th>Đơn giá</th>
                        <th>Đơn vị</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td>#<?= (int)$s['service_id'] ?></td>
                            <td>
                                <?= htmlspecialchars($s['service_name']) ?>
                                <?php if (!empty($s['description'])): ?>
                                    <br><small><?= htmlspecialchars($s['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($s['unit_price'], 0, ',', '.') ?> đ</td>
                            <td><?= htmlspecialchars($s['unit'] ?? 'lần') ?></td>
                            <td>
                                <?php if ($s['is_active']): ?>
                                    <span class="tag tag-active">Đang dùng</span>
                                <?php else: ?>
                                    <span class="tag tag-inactive">Ngừng dùng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?controller=admin&action=editService&id=<?= (int)$s['service_id'] ?>"
                                    class="btn-xs">Sửa</a>
                                <br>
                                <a href="index.php?controller=admin&action=toggleServiceStatus&id=<?= (int)$s['service_id'] ?>"
                                    class="btn-xs">
                                    <?= $s['is_active'] ? 'Ngừng dùng' : 'Kích hoạt' ?>
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
                        $link = "index.php?controller=admin&action=services"
                            . "&q=" . urlencode($keywordView ?? '')
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