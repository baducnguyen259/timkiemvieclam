<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể quên mật khẩu.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class ForgotPassword {
    
    /**
     * Tạo OTP mới cho email và xóa OTP cũ để mỗi email chỉ còn mã mới nhất.
     */
    public function create($email, $otp) {
        // Xóa OTP cũ của email này
        $this->deleteByEmail($email);
        
        // Tính expire_at theo múi giờ MySQL để tránh lệch múi giờ PHP/MySQL
        $sql = "INSERT INTO forgot_password (email, otp, expire_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 3 MINUTE))";
        
        Database::execute($sql, [$email, $otp]);
        return Database::lastInsertId();
    }
    
    /**
     * Xác minh OTP còn hạn; nếu hợp lệ thì xóa OTP để không thể dùng lại.
     */
    public function verify($email, $otp) {
        $sql = "SELECT * FROM forgot_password 
                WHERE email = ? AND otp = ? AND expire_at > NOW() 
                ORDER BY created_at DESC LIMIT 1";
        
        $result = Database::fetchOne($sql, [$email, $otp]);
        
        if ($result) {
            // Xóa OTP đã dùng
            $this->deleteById($result->id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Xóa toàn bộ OTP đang lưu của một email.
     */
    private function deleteByEmail($email) {
        $sql = "DELETE FROM forgot_password WHERE email = ?";
        Database::execute($sql, [$email]);
    }
    
    /**
     * Xóa một bản ghi OTP theo id.
     */
    private function deleteById($id) {
        $sql = "DELETE FROM forgot_password WHERE id = ?";
        Database::execute($sql, [$id]);
    }
    
    // Dọn OTP hết hạn (chạy định kỳ)
    /**
     * Dọn các OTP đã hết hạn, dùng cho cron hoặc script bảo trì định kỳ.
     */
    public function cleanExpired() {
        $sql = "DELETE FROM forgot_password WHERE expire_at < NOW()";
        return Database::execute($sql);
    }
}
