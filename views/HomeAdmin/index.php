<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';

use App\User;
use App\Document;
use App\Course;
use App\Category;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /study_sharing');
    exit;
}

// Lấy dữ liệu thống kê
$userModel = new User($pdo);
$documentModel = new Document($pdo);
$courseModel = new Course($pdo);
$categoryModel = new Category($pdo);

$totalUsers = $userModel->countUsers();
$totalDocuments = $documentModel->countDocuments();
$totalCourses = $courseModel->countCourses();
$totalCategories = $categoryModel->countCategories();
?>

<style>
    /* Card styles */
    .dashboard-card {
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        /* Gradient nền nhẹ */
    }

    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        /* Bóng đổ động */
    }

    /* Card màu riêng biệt */
    .card-users {
        border-left: 4px solid #0d6ffd;
        /* Màu xanh cho người dùng */
    }

    .card-documents {
        border-left: 4px solid #198754;
        /* Màu xanh lá cho tài liệu */
    }

    .card-courses {
        border-left: 4px solid #dc3545;
        /* Màu đỏ cho khóa học */
    }

    .card-categories {
        border-left: 4px solid #ffc107;
        /* Màu vàng cho danh mục */
    }

    .card-body {
        padding: 1.5rem;
        text-align: center;
    }

    .card-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        transition: transform 0.3s ease, color 0.3s ease;
    }

    .dashboard-card:hover .card-icon {
        transform: scale(1.2);
        /* Phóng to icon khi hover */
        color: #0d6efd;
        /* Chuyển màu icon */
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 0.5rem;
    }

    .card-text {
        font-size: 2rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 1rem;
    }

    .quick-link {
        display: inline-block;
        font-weight: 500;
        color: #0d6efd;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .quick-link:hover {
        background-color: #e9ecef;
        color: #0d6efd;
    }

    /* Nút điều hướng nhanh */
    .quick-action-btn {
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .quick-action-btn:hover {
        background-color: #0d6efd;
        color: #fff;
        transform: translateY(-3px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-card {
            margin-bottom: 1rem;
        }

        .card-text {
            font-size: 1.5rem;
        }

        .quick-action-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="container py-5">
    <h1 class="mb-4 text-primary"><i class="bi bi-speedometer2 me-2"></i> Dashboard Quản trị</h1>

    <!-- Thống kê -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-users shadow-sm">
                <div class="card-body">
                    <i class="bi bi-people card-icon"></i>
                    <h5 class="card-title">Người dùng</h5>
                    <p class="card-text"><?php echo $totalUsers; ?></p>
                    <a href="/user/manage_users" class="quick-link">Quản lý người dùng</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-documents shadow-sm">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text card-icon"></i>
                    <h5 class="card-title">Tài liệu</h5>
                    <p class="card-text"><?php echo $totalDocuments; ?></p>
                    <a href="/document/delete" class="quick-link">Quản lý tài liệu</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-courses shadow-sm">
                <div class="card-body">
                    <i class="bi bi-book card-icon"></i>
                    <h5 class="card-title">Khóa học</h5>
                    <p class="card-text"><?php echo $totalCourses; ?></p>
                    <a href="/course/manage" class="quick-link">Quản lý khóa học</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card dashboard-card card-categories shadow-sm">
                <div class="card-body">
                    <i class="bi bi-folder card-icon"></i>
                    <h5 class="card-title">Danh mục</h5>
                    <p class="card-text"><?php echo $totalCategories; ?></p>
                    <a href="/category/manage" class="quick-link">Quản lý danh mục</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Nút điều hướng nhanh -->
    <div class="row g-4">
        <div class="col-12">
            <h3 class="mb-3 text-primary">Hành động nhanh</h3>
            <div class="d-flex flex-wrap gap-3">
                <a href="/user/manage_users" class="btn btn-outline-primary quick-action-btn"><i class="bi bi-people me-2"></i> Quản lý người dùng</a>
                <a href="/category/manage" class="btn btn-outline-primary quick-action-btn"><i class="bi bi-folder me-2"></i> Quản lý danh mục</a>
                <a href="/tag/manage" class="btn btn-outline-primary quick-action-btn"><i class="bi bi-tag me-2"></i> Quản lý thẻ</a>
                <a href="/document/delete" class="btn btn-outline-primary quick-action-btn"><i class="bi bi-file-earmark-text me-2"></i> Quản lý tài liệu</a>
                <a href="/course/manage" class="btn btn-outline-primary quick-action-btn"><i class="bi bi-book me-2"></i> Quản lý khóa học</a>
            </div>
        </div>
    </div>
</div>