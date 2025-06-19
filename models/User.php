<?php

namespace App;

use PDO;

class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUserById($user_id)
    {
        $query = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers()
    {
        $query = "SELECT * FROM users";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $email, $password, $full_name, $role = 'student', $status = 'active', $avatar = null)
    {
        $query = "INSERT INTO users (username, email, password, full_name, role, status, avatar) VALUES (:username, :email, :password, :full_name, :role, :status, :avatar)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR, 255);
        return $stmt->execute();
    }

    public function updateUser($user_id, $username, $email, $password, $full_name, $role, $status, $avatar = null)
    {
        $query = "UPDATE users SET username = :username, email = :email, password = :password, full_name = :full_name, role = :role, status = :status, avatar = :avatar, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR, 255);
        return $stmt->execute();
    }

    public function deleteUser($user_id)
    {
        $query = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateUserAvatar($user_id, $avatar)
    {
        $query = "UPDATE users SET avatar = :avatar, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR, 255);
        return $stmt->execute();
    }

    public function getUserPermissions($user_id)
    {
        $query = "SELECT p.permission_name FROM permissions p
                JOIN user_permissions up ON p.permission_id = up.permission_id
                WHERE up.user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_name');
    }

    public function getUserByUsernameOrEmail($username_or_email)
    {
        $query = "SELECT * FROM users WHERE username = :username_or_email OR email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username_or_email', $username_or_email, PDO::PARAM_STR);
        $stmt->bindParam(':email', $username_or_email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
