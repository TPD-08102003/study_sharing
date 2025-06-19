<?php

namespace App;

use PDO;

class UserPermission
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUserPermissionById($user_permission_id)
    {
        $query = "SELECT * FROM user_permissions WHERE user_permission_id = :user_permission_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_permission_id', $user_permission_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function grantPermission($user_id, $permission_id)
    {
        $query = "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function revokePermission($user_permission_id)
    {
        $query = "DELETE FROM user_permissions WHERE user_permission_id = :user_permission_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_permission_id', $user_permission_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
