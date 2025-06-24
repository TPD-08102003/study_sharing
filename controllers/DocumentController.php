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
        // Danh sách loại file hợp lệ
        $valid_file_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];

        // Lấy các tham số từ query string
        $query = isset($_GET['query']) ? trim($_GET['query']) : '';
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $file_type = (isset($_GET['file_type']) && in_array(trim($_GET['file_type']), $valid_file_types)) ? trim($_GET['file_type']) : '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        // Xây dựng câu lệnh SQL cho danh sách tài liệu
        $sql = "SELECT d.*, c.category_name, u.full_name 
                FROM documents d 
                LEFT JOIN categories c ON d.category_id = c.category_id
                LEFT JOIN users u ON d.account_id = u.user_id";
        $bindParams = [];
        $hasWhere = false;

        // Điều kiện visibility dựa trên trạng thái đăng nhập
        if (isset($_SESSION['account_id'])) {
            $sql .= " WHERE (d.visibility = 'public' OR d.account_id = :account_id)";
            $bindParams[':account_id'] = $_SESSION['account_id'];
            $hasWhere = true;
        } else {
            $sql .= " WHERE d.visibility = 'public'";
            $hasWhere = true;
        }

        // Điều kiện tìm kiếm theo từ khóa
        if ($query !== '') {
            $sql .= $hasWhere ? " AND " : " WHERE ";
            $sql .= "(d.title LIKE :query1 OR d.description LIKE :query2)";
            $bindParams[':query1'] = "%$query%";
            $bindParams[':query2'] = "%$query%";
            $hasWhere = true;
        }

        // Điều kiện lọc theo danh mục
        if ($category_id > 0) {
            $sql .= $hasWhere ? " AND " : " WHERE ";
            $sql .= "d.category_id = :category_id";
            $bindParams[':category_id'] = $category_id;
            $hasWhere = true;
        }

        // Điều kiện lọc theo loại file
        if ($file_type !== '') {
            $sql .= $hasWhere ? " AND " : " WHERE ";
            $sql .= "d.file_path LIKE :file_type";
            $bindParams[':file_type'] = "%.$file_type";
            $hasWhere = true;
        }

        // Xây dựng câu lệnh SQL để đếm tổng số bản ghi
        $countSql = "SELECT COUNT(*) FROM documents d";
        $countBindParams = [];
        $hasCountWhere = false;

        // Điều kiện visibility cho câu lệnh đếm
        if (isset($_SESSION['account_id'])) {
            $countSql .= " WHERE (d.visibility = 'public' OR d.account_id = :account_id)";
            $countBindParams[':account_id'] = $_SESSION['account_id'];
            $hasCountWhere = true;
        } else {
            $countSql .= " WHERE d.visibility = 'public'";
            $hasCountWhere = true;
        }

        // Điều kiện tìm kiếm theo từ khóa cho câu lệnh đếm
        if ($query !== '') {
            $countSql .= $hasCountWhere ? " AND " : " WHERE ";
            $countSql .= "(d.title LIKE :query1 OR d.description LIKE :query2)";
            $countBindParams[':query1'] = "%$query%";
            $countBindParams[':query2'] = "%$query%";
            $hasCountWhere = true;
        }

        // Điều kiện lọc theo danh mục cho câu lệnh đếm
        if ($category_id > 0) {
            $countSql .= $hasCountWhere ? " AND " : " WHERE ";
            $countSql .= "d.category_id = :category_id";
            $countBindParams[':category_id'] = $category_id;
            $hasCountWhere = true;
        }

        // Điều kiện lọc theo loại file cho câu lệnh đếm
        if ($file_type !== '') {
            $countSql .= $hasCountWhere ? " AND " : " WHERE ";
            $countSql .= "d.file_path LIKE :file_type";
            $countBindParams[':file_type'] = "%.$file_type";
            $hasCountWhere = true;
        }

        // In log để debug
        echo "<script>console.log('Count SQL: ' + " . json_encode($countSql) . ");</script>";
        echo "<script>console.log('Count Bind Params: ' + " . json_encode($countBindParams) . ");</script>";
        echo "<script>console.log('SQL: ' + " . json_encode($sql) . ");</script>";
        echo "<script>console.log('Bind Params: ' + " . json_encode($bindParams) . ");</script>";

        // Chuẩn bị và thực thi câu lệnh đếm
        $countStmt = $this->db->prepare($countSql);
        foreach ($countBindParams as $key => $value) {
            $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Thêm phân trang vào câu lệnh SQL chính
        $sql .= " ORDER BY d.upload_date DESC LIMIT :offset, :perPage";
        $stmt = $this->db->prepare($sql);
        foreach ($bindParams as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách danh mục
        $categories = $this->category->getAllCategories();

        // Xử lý tags và rating cho từng tài liệu
        foreach ($documents as &$doc) {
            // Lấy tags
            $tagsStmt = $this->db->prepare("SELECT t.tag_name FROM document_tags dt JOIN tags t ON dt.tag_id = t.tag_id WHERE dt.document_id = :document_id");
            $tagsStmt->bindValue(':document_id', $doc['document_id'], PDO::PARAM_INT);
            $tagsStmt->execute();
            $doc['tags'] = $tagsStmt->fetchAll(PDO::FETCH_COLUMN);

            // Tính trung bình rating
            $ratingStmt = $this->db->prepare("SELECT AVG(rating_value) as avg_rating FROM ratings WHERE document_id = :document_id");
            $ratingStmt->bindValue(':document_id', $doc['document_id'], PDO::PARAM_INT);
            $ratingStmt->execute();
            $rating = $ratingStmt->fetch(PDO::FETCH_ASSOC);
            $doc['avg_rating'] = $rating['avg_rating'] ? round($rating['avg_rating'], 1) : 0;
        }
        unset($doc);

        // Tính tổng số trang
        $totalPages = ceil($total / $perPage);

        // Chuẩn bị dữ liệu cho view
        $title = 'Danh sách tài liệu';
        $layout = 'layout.php';
        ob_start();
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

        // Lấy bình luận với giới hạn 5
        $commentData = $this->comment->getCommentsByDocumentId($document_id, 5, 0);
        $comments = $commentData['comments'];
        $totalComments = $commentData['total'];
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

    public function replyComment()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['account_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để trả lời']);
            exit;
        }

        $document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
        $parent_comment_id = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : 0;
        $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';

        if ($document_id <= 0 || $parent_comment_id <= 0 || empty($comment_text)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $document = $this->document->getDocumentById($document_id);
        if (!$document) {
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại']);
            exit;
        }

        $parentComment = $this->comment->getCommentById($parent_comment_id);
        if (!$parentComment || $parentComment['document_id'] != $document_id) {
            echo json_encode(['success' => false, 'message' => 'Bình luận cha không tồn tại']);
            exit;
        }

        $success = $this->comment->createComment($document_id, $_SESSION['account_id'], $comment_text, $parent_comment_id);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Trả lời đã được gửi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi trả lời']);
        }
    }

    public function deleteComment()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['account_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa bình luận']);
            exit;
        }

        $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
        if ($comment_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $success = $this->comment->deleteComment($comment_id, $_SESSION['account_id']);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Bình luận đã được xóa']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa bình luận. Có thể bình luận không phải của bạn hoặc đã quá 1 giờ.']);
        }
    }

    public function rateDocument()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!isset($_SESSION['account_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
            exit;
        }

        $document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
        $rating_value = isset($_POST['rating_value']) ? (int)$_POST['rating_value'] : 0;
        $account_id = $_SESSION['account_id'];

        if ($document_id <= 0 || $rating_value < 1 || $rating_value > 5) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        // Kiểm tra tài liệu tồn tại
        $document = $this->document->getDocumentById($document_id);
        if (!$document) {
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại']);
            exit;
        }

        $rating = new Rating($this->db);

        // Kiểm tra xem người dùng đã đánh giá chưa
        $existingRatingStmt = $this->db->prepare("SELECT rating_id FROM ratings WHERE document_id = :document_id AND account_id = :account_id");
        $existingRatingStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $existingRatingStmt->bindValue(':account_id', $account_id, PDO::PARAM_INT);
        $existingRatingStmt->execute();
        $existingRating = $existingRatingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRating) {
            // Cập nhật rating hiện có
            $updateStmt = $this->db->prepare("UPDATE ratings SET rating_value = :rating_value, created_at = NOW() WHERE rating_id = :rating_id");
            $updateStmt->bindValue(':rating_value', $rating_value, PDO::PARAM_INT);
            $updateStmt->bindValue(':rating_id', $existingRating['rating_id'], PDO::PARAM_INT);
            $success = $updateStmt->execute();
        } else {
            // Thêm rating mới
            $success = $rating->createRating($document_id, $account_id, $rating_value);
        }

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Đánh giá đã được gửi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi đánh giá']);
        }
    }

    public function recordDownload()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;

        if ($document_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $document = $this->document->getDocumentById($document_id);
        if (!$document || $document['visibility'] !== 'public') {
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại hoặc không có quyền truy cập']);
            exit;
        }

        // Ghi nhận tải xuống nếu người dùng đã đăng nhập
        if (isset($_SESSION['account_id'])) {
            $account_id = $_SESSION['account_id'];
            $download = new Download($this->db);

            // Kiểm tra xem bản ghi tải xuống đã tồn tại chưa
            $existingDownloadStmt = $this->db->prepare("SELECT download_id FROM downloads WHERE document_id = :document_id AND account_id = :account_id");
            $existingDownloadStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $existingDownloadStmt->bindValue(':account_id', $account_id, PDO::PARAM_INT);
            $existingDownloadStmt->execute();
            $existingDownload = $existingDownloadStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingDownload) {
                // Cập nhật download_date nếu bản ghi đã tồn tại
                $updateStmt = $this->db->prepare("UPDATE downloads SET download_date = NOW() WHERE download_id = :download_id");
                $updateStmt->bindValue(':download_id', $existingDownload['download_id'], PDO::PARAM_INT);
                $success = $updateStmt->execute();
            } else {
                // Tạo bản ghi mới nếu chưa tồn tại
                $success = $download->recordDownload($document_id, $account_id);
            }

            if (!$success) {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi ghi nhận tải xuống']);
                exit;
            }
        }

        // Luôn trả về thành công để cho phép tải xuống
        echo json_encode(['success' => true, 'message' => 'Tải xuống được phép']);
    }

    public function loadMoreComments()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $document_id = isset($_POST['document_id']) ? (int)$_POST['document_id'] : 0;
        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
        $limit = 5;

        if ($document_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $document = $this->document->getDocumentById($document_id);
        if (!$document || ($document['visibility'] !== 'public' && (!isset($_SESSION['account_id']) || $_SESSION['account_id'] != $document['account_id']))) {
            echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại hoặc không có quyền truy cập']);
            exit;
        }

        $commentData = $this->comment->getCommentsByDocumentId($document_id, $limit, $offset);
        $comments = $commentData['comments'];
        $totalComments = $commentData['total'];

        // Lấy thông tin người dùng cho từng bình luận
        foreach ($comments as &$comment) {
            $comment['user'] = $this->user->getUserById($comment['account_id']);
            $comment['comment_date'] = date('d/m/Y H:i', strtotime($comment['comment_date']));
        }
        unset($comment);

        echo json_encode([
            'success' => true,
            'comments' => $comments,
            'hasMore' => ($offset + count($comments)) < $totalComments
        ]);
    }
}
