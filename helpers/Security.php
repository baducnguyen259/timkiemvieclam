<?php

require_once __DIR__ . '/Generate.php';

/**
 * Gom các quy tắc bảo mật dùng chung cho cookie phiên và token đăng nhập.
 */
class Security {
    /**
     * Xác định request hiện tại có chạy qua HTTPS hay không, kể cả khi đứng sau reverse proxy.
     */
    public static function isSecureRequest(): bool {
        // Hỗ trợ cả trường hợp ứng dụng chạy sau reverse proxy có kết thúc TLS.
        $forwardedProto = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));

        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $forwardedProto === 'https'
            || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443');
    }

    /**
     * Tính path cookie theo BASE_PATH để cookie hoạt động đúng khi app nằm trong thư mục con.
     */
    public static function cookiePath(): string {
        return defined('BASE_PATH') && BASE_PATH !== '' ? BASE_PATH . '/' : '/';
    }

    /**
     * Chuẩn hóa SameSite cho cookie và tự hạ về Lax nếu cấu hình None nhưng request không bảo mật.
     */
    public static function sameSite(): string {
        $sameSite = ucfirst(strtolower(trim((string)($_ENV['SESSION_SAME_SITE'] ?? 'Lax'))));
        if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            $sameSite = 'Lax';
        }

        // Trình duyệt chỉ chấp nhận SameSite=None khi cookie cũng được đánh dấu Secure.
        if ($sameSite === 'None' && !self::isSecureRequest()) {
            return 'Lax';
        }

        return $sameSite;
    }

    /**
     * Tính thời điểm hết hạn cho cookie đăng nhập dựa trên AUTH_COOKIE_DAYS.
     */
    public static function authCookieExpiresAt(): int {
        $days = filter_var($_ENV['AUTH_COOKIE_DAYS'] ?? 14, FILTER_VALIDATE_INT);
        if ($days === false || $days < 1) {
            $days = 14;
        }

        return time() + ($days * 24 * 60 * 60);
    }

    /**
     * Tính thời điểm hết hạn cho cookie lưu dài ngày như danh sách việc đã lưu.
     */
    public static function persistentCookieExpiresAt(int $days = 365): int {
        return time() + ($days * 24 * 60 * 60);
    }

    /**
     * Tạo cấu hình cookie session dùng chung khi gọi session_set_cookie_params().
     */
    public static function sessionCookieParams(): array {
        return [
            'lifetime' => 0,
            'path' => self::cookiePath(),
            'domain' => '',
            'secure' => self::isSecureRequest(),
            'httponly' => true,
            'samesite' => self::sameSite(),
        ];
    }

    /**
     * Ghi cookie với các cờ bảo mật thống nhất cho toàn bộ ứng dụng.
     */
    public static function setCookie(string $name, string $value, int $expiresAt, bool $httpOnly = true): void {
        setcookie($name, $value, [
            'expires' => $expiresAt,
            'path' => self::cookiePath(),
            'secure' => self::isSecureRequest(),
            'httponly' => $httpOnly,
            'samesite' => self::sameSite(),
        ]);
    }

    /**
     * Xóa cookie ở cả base path và root path để dọn các cookie cũ còn sót.
     */
    public static function clearCookie(string $name): void {
        // Xóa ở cả base path hiện tại và root để dọn cookie cũ nếu cấu hình path từng thay đổi.
        $paths = array_unique([self::cookiePath(), '/']);

        foreach ($paths as $path) {
            setcookie($name, '', [
                'expires' => time() - 3600,
                'path' => $path,
                'secure' => self::isSecureRequest(),
                'httponly' => true,
                'samesite' => self::sameSite(),
            ]);
        }
    }

    /**
     * Tạo token đăng nhập dạng raw để trả cho trình duyệt trước khi lưu bản hash vào database.
     */
    public static function generateAuthToken(): string {
        return Generate::randomString(64);
    }

    /**
     * Băm token đăng nhập trước khi so sánh hoặc lưu trữ để tránh lưu token raw trong database.
     */
    public static function hashAuthToken(string $token): string {
        return hash('sha256', $token);
    }
}
