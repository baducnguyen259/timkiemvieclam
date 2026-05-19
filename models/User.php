<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể User.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Generate.php';
require_once __DIR__ . '/../helpers/Security.php';

class User {

    /**
     * Tạo ứng viên mới và trả về id kèm token đăng nhập raw cho cookie.
     */
    public function create($data) {
        $sql = "INSERT INTO users (full_name, email, password, token_user, phone, avatar, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $tokenUser = Security::generateAuthToken();
        
        $params = [
            $data['full_name'],
            $data['email'],
            $data['password'],
            Security::hashAuthToken($tokenUser),
            $data['phone'] ?? null,
            $data['avatar'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        Database::execute($sql, $params);
        
        return [
            'id' => Database::lastInsertId(),
            'token_user' => $tokenUser
        ];
    }
    
    /**
     * Tìm ứng viên theo email, bỏ qua bản ghi đã xóa mềm.
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$email]);
    }
    
    /**
     * Tìm ứng viên đang hoạt động bằng token raw từ cookie sau khi băm.
     */
    public function findByToken($token) {
        $sql = "SELECT * FROM users 
                WHERE token_user = ? AND status = 'active' AND deleted = 0 
                LIMIT 1";
        return Database::fetchOne($sql, [Security::hashAuthToken($token)]);
    }
    
    /**
     * Lấy ứng viên theo id nếu chưa bị xóa mềm.
     */

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$id]);
    }
    
    /**
     * Cập nhật các trường được phép của ứng viên; trả false khi không có trường hợp lệ.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['full_name', 'email', 'password', 'phone', 'avatar', 'cv_file', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return Database::execute($sql, $params);
    }
    
    /**
     * Cập nhật ứng viên dựa trên token đăng nhập hiện tại.
     */
    public function updateByToken($token, $data) {
        $user = $this->findByToken($token);
        if (!$user) {
            return false;
        }
        return $this->update($user->id, $data);
    }

    /**
     * Xóa mềm ứng viên.
     */
    public function delete($id) {
        $sql = "UPDATE users SET deleted = 1, deleted_at = NOW() WHERE id = ?";
        return Database::execute($sql, [$id]);
    }

    /**
     * Sinh token đăng nhập mới cho ứng viên và lưu bản hash vào database.
     */
    public function rotateToken($id) {
        $token = Security::generateAuthToken();
        $sql = "UPDATE users SET token_user = ? WHERE id = ?";
        Database::execute($sql, [Security::hashAuthToken($token), $id]);
        return $token;
    }

    /**
     * Thu hồi token raw hiện tại của ứng viên bằng cách xoay sang token mới.
     */
    public function revokeTokenByRawToken($token) {
        $user = $this->findByToken($token);
        if (!$user) {
            return false;
        }

        $this->rotateToken($user->id);
        return true;
    }
}
