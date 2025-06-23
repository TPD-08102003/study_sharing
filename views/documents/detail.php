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
            <a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-primary" download>Tải xuống</a>
        </div>
    </div>

    <!-- Nội dung file -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Nội dung tài liệu</h5>
            <?php
            $file_ext = strtolower(pathinfo($document['file_path'], PATHINFO_EXTENSION));
            if ($file_ext === 'pdf'):
            ?>
                <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" style="width: 100%; height: 600px;" frameborder="0"></iframe>
            <?php else: ?>
                <p>Không thể hiển thị trực tiếp. Vui lòng tải xuống để xem nội dung.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bình luận -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Bình luận</h5>
            <?php if (empty($comments)): ?>
                <p class="text-muted">Chưa có bình luận nào.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="border-bottom mb-3 pb-3">
                        <div class="d-flex align-items-center mb-2">
                            <img src="/study_sharing/assets/images/<?php echo htmlspecialchars($comment['user']['avatar'] ?? 'profile.png'); ?>" alt="Avatar" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <strong><?php echo htmlspecialchars($comment['user']['full_name'] ?? 'Ẩn danh'); ?></strong>
                                <small class="text-muted ms-2"><?php echo date('d/m/Y H:i', strtotime($comment['comment_date'])); ?></small>
                            </div>
                        </div>
                        <p class="mb-0"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Form bình luận -->
            <?php if (isset($_SESSION['account_id'])): ?>
                <form id="commentForm" method="POST" class="needs-validation" novalidate>
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
                <div class="alert alert-info">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập</a> để bình luận.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>