<?php

namespace App;

use PDO;

class DocumentTag
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTagById($document_tag_id)
    {
        $query = "SELECT * FROM document_tags WHERE document_tag_id = :document_tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_tag_id', $document_tag_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addTagToDocument($document_id, $tag_id)
    {
        $query = "INSERT INTO document_tags (document_id, tag_id) VALUES (:document_id, :tag_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function removeTagFromDocument($document_tag_id)
    {
        $query = "DELETE FROM document_tags WHERE document_tag_id = :document_tag_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_tag_id', $document_tag_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
