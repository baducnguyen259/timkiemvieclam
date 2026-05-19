<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể Company.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class Company {
    
    /**
     * Lấy toàn bộ công ty chưa bị xóa mềm, sắp xếp mới nhất trước.
     */
    public function findAll() {
        $sql = "SELECT * FROM companies WHERE deleted = 0 ORDER BY id DESC";
        return Database::fetchAll($sql);
    }
    
    /**
     * Tìm công ty theo id nếu chưa bị xóa mềm.
     */
    public function findById($id) {
        $sql = "SELECT * FROM companies WHERE id = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$id]);
    }
    
    /**
     * Tạo công ty mới và trả về id vừa sinh.
     */
    public function create($data) {
        $sql = "INSERT INTO companies (name, address, phone, email, logo, description, website, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['address'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['logo'] ?? null,
            $data['description'] ?? null,
            $data['website'] ?? null,
            $data['status'] ?? 'active'
        ];
        
        Database::execute($sql, $params);
        return Database::lastInsertId();
    }
    
    /**
     * Cập nhật các trường được phép của công ty; trả false nếu không có dữ liệu hợp lệ.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'address', 'phone', 'email', 'logo', 'description', 'website', 'status'];
        
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
        $sql = "UPDATE companies SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return Database::execute($sql, $params);
    }
    
    /**
     * Xóa mềm công ty bằng cờ deleted và thời điểm deleted_at.
     */
    public function delete($id) {
        $sql = "UPDATE companies SET deleted = 1, deleted_at = NOW() WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
}
