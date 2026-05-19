<?php
/**
 * Giải thích mã:
 * - Middleware bảo vệ luồng người dùng.
 * - Chạy trước controller để kiểm tra đăng nhập, quyền truy cập và ngữ cảnh request.
 */
require_once __DIR__ . '/../models/User.php';

class UserMiddleware {
    /**
     * Nếu cookie ứng viên hợp lệ thì nạp thông tin người dùng hiện tại vào $GLOBALS cho các controller/view.
     */
    public static function handle() {
        if (isset($_COOKIE['tokenUser'])) {
            try {
                $userModel = new User();
                $user = $userModel->findByToken($_COOKIE['tokenUser']);
                
                if ($user && $user->status === 'active' && !$user->deleted) {
                    $GLOBALS['current_user'] = $user;
                }
            } catch (Exception $e) {
                error_log("UserMiddleware error: " . $e->getMessage());
            }
        }
    }
}
