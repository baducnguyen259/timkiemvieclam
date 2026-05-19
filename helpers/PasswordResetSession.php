<?php

class PasswordResetSession {
    public const TTL_SECONDS = 600;

    /**
     * Tạo payload lưu trong session để xác nhận người dùng đã qua bước OTP trước khi đặt lại mật khẩu.
     */
    public static function payload(string $email, string $target): array {
        return [
            'email' => $email,
            'target' => $target,
            'expires_at' => time() + self::TTL_SECONDS,
        ];
    }

    /**
     * Kiểm tra payload đặt lại mật khẩu còn đủ dữ liệu và chưa hết hạn.
     */
    public static function isValid($payload): bool {
        return is_array($payload)
            && isset($payload['email'], $payload['expires_at'])
            && is_string($payload['email'])
            && $payload['email'] !== ''
            && is_numeric($payload['expires_at'])
            && (int)$payload['expires_at'] >= time();
    }
}
