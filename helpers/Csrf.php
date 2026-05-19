<?php

/**
 * Quản lý CSRF token theo session và chặn các request POST không hợp lệ.
 */
class Csrf {
    private const SESSION_KEY = '_csrf_token';

    /**
     * Lấy CSRF token hiện tại của session, tự tạo mới nếu session chưa có token.
     */
    public static function token(): string {
        // Tạo token theo nhu cầu để mọi form trong cùng session dùng chung một giá trị hợp lệ.
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Tạo lại CSRF token sau các mốc nhạy cảm như đăng nhập, đăng xuất hoặc đổi phiên.
     */
    public static function rotate(): string {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Sinh input hidden chứa CSRF token để nhúng vào form HTML.
     */
    public static function field(): string {
        return '<input type="hidden" name="_csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    /**
     * Lấy token từ POST body hoặc header X-CSRF-TOKEN để hỗ trợ cả form thường và AJAX.
     */
    public static function requestToken(): string {
        // Ưu tiên token từ form, sau đó mới đọc header cho các request AJAX/API.
        $postedToken = $_POST['_csrf_token'] ?? null;
        if (is_string($postedToken) && $postedToken !== '') {
            return $postedToken;
        }

        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($headerToken) ? $headerToken : '';
    }

    /**
     * So sánh token gửi lên với token trong session bằng hash_equals để tránh timing attack.
     */
    public static function isValid(?string $token): bool {
        return is_string($token)
            && $token !== ''
            && hash_equals(self::token(), $token);
    }

    /**
     * Chặn toàn bộ request POST thiếu hoặc sai CSRF token trước khi vào router/controller.
     */
    public static function enforceForPostRequest(): void {
        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            return;
        }

        if (self::isValid(self::requestToken())) {
            return;
        }

        http_response_code(403);
        echo '403 - Token CSRF không hợp lệ';
        exit;
    }
}

/**
 * Hàm tắt giúp view lấy giá trị CSRF token mà không cần gọi trực tiếp class Csrf.
 */
function csrf_token(): string {
    return Csrf::token();
}

/**
 * Hàm tắt giúp view in input hidden CSRF token trong form.
 */
function csrf_field(): string {
    return Csrf::field();
}
