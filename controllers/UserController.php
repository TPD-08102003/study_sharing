<?php

namespace App;

use PDO;
use PDOException;

class UserController
{
    private $pdo;
    private $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
            $full_name = $_POST['full_name'] ?? '';
            $avatar = 'profile.png'; // Mặc định nếu không upload

            // Kiểm tra và xử lý upload avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/images/';
                $avatar = uniqid() . '_' . basename($_FILES['avatar']['name']);
                $uploadFile = $uploadDir . $avatar;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
                    // Thành công, sử dụng tên file đã upload
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh đại diện!']);
                    exit;
                }
            }

            if ($username && $email && $password && $full_name) {
                try {
                    $this->userModel->createUser($username, $email, $password, $full_name, 'student', 'active', $avatar);
                    echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Lỗi đăng ký: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            }
            exit;
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            exit;
        }

        try {
            $user = $this->userModel->getUserByUsernameOrEmail($username);

            if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công!']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Thông tin đăng nhập không đúng!']);
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Lỗi server, vui lòng thử lại sau!']);
        }
        exit;
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
            $full_name = $_POST['full_name'] ?? '';
            $avatar = null;

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/images/';
                $avatar = uniqid() . '_' . basename($_FILES['avatar']['name']);
                $uploadFile = $uploadDir . $avatar;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
                    // Thành công
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh đại diện!']);
                    exit;
                }
            }

            try {
                $this->userModel->updateUser($user_id, $username, $email, $password ?: '', $full_name, $_SESSION['role'], 'active', $avatar);
                echo json_encode(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()]);
            }
            exit;
        }
    }
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: /study_sharing'); // Chuyển hướng về trang chủ
        exit;
    }
}
