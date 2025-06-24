<link rel="stylesheet" href="/study_sharing/assets/css/detail_document.css">
<div class="container">
    <h1 class="mb-4"><?php echo htmlspecialchars($document['title']); ?></h1>

    <!-- Thông tin tài liệu -->
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($document['description'] ?? 'Không có mô tả'); ?></p>
            <p><strong>Danh mục:</strong> <?php echo htmlspecialchars($category['category_name'] ?? 'Không có'); ?></p>
            <p><strong>Người tải lên:</strong> <?php echo htmlspecialchars($uploader['full_name'] ?? 'Ẩn danh'); ?></p>
            <p><strong>Ngày tải:</strong> <?php echo date('d/m/Y H:i', strtotime($document['upload_date'])); ?></p>
            <p><strong>Thẻ:</strong>
                <?php foreach ($document['tags'] as $tag): ?>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </p>
            <a href="#" id="downloadLink" class="btn btn-primary" onclick="recordDownload(<?php echo $document['document_id']; ?>, event)">Tải xuống</a>
        </div>
    </div>

    <!-- Nội dung file -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Nội dung tài liệu</h5>
            <?php if ($file_ext === 'pdf'): ?>
                <div class="mb-3">
                    <label for="versionSelect" class="form-label">Chọn version:</label>
                    <select id="versionSelect" class="form-select" onchange="loadVersion(this.value)">
                        <option value="/study_sharing/uploads/<?php echo htmlspecialchars($document['file_path']); ?>" <?php echo !isset($_GET['version']) ? 'selected' : ''; ?>>Version hiện tại</option>
                        <?php foreach ($versions as $version): ?>
                            <option value="/study_sharing/uploads/<?php echo htmlspecialchars($version['file_path']); ?>" <?php echo isset($_GET['version']) && $_GET['version'] == $version['version_number'] ? 'selected' : ''; ?>>
                                Version <?php echo htmlspecialchars($version['version_number']); ?> (<?php echo htmlspecialchars($version['change_note']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="pdf-container" class="pdf-container" style="height: 600px;"></div>
            <?php else: ?>
                <p>Không thể hiển thị trực tiếp. Vui lòng tải xuống để xem nội dung.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Đánh giá tài liệu -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Đánh giá tài liệu</h5>
            <?php
            $ratingStmt = $this->db->prepare("SELECT AVG(rating_value) as avg_rating FROM ratings WHERE document_id = :document_id");
            $ratingStmt->bindValue(':document_id', $document['document_id'], PDO::PARAM_INT);
            $ratingStmt->execute();
            $rating = $ratingStmt->fetch(PDO::FETCH_ASSOC);
            $avg_rating = $rating['avg_rating'] ? round($rating['avg_rating'], 1) : 0;

            $userRating = 0;
            if (isset($_SESSION['account_id'])) {
                $userRatingStmt = $this->db->prepare("SELECT rating_value FROM ratings WHERE document_id = :document_id AND account_id = :account_id");
                $userRatingStmt->bindValue(':document_id', $document['document_id'], PDO::PARAM_INT);
                $userRatingStmt->bindValue(':account_id', $_SESSION['account_id'], PDO::PARAM_INT);
                $userRatingStmt->execute();
                $userRatingResult = $userRatingStmt->fetch(PDO::FETCH_ASSOC);
                $userRating = $userRatingResult ? $userRatingResult['rating_value'] : 0;
            }
            ?>
            <p>Đánh giá trung bình: <?php echo $avg_rating ? $avg_rating . '/5' : 'Chưa có đánh giá'; ?></p>
            <div id="rating-stars" class="rating-stars" data-document-id="<?php echo $document['document_id']; ?>" data-user-rating="<?php echo $userRating; ?>">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>
            <?php if (!isset($_SESSION['account_id'])): ?>
                <p class="text-muted mt-2">Đăng nhập để đánh giá tài liệu.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bình luận -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Bình luận</h5>
            <div id="comments-container"
                data-is-logged-in="<?php echo isset($_SESSION['account_id']) ? 'true' : 'false'; ?>"
                data-current-user-id="<?php echo isset($_SESSION['account_id']) ? (int)$_SESSION['account_id'] : 0; ?>">
                <?php if (empty($comments)): ?>
                    <p class="text-muted">Chưa có bình luận nào.</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="border-bottom mb-3 pb-3 comment-item" data-comment-id="<?php echo $comment['comment_id']; ?>">
                            <div class="d-flex align-items-center mb-2 position-relative">
                                <img src="/study_sharing/assets/images/<?php echo htmlspecialchars($comment['user']['avatar'] ?? 'profile.png'); ?>" alt="Avatar" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <strong><?php echo htmlspecialchars($comment['user']['full_name'] ?? 'Ẩn danh'); ?></strong>
                                    <small class="text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($comment['comment_date'])); ?></small>
                                </div>
                                <?php if (isset($_SESSION['account_id'])): ?>
                                    <div class="dropdown ms-auto">
                                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item reply-comment" href="#" data-comment-id="<?php echo $comment['comment_id']; ?>">Trả lời</a></li>
                                            <?php
                                            $commentTime = strtotime($comment['comment_date']);
                                            $currentTime = time();
                                            if ($comment['account_id'] == $_SESSION['account_id'] && ($currentTime - $commentTime) <= 3600):
                                            ?>
                                                <li><a class="dropdown-item delete-comment" href="#" data-comment-id="<?php echo $comment['comment_id']; ?>">Xóa</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="mb-0"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                            <!-- Form trả lời (ẩn ban đầu) -->
                            <form class="reply-form mt-3 d-none" data-parent-comment-id="<?php echo $comment['comment_id']; ?>">
                                <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                                <input type="hidden" name="parent_comment_id" value="<?php echo $comment['comment_id']; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment_text" rows="3" required placeholder="Trả lời bình luận..."></textarea>
                                    <div class="invalid-feedback">Vui lòng nhập nội dung trả lời.</div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Gửi trả lời
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm cancel-reply">Hủy</button>
                            </form>
                            <!-- Hiển thị bình luận trả lời -->
                            <?php if (!empty($comment['replies'])): ?>
                                <div class="replies mt-3 ms-4">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="border-bottom mb-2 pb-2 reply-item" data-comment-id="<?php echo $reply['comment_id']; ?>">
                                            <div class="d-flex align-items-center mb-1 position-relative">
                                                <img src="/study_sharing/assets/images/<?php echo htmlspecialchars($reply['user']['avatar'] ?? 'profile.png'); ?>" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($reply['user']['full_name'] ?? 'Ẩn danh'); ?></strong>
                                                    <small class="text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($reply['comment_date'])); ?></small>
                                                </div>
                                                <?php if (isset($_SESSION['account_id'])): ?>
                                                    <div class="dropdown ms-auto">
                                                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item reply-comment" href="#" data-comment-id="<?php echo $reply['comment_id']; ?>">Trả lời</a></li>
                                                            <?php
                                                            $replyTime = strtotime($reply['comment_date']);
                                                            if ($reply['account_id'] == $_SESSION['account_id'] && ($currentTime - $replyTime) <= 3600):
                                                            ?>
                                                                <li><a class="dropdown-item delete-comment" href="#" data-comment-id="<?php echo $reply['comment_id']; ?>">Xóa</a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-0"><?php echo htmlspecialchars($reply['comment_text']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if ($totalComments > 5): ?>
                <button id="loadMoreComments" class="btn btn-outline-primary mt-3" data-document-id="<?php echo $document['document_id']; ?>" data-offset="5">Tải thêm bình luận</button>
            <?php endif; ?>

            <!-- Form bình luận chính -->
            <?php if (isset($_SESSION['account_id'])): ?>
                <form id="commentForm" method="POST" class="needs-validation mt-4" novalidate>
                    <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                    <div class="mb-3">
                        <label for="comment_text" class="form-label">Bình luận của bạn</label>
                        <textarea class="form-control" id="comment_text" name="comment_text" rows="4" required></textarea>
                        <div class="invalid-feedback">Vui lòng nhập nội dung bình luận.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Gửi bình luận
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập</a> để bình luận.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="/study_sharing/assets/js/document.js"></script>