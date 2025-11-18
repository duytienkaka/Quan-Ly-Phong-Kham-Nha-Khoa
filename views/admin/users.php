<section class="admin-users">
    <h1>Quản lý tài khoản</h1>
    <p class="subtitle">Xem và lọc danh sách tài khoản trong hệ thống.</p>

    <!-- Bộ lọc -->
    <form method="get" action="index.php" class="admin-filter-form">
        <input type="hidden" name="controller" value="admin">
        <input type="hidden" name="action" value="users">

        <div class="admin-filter-row">
            <div class="filter-group">
                <label>Từ khóa</label>
                <input type="text" name="q" placeholder="Tên đăng nhập, họ tên, email..."
                    value="<?= htmlspecialchars($keywordView ?? '') ?>">
            </div>

            <div class="filter-group">
                <label>Vai trò</label>
                <select name="role">
                    <option value="">-- Tất cả --</option>
                    <option value="admin" <?= ($roleView === 'admin' ? 'selected' : '') ?>>Admin</option>
                    <option value="receptionist" <?= ($roleView === 'receptionist' ? 'selected' : '') ?>>Lễ tân</option>
                    <option value="doctor" <?= ($roleView === 'doctor' ? 'selected' : '') ?>>Bác sĩ</option>
                    <option value="patient" <?= ($roleView === 'patient' ? 'selected' : '') ?>>Bệnh nhân</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="">-- Tất cả --</option>
                    <option value="1" <?= ($statusView === '1' ? 'selected' : '') ?>>Hoạt động</option>
                    <option value="0" <?= ($statusView === '0' ? 'selected' : '') ?>>Khóa</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="index.php?controller=admin&action=users" class="btn-secondary">Xóa lọc</a>
            </div>
        </div>
    </form>
    <div class="admin-import-box">
        <div class="import-container">
            <form method="post"
                action="index.php?controller=admin&action=importUsers"
                enctype="multipart/form-data"
                class="import-form">
                <label>Import tài khoản từ CSV</label>
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" class="btn-secondary">Import</button>
                <p class="hint">
                    Định dạng CSV: username, full_name, email, phone, role, status, password<br>
                    (status: 1 = hoạt động, 0 = khóa)
                </p>
            </form>
            <div class="import-guide">
                <h4>Ví dụ định dạng CSV</h4>
                <table class="format-table">
                    <thead>
                        <tr>
                            <th>username</th>
                            <th>full_name</th>
                            <th>email</th>
                            <th>phone</th>
                            <th>role</th>
                            <th>status</th>
                            <th>password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>admin01</td>
                            <td>Nguyễn Văn A</td>
                            <td>admin@clinic.com</td>
                            <td>0123456789</td>
                            <td>admin</td>
                            <td>1</td>
                            <td>Pass@123</td>
                        </tr>
                        <tr>
                            <td>doctor01</td>
                            <td>Trần Thị B</td>
                            <td>doctor@clinic.com</td>
                            <td>0987654321</td>
                            <td>doctor</td>
                            <td>1</td>
                            <td>Pass@456</td>
                        </tr>
                        <tr>
                            <td>patient01</td>
                            <td>Phạm Văn C</td>
                            <td>patient@clinic.com</td>
                            <td>0912345678</td>
                            <td>patient</td>
                            <td>1</td>
                            <td>Pass@789</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách -->
    <div class="admin-panel">
        <div class="panel-header">
            <h2>Danh sách tài khoản</h2>
            <a href="index.php?controller=admin&action=createUser" class="btn-primary btn-sm">
                + Thêm tài khoản
            </a>
        </div>


        <?php if (empty($users)): ?>
            <p>Không tìm thấy tài khoản nào phù hợp.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['user_id']) ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
                            <td>
                                <?php
                                switch ($u['role']) {
                                    case 'admin':
                                        echo '<span class="tag tag-admin">Admin</span>';
                                        break;
                                    case 'receptionist':
                                        echo '<span class="tag tag-receptionist">Lễ tân</span>';
                                        break;
                                    case 'doctor':
                                        echo '<span class="tag tag-doctor">Bác sĩ</span>';
                                        break;
                                    case 'patient':
                                        echo '<span class="tag tag-patient">Bệnh nhân</span>';
                                        break;
                                    default:
                                        echo htmlspecialchars($u['role']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ((int)$u['status'] === 1): ?>
                                    <span class="tag tag-active">Hoạt động</span>
                                <?php else: ?>
                                    <span class="tag tag-inactive">Khóa</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['created_at']) ?></td>
                            <td>
                                <a href="index.php?controller=admin&action=editUser&id=<?= $u['user_id'] ?>" class="btn-xs">
                                    Sửa
                                </a>
                                <a href="index.php?controller=admin&action=toggleUserStatus&id=<?= $u['user_id'] ?>" class="btn-xs">
                                    <?= (int)$u['status'] === 1 ? 'Khóa' : 'Mở khóa' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Phân trang -->
            <?php if (!empty($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                        $link = "index.php?controller=admin&action=users"
                            . "&q="      . urlencode($keywordView ?? '')
                            . "&role="   . urlencode($roleView ?? '')
                            . "&status=" . urlencode($statusView ?? '')
                            . "&page="   . $i;
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