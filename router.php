<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

use App\UserController;
// thêm các controller khác nếu cần

session_start();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace('/study_sharing', '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Debug: Ghi log URI và method
error_log("URI: $uri, Method: $method");

$parts = explode('/', $uri);
$controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'HomeController';
$action = !empty($parts[1]) ? $parts[1] : 'index';
$params = array_slice($parts, 2);

// Debug: Ghi log controller và action
error_log("Controller: $controllerName, Action: $action");

$allowedControllers = [
    'UserController' => UserController::class,
    // Thêm các controller khác nếu cần
    // 'DocumentController' => DocumentController::class,
];

if (array_key_exists($controllerName, $allowedControllers)) {
    $controllerClass = $allowedControllers[$controllerName];
    // Debug: Ghi log controller class
    error_log("Controller class: $controllerClass");
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass($pdo);
        if (method_exists($controller, $action) && is_callable([$controller, $action])) {
            if (in_array($method, ['POST', 'GET'])) {
                ob_start();
                call_user_func_array([$controller, $action], $params);
                $output = ob_get_clean();
                // Nếu đã chuyển hướng (header location), không trả về gì nữa
                if (!headers_sent() && !empty($output)) {
                    echo $output;
                }
                exit;
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => "Action '$action' not found in $controllerName"]);
        }
    } else {
        require_once __DIR__ . '/index.php';
        exit;
    }
} else {
    require_once __DIR__ . '/index.php';
    exit;
}
