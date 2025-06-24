<?php

namespace App;

class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function reset_password()
    {
        $title = "Đặt lại mật khẩu";
        require __DIR__ . '/../views/auth/reset_password.php';
    }
}
