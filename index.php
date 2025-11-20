<?php

session_start();

require_once __DIR__ . '/config/database.php';

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

$controller->$actionName();
