<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

use App\Document;
use App\Category;

session_start();
$documentModel = new \App\Document($pdo);
$categoryModel = new \App\Category($pdo);
$latestDocuments = $documentModel->getAllDocuments(); // Lấy tất cả tài liệu
$categories = $categoryModel->getAllCategories();

ob_start();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold mb-4 text-blue-600">Chào mừng đến với Hệ thống Chia sẻ Tài liệu</h1>
    <p class="mb-6 text-gray-700">Khám phá và chia sẻ tài liệu học tập của bạn ngay hôm nay!</p>

    <section class="mb-6">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Danh mục tài liệu</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($categories as $category): ?>
                <a href="/category/<?php echo $category['category_id']; ?>" class="bg-blue-100 p-4 rounded-lg hover:bg-blue-200 transition">
                    <h3 class="font-medium"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Tài liệu mới nhất</h2>
        <?php if ($latestDocuments): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($latestDocuments as $doc): ?>
                    <div class="bg-gray-50 p-4 rounded-lg shadow">
                        <h3 class="font-medium text-lg text-blue-600">
                            <a href="/document/<?php echo $doc['document_id']; ?>" class="hover:underline">
                                <?php echo htmlspecialchars($doc['title']); ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($doc['description'] ?? ''); ?></p>
                        <p class="text-xs text-gray-500">Đăng bởi: <?php echo htmlspecialchars($doc['user_id']); ?> - <?php echo $doc['upload_date']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-600">Không có tài liệu nào để hiển thị.</p>
        <?php endif; ?>
    </section>
</div>

<?php
$content = ob_get_clean();
$title = "Trang chủ";
require 'views/layouts/layout.php';
?>