(function() {
    'use strict';
    const documentFilterForm = document.querySelector('#documentFilterForm');
    if (documentFilterForm) {
        documentFilterForm.addEventListener('submit', function(event) {
            const form = this;
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Xử lý thay đổi select để tự động submit
        const selects = documentFilterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                documentFilterForm.submit();
            });
        });
    }
})();

// Xử lý form bình luận
(function() {
    'use strict';
    const commentForm = document.querySelector('#commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(event) {
            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            let spinner = submitButton.querySelector('.spinner-border');

            if (!spinner) {
                spinner = document.createElement('span');
                spinner.classList.add('spinner-border', 'spinner-border-sm', 'me-2', 'd-none');
                spinner.setAttribute('role', 'status');
                spinner.setAttribute('aria-hidden', 'true');
                submitButton.prepend(spinner);
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                event.preventDefault();
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                const formData = new FormData(form);
                fetch('/study_sharing/document/comment', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `alert alert-${data.success ? 'success' : 'danger'} mt-3`;
                    messageDiv.textContent = data.message;
                    form.before(messageDiv);
                    if (data.success) {
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                })
                .catch(error => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'alert alert-danger mt-3';
                    messageDiv.textContent = 'Lỗi server, vui lòng thử lại!';
                    form.before(messageDiv);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                });
            }
            form.classList.add('was-validated');
        }, false);
    }
})();

function loadVersion(pdfUrl) {
    const pdfContainer = document.getElementById('pdf-container');
    pdfContainer.innerHTML = ''; // Xóa nội dung cũ

    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        const numPages = pdf.numPages;

        for (let pageNum = 1; pageNum <= numPages; pageNum++) {
            pdf.getPage(pageNum).then(function(page) {
                const scale = 1.5;
                const viewport = page.getViewport({ scale: scale });
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

        pdfContainer.scrollTop = 0; // Cuộn về đầu
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        pdfContainer.innerHTML = '<p>Tài liệu không thể hiển thị. <a href="' + pdfUrl + '" download>Vui lòng tải xuống để xem.</a></p>';
    });
}

// Gọi lần đầu với version hiện tại
document.addEventListener('DOMContentLoaded', function() {
    // Lấy đường dẫn file từ một biến toàn cục hoặc data attribute
    const filePath = window.documentFilePath || '';
    if (filePath) {
        loadVersion('/study_sharing/uploads/' + filePath);
    }
});