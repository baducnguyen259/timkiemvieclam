<?php
/**
 * Giải thích mã:
 * - Tiện ích dùng chung cho nghiệp vụ kiểm tra dữ liệu.
 * - Đóng gói logic lặp lại để controller/model tập trung vào luồng nghiệp vụ chính.
 */
class Validator {
    /**
     * Kiểm tra chuỗi có phải email hợp lệ theo filter chuẩn của PHP hay không.
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Kiểm tra mật khẩu đạt độ dài tối thiểu.
     */
    public static function validatePassword($password, $minLength = 8) {
        return strlen($password) >= $minLength;
    }
    
    /**
     * Cắt khoảng trắng và escape HTML để hiển thị chuỗi an toàn trong view.
     */
    public static function sanitizeString($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Kiểm tra giá trị bắt buộc không rỗng.
     */
    public static function validateRequired($value) {
        return !empty($value);
    }
    
    /**
     * Kiểm tra giá trị có thể được xử lý như số hay không.
     */
    public static function validateNumber($value) {
        return is_numeric($value);
    }
}
