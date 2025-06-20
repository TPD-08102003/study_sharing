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
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    const formData = new FormData(form);
    fetch(getBaseURL() + 'user/register', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('registerMessage').innerHTML = `<div class="alert ${data.success ? 'alert-success' : 'alert-danger'}">${data.message}</div>`;
            if (data.success) {
                setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide(), 1000);
            }
        });
    form.classList.remove('was-validated');
});