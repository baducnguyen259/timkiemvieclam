<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể Role.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class Role {
    
    /**
     * Lấy toàn bộ vai trò chưa bị xóa mềm.
     */
    public function findAll() {
        $sql = "SELECT * FROM roles WHERE deleted = 0 ORDER BY id ASC";
        return Database::fetchAll($sql);
    }
    
    /**
     * Tìm vai trò theo id nếu chưa bị xóa mềm.
     */
    public function findById($id) {
        $sql = "SELECT * FROM roles WHERE id = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Tìm vai trò theo tên, không phân biệt chữ hoa chữ thường.
     */
    public function findByTitle($title) {
        $sql = "SELECT * FROM roles WHERE LOWER(title) = LOWER(?) AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$title]);
    }
    
    /**
     * Tạo vai trò mới, tự chuyển mảng quyền thành JSON trước khi lưu.
     */
    public function create($data) {
        $sql = "INSERT INTO roles (title, description, permissions) VALUES (?, ?, ?)";
        
        $permissions = is_array($data['permissions']) 
            ? json_encode($data['permissions']) 
            : $data['permissions'];
        
        $params = [
            $data['title'],
            $data['description'] ?? null,
            $permissions
        ];
        
        Database::execute($sql, $params);
        return Database::lastInsertId();
    }
    
    /**
     * Cập nhật thông tin vai trò và danh sách quyền nếu dữ liệu được gửi lên.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $params[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (isset($data['permissions'])) {
            $fields[] = "permissions = ?";
            $params[] = is_array($data['permissions']) 
                ? json_encode($data['permissions']) 
                : $data['permissions'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE roles SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return Database::execute($sql, $params);
    }
}
