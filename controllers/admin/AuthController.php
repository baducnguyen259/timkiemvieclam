<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Auth.
 * - Xử lý kiểm tra request, gọi model và hiển thị/chuyển hướng cho trang quản trị.
 */
require_once __DIR__ . '/../../models/Account.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../helpers/Security.php';
require_once __DIR__ . '/../../helpers/Csrf.php';

class AdminAuthController {
    private $accountModel;
    private $roleModel;

    /**
     * Khởi tạo model tài khoản và vai trò để xác thực cổng quản trị.
     */
    public function __construct() {
        $this->accountModel = new Account();
        $this->roleModel = new Role();
    }

    // Tuyến GET: /admin/auth/login
    /**
     * Hiển thị form đăng nhập quản trị.
     */
    public function login() {
        $pageTitle = 'Đăng nhập quản trị';
        require_once __DIR__ . '/../../views/admin/auth/login.php';
    }

    // Tuyến POST: /admin/auth/login
    /**
     * Xử lý đăng nhập admin, kiểm tra mật khẩu, trạng thái và vai trò trước khi cấp token.
     */
    public function loginPost() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Vui lòng nhập đầy đủ thông tin';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        $account = $this->accountModel->findByEmail($email);
        if (!$account) {
            $_SESSION['flash_error'] = 'Email không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        if (!password_verify($password, $account->password)) {
            $_SESSION['flash_error'] = 'Mật khẩu không đúng';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        if ($account->status !== 'active') {
            $_SESSION['flash_error'] = 'Tài khoản đã bị khóa';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        $role = null;
        if ($account->role_id) {
            $role = $this->roleModel->findById($account->role_id);
        }

        if (!$role || strtolower(trim((string)$role->title)) !== 'admin') {
            $_SESSION['flash_error'] = 'Tài khoản này không thuộc cổng quản trị';
            header('Location: ' . BASE_PATH . '/admin/auth/login');
            exit;
        }

        $rotatedToken = $this->accountModel->rotateToken($account->id);
        session_regenerate_id(true);
        Csrf::rotate();
        Security::setCookie('tokenAdmin', $rotatedToken, Security::authCookieExpiresAt());

        $_SESSION['flash_success'] = 'Đăng nhập thành công!';
        header('Location: ' . BASE_PATH . '/admin/dashboard');
        exit;
    }

        // Route POST: /admin/auth/logout
    /**
     * Đăng xuất admin, thu hồi token hiện tại và xóa cookie xác thực quản trị.
     */
    public function logout() {
        $token = $_COOKIE['tokenAdmin'] ?? '';
        if (is_string($token) && $token !== '') {
            $this->accountModel->revokeTokenByRawToken($token);
        }

        Security::clearCookie('tokenAdmin');
        session_regenerate_id(true);
        Csrf::rotate();
        $_SESSION['flash_success'] = 'Đăng xuất thành công!';
        header('Location: ' . BASE_PATH . '/admin/auth/login');
        exit;
    }
}
