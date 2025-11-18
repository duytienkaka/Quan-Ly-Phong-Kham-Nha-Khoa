<?php
// views/layouts/patient_layout.php
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'Patient') ?></title>
    <link rel="stylesheet" href="/dental_clinic/public/css/public.css?v=<?= filemtime(__DIR__ . '/../../public/css/public.css') ?>">
    <link rel="stylesheet" href="/dental_clinic/public/css/patient.css?v=<?= filemtime(__DIR__ . '/../../public/css/patient.css') ?>">
</head>

<body>
    <?php
    $currentAction = $_GET['action'] ?? 'dashboard';
    $displayName = htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'User');
    $initials = '';
    if (!empty($user['full_name'])) {
        $parts = preg_split('/\s+/', trim($user['full_name']));
        $initials = strtoupper(substr(end($parts), 0, 1) . (isset($parts[0]) ? substr($parts[0], 0, 1) : ''));
    } else {
        $initials = strtoupper(substr($user['username'] ?? 'U', 0, 1));
    }
    ?>

    <header class="patient-header">
        <div class="ph-left">
            <span class="logo">Nha Khoa <strong>Smile</strong></span>
        </div>
        <div class="ph-right">
            <span class="ph-avatar" title="<?= $displayName ?>"><?= htmlspecialchars($initials) ?></span>
            <a href="index.php" class="ph-link">Trang chủ</a>
            <a href="index.php?controller=auth&action=login" class="ph-link">Đăng xuất</a>
        </div>
    </header>

    <div class="patient-layout">
        <aside class="patient-sidebar">
            <h3>Tài khoản</h3>
            <ul>
                <li><a href="index.php?controller=patient&action=dashboard" class="<?= $currentAction === 'dashboard' ? 'active' : '' ?>">Thông tin & hồ sơ</a></li>
                <li><a href="index.php?controller=patient&action=appointments" class="<?= $currentAction === 'appointments' ? 'active' : '' ?>">Lịch hẹn của tôi</a></li>
                <li><a href="index.php?controller=patient&action=booking" class="<?= $currentAction === 'booking' ? 'active' : '' ?>">Đặt lịch khám</a></li>
                <li><a href="index.php?controller=patient&action=history" class="<?= $currentAction === 'history' ? 'active' : '' ?>">Lịch sử khám</a></li>
                <li><a href="index.php?controller=patient&action=invoices" class="<?= $currentAction === 'invoices' ? 'active' : '' ?>">Hóa đơn</a></li>
            </ul>
        </aside>

        <main class="patient-main">
            <?php if (isset($view)) include $view; ?>
        </main>
    </div>
</body>

</html>