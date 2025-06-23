<?php

namespace App;

use PDO;

class Comment
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getCommentById($comment_id)
    {
        $query = "SELECT * FROM comments WHERE comment_id = :comment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCommentsByDocumentId($document_id)
    {
        $query = "SELECT * FROM comments WHERE document_id = :document_id ORDER BY comment_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createComment($document_id, $account_id, $comment_text)
    {
        $query = "INSERT INTO comments (document_id, account_id, comment_text) VALUES (:document_id, :account_id, :comment_text)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment_text', $comment_text, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteComment($comment_id)
    {
        $query = "DELETE FROM comments WHERE comment_id = :comment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
