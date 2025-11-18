<?php
// controllers/AuthController.php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function login()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionType = $_POST['action_type'] ?? 'login';

            // ĐĂNG NHẬP
            if ($actionType === 'login') {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                $user = User::findByUsername($username);

                // TẠM: so sánh plain text
                if ($user && $user['password_hash'] === $password) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role']    = $user['role'];

                    // redirect theo role
                    switch ($user['role']) {
                        case 'patient':
                            header('Location: index.php?controller=patient&action=dashboard');
                            break;
                        case 'admin':
                            header('Location: index.php?controller=admin&action=dashboard');
                            break;
                        case 'receptionist':
                            header('Location: index.php?controller=receptionist&action=dashboard');
                            break;
                        case 'doctor':
                            header('Location: index.php?controller=doctor&action=dashboard');
                            break;
                        default:
                            header('Location: index.php');
                            break;
                    }
                    exit;
                } else {
                    $error = 'Sai tên đăng nhập hoặc mật khẩu.';
                }
            }

            if ($actionType === 'register') {
                $username  = trim($_POST['username'] ?? '');
                $password  = $_POST['password'] ?? '';
                $password2 = $_POST['password2'] ?? '';

                if ($password !== $password2) {
                    $error = 'Mật khẩu nhập lại không khớp.';
                } elseif (User::findByUsername($username)) {
                    $error = 'Tên đăng nhập đã tồn tại.';
                } else {
                    $userId = User::createPatientUser($username, $password);

                    $_SESSION['user_id'] = $userId;
                    $_SESSION['role']    = 'patient';

                    header('Location: index.php?controller=patient&action=dashboard');
                    exit;
                }
            }
        }

        $pageTitle = 'Đăng nhập / Đăng ký';
        $view      = __DIR__ . '/../views/auth/login.php';
        include __DIR__ . '/../views/layouts/public_layout.php';
    }
}
