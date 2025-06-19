<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tải autoload và kết nối PDO
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

// Hàm gọi controller và action
function call($controller, $action, $pdo)
{
    // Tải file controller (dùng namespace thay vì require_once trực tiếp)
    $controllerClass = "App\\" . ucfirst($controller) . "Controller";

    // Debug: Ghi log controller và action
    error_log("Calling Controller: $controllerClass, Action: $action");

    // Kiểm tra xem lớp controller có tồn tại không
    if (class_exists($controllerClass)) {
        // Tạo instance của controller, truyền PDO
        $controllerInstance = new $controllerClass($pdo);

        // Kiểm tra xem action có tồn tại không
        if (method_exists($controllerInstance, $action)) {
            // Gọi action
            $controllerInstance->$action();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => "Action '$action' not found in $controllerClass"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => "Controller class '$controllerClass' not found"]);
    }
}

// Mảng các controller và action được phép
$controllers = [
    'user' => ['login', 'register', 'updateProfile'],
    // Thêm các controller khác nếu cần, ví dụ:
    // 'document' => ['list', 'upload', 'delete'],
];

// Xử lý URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace('/study_sharing', '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Debug: Ghi log URI và method
error_log("URI: $uri, Method: $method");

// Phân tách URI thành controller và action
$parts = explode('/', $uri);
$controller = !empty($parts[0]) ? strtolower($parts[0]) : 'home';
$action = !empty($parts[1]) ? $parts[1] : 'index';

// Kiểm tra controller và action có được phép không
if (array_key_exists($controller, $controllers)) {
    if (in_array($action, $controllers[$controller])) {
        if ($method === 'POST' || $method === 'GET') {
            call($controller, $action, $pdo);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => "Invalid action '$action' for controller '$controller'"]);
    }
} else {
    // Nếu controller không tồn tại, gọi trang chủ hoặc lỗi
    if ($controller === 'home' && $action === 'index') {
        require_once __DIR__ . '/index.php';
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => "Controller '$controller' not found"]);
    }
}
