<style>
    .pdf-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        /* Đảm bảo bắt đầu từ đầu */
        overflow: auto;
        /* Cuộn khi vượt quá */
        max-width: 100%;
        height: 600px;
        /* Giữ chiều cao cố định */
        position: relative;
        /* Đảm bảo cuộn đúng */
    }

    .pdf-container canvas {
        margin: 0 auto;
        display: block;
        max-width: 100%;
        /* Giới hạn chiều rộng */
    }
</style>
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
            <a href="/study_sharing/uploads/<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-primary" download>Tải xuống</a>
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
                        <option value="/study_sharing/uploads/<?php echo htmlspecialchars($document['file_path']); ?>">Version hiện tại</option>
                        <?php foreach ($versions as $version): ?>
                            <option value="/study_sharing/uploads/<?php echo htmlspecialchars($version['file_path']); ?>">
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    document.addEventListener('DOMContentLoaded', function() {
        const pdfContainer = document.getElementById('pdf-container');
        const pdfUrl = '/study_sharing/uploads/<?php echo htmlspecialchars($document['file_path']); ?>';

        // Tải và hiển thị PDF
        pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            const numPages = pdf.numPages;

            for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                pdf.getPage(pageNum).then(function(page) {
                    const scale = 1.5;
                    const viewport = page.getViewport({
                        scale: scale
                    });
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    canvas.style.maxWidth = '100%';
                    pdfContainer.appendChild(canvas);

                    page.render({
                        canvasContext: context,
                        viewport: viewport
                    });
                });
            }

            // Cuộn về đầu sau khi tất cả trang được tải
            pdfContainer.scrollTop = 0;
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            pdfContainer.innerHTML = '<p>Tài liệu không thể hiển thị. <a href="' + pdfUrl + '" download>Vui lòng tải xuống để xem.</a></p>';
        });
    });
</script>

<script src="/study_sharing/assets/js/document.js"></script>