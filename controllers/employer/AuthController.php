<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển cổng nhà tuyển dụng cho tính năng Auth.
 * - Xử lý thao tác nhà tuyển dụng và trả về giao diện bảng điều khiển, việc làm, ứng tuyển tương ứng.
 */
require_once __DIR__ . '/../../models/Account.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../helpers/Security.php';
require_once __DIR__ . '/../../helpers/Csrf.php';

class EmployerAuthController {
    private $accountModel;
    private $roleModel;

    /**
     * Khởi tạo model tài khoản và vai trò cho cổng nhà tuyển dụng.
     */
    public function __construct() {
        $this->accountModel = new Account();
        $this->roleModel = new Role();
    }

    // Tuyến GET: /employer/auth/login
    /**
     * Điều hướng form đăng nhập nhà tuyển dụng về form đăng nhập dùng chung.
     */
    public function login() {
        header('Location: ' . BASE_PATH . '/user/login');
        exit;
    }

    // Tuyến POST: /employer/auth/login
    /**
     * Xử lý đăng nhập nhà tuyển dụng, kiểm tra vai trò Employer rồi cấp token employer.
     */
    public function loginPost() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        $account = $this->accountModel->findByEmail($email);
        if (!$account) {
            $_SESSION['flash_error'] = 'Email không tồn tại';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        if (!password_verify($password, $account->password)) {
            $_SESSION['flash_error'] = 'Mật khẩu không đúng';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        if ($account->status !== 'active') {
            $_SESSION['flash_error'] = 'Tài khoản đã bị khóa';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        $role = null;
        if ($account->role_id) {
            $role = $this->roleModel->findById($account->role_id);
        }

        if (!$role || strtolower($role->title) !== 'employer') {
            $_SESSION['flash_error'] = 'Tài khoản này không thuộc cổng nhà tuyển dụng';
            header('Location: ' . BASE_PATH . '/user/login');
            exit;
        }

        // Tách biệt session giữa các cổng
        $rotatedToken = $this->accountModel->rotateToken($account->id);
        session_regenerate_id(true);
        Csrf::rotate();

        Security::clearCookie('tokenAdmin');
        Security::setCookie('tokenEmployer', $rotatedToken, Security::authCookieExpiresAt());

        $_SESSION['flash_success'] = 'Đăng nhập nhà tuyển dụng thành công!';
        header('Location: ' . BASE_PATH . '/employer/dashboard');
        exit;
    }

        // Route POST: /employer/auth/logout
    /**
     * Đăng xuất nhà tuyển dụng, thu hồi token hiện tại và xóa cookie liên quan.
     */
    public function logout() {
        $token = $_COOKIE['tokenEmployer'] ?? '';
        if (is_string($token) && $token !== '') {
            $this->accountModel->revokeTokenByRawToken($token);
        }

        Security::clearCookie('tokenAdmin');
        Security::clearCookie('tokenEmployer');
        session_regenerate_id(true);
        Csrf::rotate();
        $_SESSION['flash_success'] = 'Đăng xuất thành công!';
        header('Location: ' . BASE_PATH . '/user/login');
        exit;
    }
}
