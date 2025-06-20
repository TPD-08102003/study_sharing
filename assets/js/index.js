// Hàm lấy base URL
function getBaseURL() {
    return window.location.pathname.split('/').slice(0, -1).join('/') + '/';
}

// Xử lý form đăng nhập
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    fetch('/study_sharing/user/login', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('loginMessage').innerHTML = `<div class="alert ${data.success ? 'alert-success' : 'alert-danger'}">${data.message}</div>`;
            if (data.success) {
                // Kiểm tra role từ phản hồi JSON
                if (data.role === 'admin') {
                    setTimeout(() => window.location.href = '/study_sharing/HomeAdmin/index', 1000);
                } else {
                    setTimeout(() => location.reload(), 1000);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loginMessage').innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
        });
    form.classList.remove('was-validated');
});

// Xử lý form đăng ký
(function() {
    'use strict';
    const registerForm = document.querySelector('#registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const form = this;
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                event.preventDefault();
                const formData = new FormData(form);
                // Gửi AJAX request
                fetch('/study_sharing/user/register', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        const messageDiv = document.querySelector('#registerMessage');
                        messageDiv.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.message}</div>`;
                        if (data.success) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        const messageDiv = document.querySelector('#registerMessage');
                        messageDiv.innerHTML = '<div class="alert alert-danger">Lỗi server, vui lòng thử lại!</div>';
                    });
            }
            form.classList.add('was-validated');
        }, false);

        // Xóa invalid-feedback khi người dùng nhập dữ liệu
        const optionalFields = ['registerDateOfBirth', 'registerPhoneNumber', 'registerAddress'];
        optionalFields.forEach(id => {
            const field = document.querySelector(`#${id}`);
            field.addEventListener('input', function() {
                if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            });
        });
    }
})();