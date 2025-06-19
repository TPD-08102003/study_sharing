<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Quản lý và Chia sẻ Tài liệu'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="/study_sharing/assets/css/custom.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="/study_sharing">
                <img src="/study_sharing/assets/images/logo.png" alt="Logo" class="rounded-circle" style="height: 40px; width: 40px; object-fit: cover;">
            </a>

            <!-- Thanh tìm kiếm -->
            <form class="d-flex ms-3" role="search" action="/search" method="GET">
                <input class="form-control me-2" type="search" name="query" placeholder="Tìm kiếm..." aria-label="Search">
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <!-- Nút chức năng -->
            <div class="navbar-nav ms-auto">
                <?php
                $user = isset($_SESSION['user_id']) ? (new \App\User($pdo))->getUserById($_SESSION['user_id']) : null;
                $role = $user ? $user['role'] : null;
                $avatar = $user && $user['avatar'] ? '/study_sharing/assets/images/' . htmlspecialchars($user['avatar']) : '/study_sharing/assets/images/profile.png';
                ?>

                <!-- Chức năng chung -->
                <a class="nav-link text-white" href="/documents/list">
                    <i class="bi bi-file-earmark-text-fill"></i> Tài liệu
                </a>
                <a class="nav-link text-white" href="/courses/list">
                    <i class="bi bi-book-fill"></i> Khóa học
                </a>

                <?php if ($user): ?>
                    <!-- Chức năng khi đã đăng nhập -->
                    <a class="nav-link text-white" href="/user/profile">
                        <i class="bi bi-person-fill"></i> Hồ sơ
                    </a>
                    <a class="nav-link text-white" href="/notification/list">
                        <i class="bi bi-bell-fill"></i> Thông báo
                    </a>
                    <a class="nav-link text-white" href="/study_sharing/user/logout">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </a>
                    <a class="nav-link" href="/user/profile">
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="rounded-circle" style="height: 30px; width: 30px; object-fit: cover;">
                    </a>

                    <!-- Chức năng theo vai trò -->
                    <?php if ($role === 'admin'): ?>
                        <a class="nav-link text-white" href="/user/manage_users">
                            <i class="bi bi-people-fill"></i> Quản lý Người dùng
                        </a>
                        <a class="nav-link text-white" href="/category/manage">
                            <i class="bi bi-folder-fill"></i> Quản lý Danh mục
                        </a>
                        <a class="nav-link text-white" href="/tag/manage">
                            <i class="bi bi-tag-fill"></i> Quản lý Thẻ
                        </a>
                    <?php elseif ($role === 'teacher'): ?>
                        <a class="nav-link text-white" href="/course/create">
                            <i class="bi bi-plus-circle-fill"></i> Tạo Khóa học
                        </a>
                        <a class="nav-link text-white" href="/course/manage">
                            <i class="bi bi-gear-fill"></i> Quản lý Khóa học
                        </a>
                    <?php endif; ?>

                    <?php
                    // Kiểm tra quyền từ user_permissions (giả sử có phương thức kiểm tra quyền)
                    $userModel = new \App\User($pdo);
                    $permissions = $userModel->getUserPermissions($user['user_id']); // Cần thêm phương thức này
                    if (in_array('upload_document', $permissions)) {
                        echo '<a class="nav-link text-white" href="/document/upload"><i class="bi bi-upload"></i> Tải lên Tài liệu</a>';
                    }
                    if (in_array('delete_document', $permissions)) {
                        echo '<a class="nav-link text-white" href="/document/delete"><i class="bi bi-trash-fill"></i> Xóa Tài liệu</a>';
                    }
                    ?>

                <?php else: ?>
                    <!-- Chức năng khi chưa đăng nhập -->
                    <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </a>
                    <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">
                        <i class="bi bi-person-plus-fill"></i> Đăng ký
                    </a>
                    <a class="nav-link">
                        <img src="/study_sharing/assets/images/profile.png" alt="Avatar" class="rounded-circle" style="height: 30px; width: 30px; object-fit: cover;">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content flex-grow-1 p-4">
        <div class="container-fluid">
            <?php echo $content; ?>
        </div>
    </div>

    <footer class="bg-dark text-white text-center p-4 mt-auto" style="width: 100%;">
        <p>© 2025 - Hệ thống Quản lý và Chia sẻ Tài liệu. Đã đăng ký bản quyền.</p>
    </footer>

    <!-- Modal Đăng nhập -->
    <div id="loginModal" class="modal fade" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Đăng nhập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginMessage"></div>
                    <form id="loginForm" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Tên đăng nhập hoặc Email</label>
                            <input type="text" class="form-control" id="loginUsername" name="username" required>
                            <div class="invalid-feedback">Vui lòng nhập tên đăng nhập hoặc email.</div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đăng ký -->
    <div id="registerModal" class="modal fade" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Đăng ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="registerMessage"></div>
                    <form id="registerForm" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" id="registerUsername" name="username" required>
                            <div class="invalid-feedback">Vui lòng nhập tên đăng nhập.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required>
                            <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="registerPassword" name="password" required>
                            <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerFullName" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="registerFullName" name="full_name" required>
                            <div class="invalid-feedback">Vui lòng nhập họ và tên.</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerAvatar" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="registerAvatar" name="avatar" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script>
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
            fetch('/study_sharing/user/login', { // Đảm bảo khớp với /controller/action
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
                        setTimeout(() => location.reload(), 1000);
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
            fetch(getBaseURL() + 'user/register', { // Sử dụng định dạng /controller/action
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
    </script>
</body>

</html>