<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Quản lý và Chia sẻ Tài liệu'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<body class="bg-gray-100 font-sans">
    <header class="bg-blue-600 text-white p-4">
        <nav class="container mx-auto flex justify-between items-center">
            <div>
                <a href="/" class="text-xl font-bold">Chia sẻ Tài liệu</a>
            </div>
            <ul class="flex space-x-6">
                <li><a href="/" class="hover:underline">Trang chủ</a></li>
                <li><a href="/documents" class="hover:underline">Tài liệu</a></li>
                <li><a href="/courses" class="hover:underline">Khóa học</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/profile" class="hover:underline">Hồ sơ</a></li>
                    <li><a href="/logout" class="hover:underline">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="/login" class="hover:underline">Đăng nhập</a></li>
                    <li><a href="/register" class="hover:underline">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container mx-auto mt-6 p-4">
        <?php echo $content; ?>
    </main>

    <footer class="bg-gray-800 text-white p-4 mt-6">
        <div class="container mx-auto text-center">
            <p>© 2025 - Hệ thống Quản lý và Chia sẻ Tài liệu. Đã đăng ký bản quyền.</p>
        </div>
    </footer>
</body>

</html>