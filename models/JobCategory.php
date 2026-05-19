<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể danh mục việc làm.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class JobCategory {
    
    /**
     * Lấy danh sách danh mục việc làm theo bộ lọc deleted/status/parent_id.
     */
    public function find($filters = []) {
        $sql = "SELECT * FROM job_categories WHERE 1=1";
        $params = [];
        
        if (isset($filters['deleted'])) {
            $sql .= " AND deleted = ?";
            $params[] = $filters['deleted'] ? 1 : 0;
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === null) {
                $sql .= " AND parent_id IS NULL";
            } else {
                $sql .= " AND parent_id = ?";
                $params[] = $filters['parent_id'];
            }
        }
        
        $sql .= " ORDER BY position ASC";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Tìm một danh mục theo id hoặc slug kèm các điều kiện trạng thái.
     */
    public function findOne($filters) {
        $sql = "SELECT * FROM job_categories WHERE 1=1";
        $params = [];
        
        if (isset($filters['id'])) {
            $sql .= " AND id = ?";
            $params[] = $filters['id'];
        }
        
        if (isset($filters['slug'])) {
            $sql .= " AND slug = ?";
            $params[] = $filters['slug'];
        }
        
        if (isset($filters['deleted'])) {
            $sql .= " AND deleted = ?";
            $params[] = $filters['deleted'] ? 1 : 0;
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " LIMIT 1";
        
        return Database::fetchOne($sql, $params);
    }
    
    /**
     * Tìm danh mục theo id nếu chưa bị xóa mềm.
     */
    public function findById($id) {
        return $this->findOne(['id' => $id, 'deleted' => false]);
    }
    
    /**
     * Tạo danh mục việc làm mới và trả về id vừa sinh.
     */
    public function create($data) {
        $sql = "INSERT INTO job_categories (title, parent_id, thumbnail, description, status, slug, position) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['parent_id'] ?? null,
            $data['thumbnail'] ?? null,
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $data['slug'],
            $data['position'] ?? 0
        ];
        
        Database::execute($sql, $params);
        return Database::lastInsertId();
    }
    
    /**
     * Cập nhật các trường được phép của danh mục; trả false khi không có trường hợp lệ.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'parent_id', 'thumbnail', 'description', 'status', 'slug', 'position'];
        
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
        $sql = "UPDATE job_categories SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return Database::execute($sql, $params);
    }
}
