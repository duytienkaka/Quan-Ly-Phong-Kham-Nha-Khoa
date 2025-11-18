<?php
// index.php
session_start();

// autoload rất đơn giản
require_once __DIR__ . '/config/database.php';

// hàm load controller
function loadController($name) {
    $file = __DIR__ . '/controllers/' . $name . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$actionName     = isset($_GET['action']) ? $_GET['action'] : 'index';

// Ví dụ: home -> HomeController
$className = ucfirst($controllerName) . 'Controller';

if (!loadController($className) || !class_exists($className)) {
    http_response_code(404);
    echo "Không tìm thấy controller.";
    exit;
}

$controller = new $className();

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo "Không tìm thấy action.";
    exit;
}

// Gọi action
$controller->$actionName();
