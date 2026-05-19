<?php
/**
 * Giải thích mã:
 * - Middleware bảo vệ luồng xác thực nhà tuyển dụng.
 * - Chạy trước controller để kiểm tra đăng nhập, quyền truy cập và ngữ cảnh request.
 */
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../helpers/Security.php';

class EmployerAuthMiddleware {
    /**
     * Bắt buộc tài khoản hiện tại phải đăng nhập ở cổng nhà tuyển dụng và có vai trò Employer.
     */
    public static function requireAuth() {
        if (!isset($_COOKIE['tokenEmployer'])) {
            $_SESSION['flash_error'] = 'Bạn chưa đăng nhập cổng nhà tuyển dụng';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        $accountModel = new Account();
        $account = $accountModel->findByToken($_COOKIE['tokenEmployer']);

        if (!$account) {
            $_SESSION['flash_error'] = 'Phiên đăng nhập không hợp lệ';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        $role = null;
        if ($account->role_id) {
            $roleModel = new Role();
            $role = $roleModel->findById($account->role_id);
        }

        if (!$role || strtolower($role->title) !== 'employer') {
            Security::clearCookie('tokenEmployer');
            $_SESSION['flash_error'] = 'Tài khoản không có quyền truy cập cổng nhà tuyển dụng';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        $GLOBALS['current_role'] = $role;
        $GLOBALS['current_user'] = $account;
    }
}
