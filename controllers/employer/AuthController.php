<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển cổng nhà tuyển dụng cho tính năng Auth.
 * - Xử lý thao tác nhà tuyển dụng và trả về giao diện bảng điều khiển, việc làm, ứng tuyển tương ứng.
 */
require_once __DIR__ . '/../../models/Account.php';
require_once __DIR__ . '/../../helpers/Security.php';
require_once __DIR__ . '/../../helpers/Csrf.php';

class EmployerAuthController {
    private $accountModel;

    /**
     * Khởi tạo model tài khoản cho cổng nhà tuyển dụng.
     */
    public function __construct() {
        $this->accountModel = new Account();
    }

    // Tuyến GET: /employer/auth/login
    /**
     * Điều hướng form đăng nhập nhà tuyển dụng về form đăng nhập dùng chung.
     */
    public function login() {
        header('Location: ' . BASE_PATH . '/user/login');
        exit;
    }

    // Tuyến POST: /employer/auth/logout
    /**
     * Đăng xuất nhà tuyển dụng, thu hồi token hiện tại và xóa cookie liên quan.
     */
    public function logout() {
        $token = $_COOKIE['tokenEmployer'] ?? '';
        if (is_string($token) && $token !== '') {
            $this->accountModel->revokeTokenByRawToken($token);
        }

        // Fix #28: Chỉ xóa cookie của nhà tuyển dụng để đảm bảo cách ly phiên đăng nhập
        Security::clearCookie('tokenEmployer');
        session_regenerate_id(true);
        Csrf::rotate();
        $_SESSION['flash_success'] = 'Đăng xuất thành công!';
        header('Location: ' . BASE_PATH . '/user/login');
        exit;
    }
}
