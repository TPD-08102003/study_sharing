<?php

namespace App;

use PDO;

class DocumentController
{
    private $db;
    private $document;
    private $category;
    private $tag;
    private $comment;
    private $user;

    public function __construct($db)
    {
        $this->db = $db;
        $this->document = new Document($db);
        $this->category = new Category($db);
        $this->tag = new Tag($db);
        $this->comment = new Comment($db);
        $this->user = new User($db);
    }

    public function list()
    {
        $valid_file_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
        $query = isset($_GET['query']) ? trim($_GET['query']) : '';
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $file_type = (isset($_GET['file_type']) && in_array(trim($_GET['file_type']), $valid_file_types)) ? trim($_GET['file_type']) : '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        $sql = "SELECT d.*, c.category_name, u.full_name 
            FROM documents d 
            LEFT JOIN categories c ON d.category_id = c.category_id
            LEFT JOIN users u ON d.account_id = u.user_id";
        $params = [];
        if (isset($_SESSION['account_id'])) {
            $sql .= " WHERE (d.visibility = 'public' OR d.account_id = :account_id)";
            $params[':account_id'] = $_SESSION['account_id'];
        } else {
            $sql .= " WHERE d.visibility = 'public'";
        }

        if ($query) {
            $sql .= " AND (d.title LIKE :query OR d.description LIKE :query)";
            $params[':query'] = "%$query%";
        }
        if ($category_id > 0) {
            $sql .= " AND d.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        if ($file_type) {
            $sql .= " AND d.file_path LIKE :file_type";
            $params[':file_type'] = "%.$file_type";
        }

        $countSql = str_replace("SELECT d.*, c.category_name, u.full_name", "SELECT COUNT(*)", $sql);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        $sql .= " ORDER BY d.upload_date DESC LIMIT :offset, :perPage";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = $this->category->getAllCategories();

        foreach ($documents as &$doc) {
            $tags = $this->db->prepare("SELECT t.tag_name FROM document_tags dt JOIN tags t ON dt.tag_id = t.tag_id WHERE dt.document_id = :document_id");
            $tags->bindValue(':document_id', $doc['document_id'], PDO::PARAM_INT);
            $tags->execute();
            $doc['tags'] = $tags->fetchAll(PDO::FETCH_COLUMN);
        }
        unset($doc);

        $totalPages = ceil($total / $perPage);

        // Truyền các biến vào view
        $title = 'Danh sách tài liệu';
        $layout = 'layout.php';
        ob_start();
        // Đảm bảo truyền tất cả các biến cần thiết
        require __DIR__ . '/../views/document/list.php';
        $content = ob_get_clean();
        $pdo = $this->db;
        require __DIR__ . '/../views/layouts/' . $layout;
    }

    public function detail($document_id)
    {
        $document = $this->document->getDocumentById($document_id);
        if (!$document || ($document['visibility'] !== 'public' && (!isset($_SESSION['account_id']) || $_SESSION['account_id'] != $document['account_id']))) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại hoặc không có quyền truy cập']);
            exit;
        }
        $file_ext = strtolower(pathinfo($document['file_path'], PATHINFO_EXTENSION));
        $category = $document['category_id'] ? $this->category->getCategoryById($document['category_id']) : null;
        $uploader = $document['account_id'] ? $this->user->getUserById($document['account_id']) : null;

        $tags = $this->db->prepare("SELECT t.tag_name FROM document_tags dt JOIN tags t ON dt.tag_id = t.tag_id WHERE dt.document_id = :document_id");
        $tags->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $tags->execute();
        $document['tags'] = $tags->fetchAll(PDO::FETCH_COLUMN);

        $comments = $this->comment->getCommentsByDocumentId($document_id);
        foreach ($comments as &$comment) {
            $comment['user'] = $this->user->getUserById($comment['account_id']);
            error_log("Comment ID: " . $comment['comment_id'] . ", Account ID: " . $comment['account_id'] . ", Full Name: " . ($comment['user']['full_name'] ?? 'Unknown'));
        }
        unset($comment);
        // Lấy các version của tài liệu
        $documentVersion = new DocumentVersion($this->db);
        $versions = $documentVersion->getVersionsByDocumentId($document_id);

        $title = $document['title'];
        $layout = 'layout.php';
        ob_start();
        require __DIR__ . '/../views/document/detail.php';
        $content = ob_get_clean();
        $pdo = $this->db;
        require __DIR__ . '/../views/layouts/' . $layout;
    }

    public function comment()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['account_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận']);
            exit;
        }

        $document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
        $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';

        if ($document_id <= 0 || empty($comment_text)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $document = $this->document->getDocumentById($document_id);
        if (!$document) {
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại']);
            exit;
        }

        $success = $this->comment->createComment($document_id, $_SESSION['account_id'], $comment_text);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Bình luận đã được gửi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi bình luận']);
        }
    }
}
