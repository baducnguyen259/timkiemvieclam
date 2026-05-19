<?php
/**
 * Giải thích mã:
 * - Middleware bảo vệ luồng xác thực quản trị.
 * - Chạy trước controller để kiểm tra đăng nhập, quyền truy cập và ngữ cảnh request.
 */
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../helpers/Security.php';

class AdminAuthMiddleware {
    /**
     * Bắt buộc người dùng hiện tại phải đăng nhập bằng token admin và có vai trò Admin.
     */
    public static function requireAuth() {
        if (!isset($_COOKIE['tokenAdmin'])) {
            $_SESSION['flash_error'] = 'Bạn chưa đăng nhập!';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        $accountModel = new Account();
        $account = $accountModel->findByToken($_COOKIE['tokenAdmin']);

        if (!$account) {
            $_SESSION['flash_error'] = 'Bạn chưa đăng nhập!';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        $role = null;
        if ($account->role_id) {
            $roleModel = new Role();
            $role = $roleModel->findById($account->role_id);
            $GLOBALS['current_role'] = $role;
        }

        // Khu vực admin chỉ cho phép vai trò Admin.
        if (!$role || strtolower(trim((string)$role->title)) !== 'admin') {
            Security::clearCookie('tokenAdmin');
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập đúng cổng quản trị';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        $GLOBALS['current_user'] = $account;
    }
}
