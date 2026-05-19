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
    
}
