<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể Account.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Generate.php';
require_once __DIR__ . '/../helpers/Security.php';

class Account {
    
    /**
     * Tạo tài khoản quản trị/nhà tuyển dụng mới và trả về id kèm token đăng nhập raw cho cookie.
     */
    public function create($data) {
        $sql = "INSERT INTO accounts (full_name, email, password, token, phone, avatar, role_id, company_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $token = Security::generateAuthToken();
        
        $params = [
            $data['full_name'],
            $data['email'],
            $data['password'],
            Security::hashAuthToken($token),
            $data['phone'] ?? null,
            $data['avatar'] ?? null,
            $data['role_id'] ?? null,
            $data['company_id'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        Database::execute($sql, $params);
        
        return [
            'id' => Database::lastInsertId(),
            'token' => $token
        ];
    }
    
    /**
     * Tìm tài khoản theo email, bỏ qua bản ghi đã xóa mềm.
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM accounts WHERE email = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$email]);
    }
    
    /**
     * Tìm tài khoản đang hoạt động bằng token raw từ cookie sau khi băm để so sánh với database.
     */
    public function findByToken($token) {
        $sql = "SELECT * FROM accounts 
                WHERE token = ? AND status = 'active' AND deleted = 0 
                LIMIT 1";
        return Database::fetchOne($sql, [Security::hashAuthToken($token)]);
    }
    
    /**
     * Lấy một tài khoản theo id nếu chưa bị xóa mềm.
     */
    public function findById($id) {
        $sql = "SELECT * FROM accounts WHERE id = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$id]);
    }
    
    // SỬA HIỆU NĂNG: Lấy nhiều tài khoản trong một lần truy vấn
    /**
     * Lấy nhiều tài khoản theo danh sách id trong một truy vấn để tránh N+1 query.
     */
    public function findByIds($ids) {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "SELECT * FROM accounts WHERE id IN ($placeholders) AND deleted = 0";
        
        return Database::fetchAll($sql, $ids);
    }
    
    /**
     * Cập nhật các trường được phép của tài khoản; trả false khi không có trường hợp lệ.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['full_name', 'email', 'password', 'phone', 'avatar', 'role_id', 'company_id', 'status'];
        
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
        $sql = "UPDATE accounts SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return Database::execute($sql, $params);
    }
    
    /**
     * Xóa mềm tài khoản bằng cờ deleted và thời điểm deleted_at.
     */
    public function delete($id) {
        $sql = "UPDATE accounts SET deleted = 1, deleted_at = NOW() WHERE id = ?";
        return Database::execute($sql, [$id]);
    }

    /**
     * Sinh token đăng nhập mới, lưu bản hash vào database và trả token raw cho cookie.
     */
    public function rotateToken($id) {
        $token = Security::generateAuthToken();
        $sql = "UPDATE accounts SET token = ? WHERE id = ?";
        Database::execute($sql, [Security::hashAuthToken($token), $id]);
        return $token;
    }

    /**
     * Thu hồi token raw hiện tại bằng cách tìm tài khoản rồi xoay sang token mới.
     */
    public function revokeTokenByRawToken($token) {
        $account = $this->findByToken($token);
        if (!$account) {
            return false;
        }

        $this->rotateToken($account->id);
        return true;
    }
}
