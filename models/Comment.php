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

    public function getCommentsByDocumentId($document_id, $limit = 5, $offset = 0)
    {
        // Lấy danh sách bình luận gốc (parent_comment_id IS NULL)
        $query = "SELECT * FROM comments WHERE document_id = :document_id AND parent_comment_id IS NULL ORDER BY comment_date DESC LIMIT :offset, :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy bình luận trả lời cho mỗi bình luận gốc
        foreach ($comments as &$comment) {
            $replyQuery = "SELECT * FROM comments WHERE parent_comment_id = :parent_comment_id ORDER BY comment_date ASC";
            $replyStmt = $this->db->prepare($replyQuery);
            $replyStmt->bindParam(':parent_comment_id', $comment['comment_id'], PDO::PARAM_INT);
            $replyStmt->execute();
            $comment['replies'] = $replyStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($comment);

        // Đếm tổng số bình luận gốc
        $countQuery = "SELECT COUNT(*) FROM comments WHERE document_id = :document_id AND parent_comment_id IS NULL";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $countStmt->execute();
        $totalComments = $countStmt->fetchColumn();

        return [
            'comments' => $comments,
            'total' => $totalComments
        ];
    }

    public function createComment($document_id, $account_id, $comment_text, $parent_comment_id = null)
    {
        $query = "INSERT INTO comments (document_id, account_id, comment_text, parent_comment_id) VALUES (:document_id, :account_id, :comment_text, :parent_comment_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':comment_text', $comment_text, PDO::PARAM_STR);
        $stmt->bindParam(':parent_comment_id', $parent_comment_id, $parent_comment_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        return $stmt->execute();
    }

    public function deleteComment($comment_id, $account_id)
    {
        // Kiểm tra bình luận có thuộc về người dùng và trong vòng 1 giờ
        $query = "SELECT comment_date FROM comments WHERE comment_id = :comment_id AND account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comment) {
            return false; // Không tìm thấy bình luận hoặc không phải của người dùng
        }

        // Kiểm tra thời gian (1 giờ = 3600 giây)
        $commentTime = strtotime($comment['comment_date']);
        $currentTime = time();
        if (($currentTime - $commentTime) > 3600) {
            return false; // Quá 1 giờ
        }

        // Xóa bình luận
        $deleteQuery = "DELETE FROM comments WHERE comment_id = :comment_id";
        $deleteStmt = $this->db->prepare($deleteQuery);
        $deleteStmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        return $deleteStmt->execute();
    }
}
