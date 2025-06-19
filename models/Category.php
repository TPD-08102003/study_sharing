<?php

namespace App;

use PDO;

class Category
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getCategoryById($category_id)
    {
        $query = "SELECT * FROM categories WHERE category_id = :category_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCategories()
    {
        $query = "SELECT * FROM categories";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCategory($category_name, $description = null)
    {
        $query = "INSERT INTO categories (category_name, description) VALUES (:category_name, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_name', $category_name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function updateCategory($category_id, $category_name, $description)
    {
        $query = "UPDATE categories SET category_name = :category_name, description = :description WHERE category_id = :category_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':category_name', $category_name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteCategory($category_id)
    {
        $query = "DELETE FROM categories WHERE category_id = :category_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
