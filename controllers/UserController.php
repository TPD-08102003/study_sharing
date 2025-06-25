<?php

namespace App;

use PDOException;

require 'vendor/autoload.php';

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
        date_default_timezone_set('Asia/Ho_Chi_Minh');
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
}
