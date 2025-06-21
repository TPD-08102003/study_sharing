<?php
// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

use App\UserController;

session_start();

// Lấy URI và loại bỏ prefix '/study_sharing'
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace('/study_sharing', '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Debug: Ghi log URI và method
error_log("URI: $uri, Method: $method");

// Định nghĩa các tuyến đường tĩnh
$staticRoutes = [
    'auth/reset_password' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/auth/reset_password.php',
        'title' => 'Đặt lại mật khẩu',
        'layout' => 'layout.php'
    ],
    'HomeAdmin/index' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/HomeAdmin/index.php',
        'title' => 'Bảng điều khiển Admin',
        'layout' => 'admin_layout.php'
    ],
    '' => [ // Trang chủ
        'method' => 'GET',
        'view' => __DIR__ . '/views/home/index.php',
        'title' => 'Trang chủ',
        'layout' => 'layout.php'
    ],
    'document/approve' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/document/approve.php',
        'title' => 'Phê duyệt tài liệu',
        'layout' => 'admin_layout.php'
    ],
    'document/statistics' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/document/statistics.php',
        'title' => 'Thống kê tài liệu',
        'layout' => 'admin_layout.php'
    ],
    'document/my_documents' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/document/my_documents.php',
        'title' => 'Tài liệu của tôi',
        'layout' => 'layout.php'
    ],
    'course/approve' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/course/approve.php',
        'title' => 'Phê duyệt khóa học',
        'layout' => 'admin_layout.php'
    ],
    'course/statistics' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/course/statistics.php',
        'title' => 'Thống kê khóa học',
        'layout' => 'admin_layout.php'
    ],
    'course/my_courses' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/course/my_courses.php',
        'title' => 'Khóa học của tôi',
        'layout' => 'layout.php'
    ],
    'user/manage_users' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/user/manage_users.php',
        'title' => 'Quản lý người dùng',
        'layout' => 'admin_layout.php'
    ],
    'category/manage' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/category/manage.php',
        'title' => 'Quản lý danh mục',
        'layout' => 'admin_layout.php'
    ],
    'tag/manage' => [
        'method' => 'GET',
        'view' => __DIR__ . '/views/tag/manage.php',
        'title' => 'Quản lý thẻ',
        'layout' => 'admin_layout.php'
    ]
];

// Định nghĩa các controller được phép
$allowedControllers = [
    'UserController' => UserController::class,
];

// Xử lý tuyến đường
function handleRoute($uri, $method, $pdo, $staticRoutes, $allowedControllers)
{
    // Kiểm tra tuyến đường tĩnh
    if (array_key_exists($uri, $staticRoutes)) {
        $route = $staticRoutes[$uri];
        if ($method === $route['method']) {
            $title = $route['title'];
            $layout = $route['layout'];
            ob_start();
            require $route['view'];
            $content = ob_get_clean();
            require __DIR__ . '/views/layouts/' . $layout;
            exit;
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
    }

    // Xử lý tuyến đường động (controller-based)
    $parts = explode('/', $uri);
    $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'HomeController';
    $action = !empty($parts[1]) ? $parts[1] : 'index';
    $params = array_slice($parts, 2);

    // Debug: Ghi log controller và action
    error_log("Controller: $controllerName, Action: $action");

    if (array_key_exists($controllerName, $allowedControllers)) {
        $controllerClass = $allowedControllers[$controllerName];
        error_log("Controller class: $controllerClass");
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass($pdo);
            if (method_exists($controller, $action) && is_callable([$controller, $action])) {
                if (in_array($method, ['POST', 'GET'])) {
                    ob_start();
                    call_user_func_array([$controller, $action], $params);
                    $output = ob_get_clean();
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
                exit;
            }
        }
    }

    // Nếu không khớp, trả về 404
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Page not found']);
    exit;
}

// Gọi hàm xử lý tuyến đường
handleRoute($uri, $method, $pdo, $staticRoutes, $allowedControllers);
