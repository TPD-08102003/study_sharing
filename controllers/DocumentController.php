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
        // Lấy tham số tìm kiếm, lọc, và phân trang
        $query = isset($_GET['query']) ? trim($_GET['query']) : '';
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $file_type = isset($_GET['file_type']) ? trim($_GET['file_type']) : '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        // Xây dựng câu truy vấn
        $sql = "SELECT d.*, c.category_name, u.full_name 
                FROM documents d 
                LEFT JOIN categories c ON d.category_id = c.category_id
                LEFT JOIN users u ON d.account_id = u.account_id
                WHERE d.visibility = 'public'";
        $params = [];

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

        // Đếm tổng số tài liệu
        $countSql = str_replace("SELECT d.*, c.category_name, u.full_name", "SELECT COUNT(*)", $sql);
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // Lấy danh sách tài liệu
        $sql .= " ORDER BY d.upload_date DESC LIMIT :offset, :perPage";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách danh mục
        $categories = $this->category->getAllCategories();

        // Lấy danh sách thẻ cho mỗi tài liệu
        foreach ($documents as &$doc) {
            $tags = $this->db->prepare("SELECT t.tag_name FROM document_tags dt JOIN tags t ON dt.tag_id = t.tag_id WHERE dt.document_id = :document_id");
            $tags->bindValue(':document_id', $doc['document_id'], PDO::PARAM_INT);
            $tags->execute();
            $doc['tags'] = $tags->fetchAll(PDO::FETCH_COLUMN);
        }

        // Trả về view
        $title = 'Danh sách tài liệu';
        $layout = 'layout.php';
        ob_start();
        require __DIR__ . '/../views/document/list.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/' . $layout;
    }

    public function detail($document_id)
    {
        // Lấy thông tin tài liệu
        $document = $this->document->getDocumentById($document_id);
        if (!$document || ($document['visibility'] !== 'public' && (!isset($_SESSION['account_id']) || $_SESSION['account_id'] != $document['account_id']))) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại hoặc không có quyền truy cập']);
            exit;
        }

        // Lấy danh mục
        $category = $document['category_id'] ? $this->category->getCategoryById($document['category_id']) : null;

        // Lấy người tải lên
        $uploader = $document['account_id'] ? $this->user->getUserById($document['account_id']) : null;

        // Lấy thẻ
        $tags = $this->db->prepare("SELECT t.tag_name FROM document_tags dt JOIN tags t ON dt.tag_id = t.tag_id WHERE dt.document_id = :document_id");
        $tags->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $tags->execute();
        $document['tags'] = $tags->fetchAll(PDO::FETCH_COLUMN);

        // Lấy bình luận
        $comments = $this->comment->getCommentsByDocumentId($document_id);
        foreach ($comments as &$comment) {
            $comment['user'] = $this->user->getUserById($comment['account_id']);
        }

        // Trả về view
        $title = $document['title'];
        $layout = 'layout.php';
        ob_start();
        require __DIR__ . '/../views/document/detail.php';
        $content = ob_get_clean();
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

        // Kiểm tra tài liệu tồn tại
        $document = $this->document->getDocumentById($document_id);
        if (!$document) {
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại']);
            exit;
        }

        // Thêm bình luận
        $success = $this->comment->createComment($document_id, $_SESSION['account_id'], $comment_text);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Bình luận đã được gửi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi bình luận']);
        }
    }
}
