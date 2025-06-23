
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
    const pdfUrl = document.getElementById('versionSelect').value || '/study_sharing/uploads/' + document.querySelector('option[selected]').value.split('/').pop();
    if (pdfUrl) {
        loadVersion(pdfUrl);
    }

    // Khởi tạo đánh giá sao
    const ratingStars = document.getElementById('rating-stars');
    if (ratingStars) {
        const documentId = ratingStars.dataset.documentId;
        const userRating = parseInt(ratingStars.dataset.userRating);
        initializeRatingStars(ratingStars, documentId, userRating);
    }
});

// Khởi tạo và xử lý sao đánh giá
function initializeRatingStars(ratingStars, documentId, userRating) {
    const stars = ratingStars.querySelectorAll('.star');

    // Hiển thị sao đã đánh giá (nếu có)
    if (userRating > 0) {
        for (let i = 0; i < userRating; i++) {
            stars[i].classList.add('filled');
        }
    }

    // Xử lý hover và click
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const value = parseInt(this.dataset.value);
            highlightStars(value);
        });

        star.addEventListener('mouseout', function() {
            resetStars(userRating);
        });

        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            submitRating(documentId, value);
        });
    });
}

// Highlight sao đến vị trí hover
function highlightStars(value) {
    const stars = document.getElementById('rating-stars').querySelectorAll('.star');
    stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        if (starValue <= value) {
            star.classList.add('filled');
        } else {
            star.classList.remove('filled');
        }
    });
}

// Reset sao về trạng thái ban đầu
function resetStars(userRating) {
    const stars = document.getElementById('rating-stars').querySelectorAll('.star');
    stars.forEach(star => {
        star.classList.remove('filled');
    });
    if (userRating > 0) {
        for (let i = 0; i < userRating; i++) {
            stars[i].classList.add('filled');
        }
    }
}

// Xử lý submit rating cho tài liệu
function submitRating(documentId, ratingValue) {
    // Kiểm tra đăng nhập
    const isLoggedIn = document.querySelector('.nav-item.dropdown.ms-lg-2 .nav-link.dropdown-toggle')?.textContent.trim() !== 'Tài khoản';
    if (!isLoggedIn) {
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
        return;
    }

    fetch('/study_sharing/document/rateDocument', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `document_id=${documentId}&rating_value=${ratingValue}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đánh giá đã được gửi!');
            location.reload(); // Tải lại trang để cập nhật rating trung bình và user rating
        } else {
            alert('Lỗi khi gửi đánh giá: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi server, vui lòng thử lại!');
    });
}

function recordDownload(documentId, event) {
    event.preventDefault();

    fetch('/study_sharing/document/recordDownload', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `document_id=${documentId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/study_sharing/uploads/' + document.querySelector('a[download]').getAttribute('href').split('/').pop();
        } else {
            alert('Lỗi khi ghi nhận tải xuống: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi server, vui lòng thử lại!');
    });
}

pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    document.addEventListener('DOMContentLoaded', function() {
        const pdfContainer = document.getElementById('pdf-container');
        const pdfUrl = document.getElementById('versionSelect').value;

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

            pdfContainer.scrollTop = 0;
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            pdfContainer.innerHTML = '<p>Tài liệu không thể hiển thị. <a href="' + pdfUrl + '" download>Vui lòng tải xuống để xem.</a></p>';
        });
    });