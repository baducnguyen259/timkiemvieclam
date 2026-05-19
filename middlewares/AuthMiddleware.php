<?php
/**
 * Giải thích mã:
 * - Middleware bảo vệ luồng xác thực người dùng.
 * - Chạy trước controller để kiểm tra đăng nhập, quyền truy cập và ngữ cảnh request.
 */
require_once __DIR__ . '/../models/User.php';

class AuthMiddleware {
    /**
     * Bắt buộc ứng viên phải đăng nhập hợp lệ trước khi truy cập route cần xác thực.
     */
    public static function requireAuth() {
        if (!isset($_COOKIE['tokenUser'])) {
            $_SESSION['flash_error'] = 'Bạn chưa đăng nhập!';
            header('Location: ' . BASE_PATH . '/user/login');
            exit; // SỬA BẢO MẬT: Thêm exit để tránh bỏ qua kiểm tra
        }
        
        $userModel = new User();
        $user = $userModel->findByToken($_COOKIE['tokenUser']);
        
        if (!$user) {
            $_SESSION['flash_error'] = 'Bạn chưa đăng nhập!';
            header('Location: ' . BASE_PATH . '/user/login');
            exit; // SỬA BẢO MẬT: Thêm exit để tránh bỏ qua kiểm tra
        }
        
        // Lưu thông tin người dùng vào biến toàn cục
        $GLOBALS['current_user'] = $user;
    }
}
