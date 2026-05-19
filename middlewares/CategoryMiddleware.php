<?php
/**
 * Giải thích mã:
 * - Middleware bảo vệ luồng danh mục.
 * - Chạy trước controller để kiểm tra đăng nhập, quyền truy cập và ngữ cảnh request.
 */
require_once __DIR__ . '/../models/JobCategory.php';
require_once __DIR__ . '/../helpers/CreateTree.php';

class CategoryMiddleware {
    /**
     * Nạp danh mục việc làm đang hoạt động vào biến toàn cục để layout hiển thị menu danh mục.
     */
    public static function handle() {
        try {
            // SỬA ĐỘ TIN CẬY: Bổ sung xử lý lỗi
            $categoryModel = new JobCategory();
            $categories = $categoryModel->find([
                'deleted' => false,
                'status' => 'active'
            ]);
            
            // SỬA HIỆU NĂNG: Lấy danh mục một lần và dựng cây trong bộ nhớ
            $tree = CreateTree::build($categories);
            $GLOBALS['layoutProductsCategory'] = $tree;
            
        } catch (Exception $e) {
            error_log("CategoryMiddleware error: " . $e->getMessage());
            $GLOBALS['layoutProductsCategory'] = [];
        }
    }
}
