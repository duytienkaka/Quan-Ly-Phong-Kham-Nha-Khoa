<?php
// $pageTitle, $view, $user (User::findById) được truyền từ controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'Lễ tân - Nha khoa') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS riêng cho lễ tân -->
    <link rel="stylesheet" href="public/css/receptionist.css">
</head>
<body class="receptionist-body">

<header class="rc-header">
    <div class="rc-header-left">
        <div class="rc-logo">
            <span class="rc-logo-icon">🦷</span>
            <span class="rc-logo-text">
                Nha Khoa <strong>Smile</strong>
            </span>
        </div>
        <nav class="rc-nav">
            <?php $action = $_GET['action'] ?? 'dashboard'; ?>
            <a href="index.php?controller=receptionist&action=dashboard"
               class="rc-nav-link <?= $action === 'dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>
            <a href="index.php?controller=receptionist&action=appointments"
               class="rc-nav-link <?= $action === 'appointments' ? 'active' : '' ?>">
                Lịch hẹn
            </a>
            <a href="index.php?controller=receptionist&action=invoices"
               class="rc-nav-link <?= $action === 'invoices' ? 'active' : '' ?>">
                Hóa đơn
            </a>
        </nav>
    </div>

    <div class="rc-header-right">
        <span class="rc-user-name">
            <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Lễ tân') ?>
        </span>
        <a href="index.php" class="rc-header-link">Trang chủ</a>
        <a href="index.php?controller=auth&action=login" class="rc-header-link">Đăng xuất</a>
    </div>
</header>

<main class="rc-main">
    <?php
    if (!empty($view) && file_exists($view)) {
        include $view;
    } else {
        echo "<p>Không tìm thấy view.</p>";
    }
    ?>
</main>

<footer class="rc-footer">
    <span>© <?= date('Y') ?> Nha khoa Smile - Lễ tân</span>
</footer>
</footer>

<!-- Toast container (used by receptionist JS) -->
<div id="rc-toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- Receptionist scripts -->
<script src="public/js/receptionist.js"></script>

</body>
</html>
