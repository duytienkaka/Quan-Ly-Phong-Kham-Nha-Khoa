<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?></title>
    <link rel="stylesheet" href="/dental_clinic/public/css/public.css">
    <link rel="stylesheet" href="/dental_clinic/public/css/admin.css">
</head>

<body>

    <header class="admin-header">
        <div class="ah-left">
            <span class="logo">Nha Khoa <strong>Smile</strong> | Admin</span>
        </div>
        <div class="ah-right">
            <span class="ah-user">
                <?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?>
            </span>
            <a href="index.php" class="ah-link">Trang public</a>
            <a href="index.php?controller=auth&action=login" class="ah-link">Đăng xuất</a>
        </div>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>Quản trị</h3>
            <ul>
                <li><a href="index.php?controller=admin&action=dashboard">Dashboard</a></li>
                <li><a href="index.php?controller=admin&action=users">Người dùng / Tài khoản</a></li>
                <li><a href="index.php?controller=admin&action=doctors">Bác sĩ</a></li>
                <li><a href="index.php?controller=admin&action=patients">Bệnh nhân</a></li>
                <li><a href="index.php?controller=admin&action=services">Dịch vụ / Giá</a></li>
                <li><a href="index.php?controller=admin&action=invoices">Hóa đơn</a></li>
                <li><a href="index.php?controller=admin&action=backup">Sao lưu / Export Excel</a></li>
            </ul>
        </aside>

        <main class="admin-main">
            <?php
            if (isset($view)) {
                include $view;
            }
            ?>
        </main>
    </div>

</body>

</html>