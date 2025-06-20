<?php

namespace App;

use PDO;
use PDOException;

class UserController
{
    private $pdo;
    private $userModel;
    private $accountModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->accountModel = new Account($pdo);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
            $full_name = $_POST['full_name'] ?? '';
            $role = $_POST['role'] ?? '';
            $date_of_birth = $_POST['date_of_birth'] ?: null;
            $phone_number = $_POST['phone_number'] ?: null;
            $address = $_POST['address'] ?: null;
            $avatar = 'profile.png';

            if (!in_array($role, ['student', 'teacher'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vai trò không hợp lệ!']);
                exit;
            }

            if ($username && $email && $password && $full_name && $role) {
                try {
                    $this->pdo->beginTransaction();

                    $account_id = $this->accountModel->createAccount($username, $email, $password, $role, 'active');

                    $this->userModel->createUser($account_id, $full_name, $avatar, $date_of_birth, $phone_number, $address);

                    $this->pdo->commit();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Đăng ký thành công!']);
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    error_log("Register error: " . $e->getMessage());
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Lỗi đăng ký: ' . $e->getMessage()]);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
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
            $account = $this->accountModel->getAccountByUsernameOrEmail($username);
            if ($account && password_verify($password, $account['password'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['account_id'] = $account['account_id'];
                $_SESSION['role'] = $account['role'];
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Đăng nhập thành công!',
                    'role' => $account['role']
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng!']);
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['account_id'])) {
            $account_id = $_SESSION['account_id'];
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
            $full_name = $_POST['full_name'] ?? '';
            $date_of_birth = $_POST['date_of_birth'] ?: null;
            $phone_number = $_POST['phone_number'] ?: null;
            $address = $_POST['address'] ?: null;
            $avatar = 'profile.png';

            try {
                $this->pdo->beginTransaction();

                $this->accountModel->updateAccount($account_id, $username, $email, $password, $_SESSION['role'], 'active');

                $this->userModel->updateUser($account_id, $full_name, $avatar, $date_of_birth, $phone_number, $address);

                $this->pdo->commit();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!']);
            } catch (PDOException $e) {
                $this->pdo->rollBack();
                error_log("Update profile error: " . $e->getMessage());
                header('Content-Type: application/json');
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
        header('Location: /study_sharing');
        exit;
    }
}
