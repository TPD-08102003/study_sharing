<?php

namespace App;

use PDO;

class Tag
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTagById($tag_id)
    {
        $query = "SELECT * FROM tags WHERE tag_id = :tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTags()
    {
        $query = "SELECT * FROM tags";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTag($tag_name, $description)
    {
        $query = "INSERT INTO tags (tag_name, description) VALUES (:tag_name, :description)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $tag_name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
