<?php

namespace App;

use PDO;

class Permission
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getPermissionById($permission_id)
    {
        $query = "SELECT * FROM permissions WHERE permission_id = :permission_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':permission_id', $permission_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllPermissions()
    {
        $query = "SELECT * FROM permissions";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPermission($permission_name, $description)
    {
        $query = "INSERT INTO permissions (permission_name, description) VALUES (:permission_name, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':permission_name', $permission_name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
