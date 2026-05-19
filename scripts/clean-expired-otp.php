<?php
/**
 * Giải thích mã:
 * - Tập lệnh bảo trì cho tác vụ clean-expired-otp.
 * - Thực thi các thao tác một lần hoặc theo lô để sửa/khởi tạo/dọn dữ liệu dự án.
 */
/**
 * Tập lệnh dọn dẹp OTP đã hết hạn
 * Chạy định kỳ: php scripts/clean-expired-otp.php
 * Tác vụ cron mỗi 5 phút: 0/5 * * * * php /path/to/scripts/clean-expired-otp.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ForgotPassword.php';

try {
    $forgotPasswordModel = new ForgotPassword();
    $deleted = $forgotPasswordModel->cleanExpired();
    
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Đã xóa $deleted OTP hết hạn.\n";
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
