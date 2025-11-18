<?php
// $pageTitle, $view, $userView được set trong controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Bác sĩ') ?> — Nha Khoa</title>
    <link rel="stylesheet" href="public/css/doctor.css">
</head>
<body class="doctor-body">
    <header class="dc-header">
        <div class="dc-header-left">
            <div class="dc-logo">
                <span class="dc-logo-icon">🦷</span>
                <span class="dc-logo-text">Nha Khoa Smile</span>
            </div>
            <nav class="dc-nav">
                <a href="index.php?controller=doctor&action=dashboard" class="dc-nav-link">Dashboard</a>
                <a href="index.php?controller=doctor&action=appointments" class="dc-nav-link">Lịch hẹn</a>
            </nav>
        </div>
        <div class="dc-header-right">
            <div class="dc-user-menu">
                <span><?= htmlspecialchars($userView['full_name'] ?? $userView['username'] ?? 'Bác sĩ') ?></span>
                <a href="index.php?controller=auth&action=login">Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="doctor-main">
        <?php if (!empty($view) && file_exists($view)) {
            include $view;
        } ?>
    </div>
</body>
</html>
