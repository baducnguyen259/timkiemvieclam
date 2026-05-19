<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể Job.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class Job {
    
    // Lấy danh sách việc làm theo bộ lọc
    /**
     * Lấy danh sách việc làm theo bộ lọc và tùy chọn sắp xếp/phân trang.
     */
    public function find($filters = [], $options = []) {
        $sql = "SELECT j.*, c.title as category_name 
                FROM jobs j 
                LEFT JOIN job_categories c ON j.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        // Lọc theo ID (một giá trị hoặc mảng)
        if (isset($filters['id'])) {
            if (is_array($filters['id'])) {
                if (empty($filters['id'])) {
                    return []; // Không có ID để đối chiếu
                }
                $placeholders = str_repeat('?,', count($filters['id']) - 1) . '?';
                $sql .= " AND j.id IN ($placeholders)";
                $params = array_merge($params, $filters['id']);
            } else {
                $sql .= " AND j.id = ?";
                $params[] = $filters['id'];
            }
        }
        
        // Áp dụng bộ lọc
        if (isset($filters['deleted'])) {
            $sql .= " AND j.deleted = ?";
            $params[] = $filters['deleted'] ? 1 : 0;
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND j.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['featured'])) {
            $sql .= " AND j.featured = ?";
            $params[] = $filters['featured'];
        }
        
        if (isset($filters['category_id'])) {
            if (is_array($filters['category_id'])) {
                $placeholders = str_repeat('?,', count($filters['category_id']) - 1) . '?';
                $sql .= " AND j.category_id IN ($placeholders)";
                $params = array_merge($params, $filters['category_id']);
            } else {
                $sql .= " AND j.category_id = ?";
                $params[] = $filters['category_id'];
            }
        }
        
        if (isset($filters['location'])) {
            $sql .= " AND j.location = ?";
            $params[] = $filters['location'];
        }
        
        if (isset($filters['type'])) {
            $sql .= " AND j.type = ?";
            $params[] = $filters['type'];
        }
        
        if (isset($filters['title_search'])) {
            $keyword = trim((string)$filters['title_search']);
            if ($keyword !== '') {
                $tokens = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($tokens)) {
                    foreach ($tokens as $token) {
                        $sql .= " AND j.title LIKE ?";
                        $params[] = '%' . Database::escapeLike($token) . '%';
                    }
                }
            }
        }
        
        if (isset($filters['created_by_account_id'])) {
            $sql .= " AND j.created_by_account_id = ?";
            $params[] = $filters['created_by_account_id'];
        }
        
        // Sắp xếp
        if (isset($options['sort'])) {
            $allowedSortFields = [
                'id',
                'title',
                'company_name',
                'location',
                'salary_min',
                'salary_max',
                'featured',
                'type',
                'experience',
                'application_deadline',
                'status',
                'slug',
                'position',
                'created_at',
                'updated_at',
                'created_by_account_id'
            ];
            $sortParts = [];
            foreach ($options['sort'] as $field => $direction) {
                if (!in_array($field, $allowedSortFields, true)) {
                    continue;
                }
                $dir = ($direction === -1 || strtoupper($direction) === 'DESC') ? 'DESC' : 'ASC';
                $sortParts[] = "j.$field $dir";
            }
            if (!empty($sortParts)) {
                $sql .= " ORDER BY " . implode(', ', $sortParts);
            } else {
                $sql .= " ORDER BY j.position DESC";
            }
        } else {
            $sql .= " ORDER BY j.position DESC";
        }
        
        // Phân trang
        if (isset($options['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$options['limit'];
            
            if (isset($options['skip'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$options['skip'];
            }
        }
        
        return Database::fetchAll($sql, $params);
    }
    
    // Lấy một việc làm
    /**
     * Lấy một việc làm theo id hoặc slug, đồng thời nạp danh sách kỹ năng nếu tìm thấy.
     */
    public function findOne($filters) {
        $sql = "SELECT j.*, c.title as category_name 
                FROM jobs j 
                LEFT JOIN job_categories c ON j.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['id'])) {
            $sql .= " AND j.id = ?";
            $params[] = $filters['id'];
        }
        
        if (isset($filters['slug'])) {
            $sql .= " AND j.slug = ?";
            $params[] = $filters['slug'];
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND j.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['deleted'])) {
            $sql .= " AND j.deleted = ?";
            $params[] = $filters['deleted'] ? 1 : 0;
        }
        
        $sql .= " LIMIT 1";
        
        $job = Database::fetchOne($sql, $params);
        
        // Nạp danh sách kỹ năng
        if ($job) {
            $job->skills = $this->getJobSkills($job->id);
        }
        
        return $job;
    }
    
    // Lấy kỹ năng việc làm
    /**
     * Lấy danh sách tên kỹ năng gắn với một tin tuyển dụng.
     */
    private function getJobSkills($jobId) {
        $sql = "SELECT skill_name FROM job_skills WHERE job_id = ?";
        $skills = Database::fetchAll($sql, [$jobId]);
        return array_map(function($s) { return $s->skill_name; }, $skills);
    }
    
    // Tạo việc làm
    /**
     * Tạo tin tuyển dụng mới và ghi thêm các kỹ năng liên quan nếu có.
     */
    public function create($data) {
        $sql = "INSERT INTO jobs (
            title, company_name, company_logo, description, candidate_requirements, benefits, location, address_detail, category_id, thumbnail,
            salary_min, salary_max, featured, type, experience, application_deadline,
            status, slug, position, created_by_account_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['company_name'] ?? null,
            $data['company_logo'] ?? null,
            $data['description'] ?? null,
            $data['candidate_requirements'] ?? null,
            $data['benefits'] ?? null,
            $data['location'] ?? null,
            $data['address_detail'] ?? null,
            $data['category_id'] ?? null,
            $data['thumbnail'] ?? null,
            $data['salary_min'] ?? null,
            $data['salary_max'] ?? null,
            $data['featured'] ?? '0',
            $data['type'] ?? null,
            $data['experience'] ?? null,
            $data['application_deadline'] ?? null,
            $data['status'] ?? 'active',
            $data['slug'],
            $data['position'] ?? 0,
            $data['created_by_account_id'] ?? null
        ];
        
        Database::execute($sql, $params);
        $jobId = Database::lastInsertId();
        
        // Thêm kỹ năng
        if (isset($data['skills']) && is_array($data['skills'])) {
            $this->updateJobSkills($jobId, $data['skills']);
        }
        
        return $jobId;
    }
    
    // Cập nhật việc làm
    /**
     * Cập nhật tin tuyển dụng, đồng bộ kỹ năng và ghi nhận người cập nhật khi có dữ liệu.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'title', 'company_name', 'company_logo', 'description', 'candidate_requirements', 'benefits', 'location', 'address_detail', 'category_id', 'thumbnail',
            'salary_min', 'salary_max', 'featured', 'type', 'experience', 'application_deadline',
            'status', 'position'
        ];
        
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
        $sql = "UPDATE jobs SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $result = Database::execute($sql, $params);
        
        // Cập nhật kỹ năng
        if (isset($data['skills']) && is_array($data['skills'])) {
            $this->updateJobSkills($id, $data['skills']);
        }
        
        // Ghi nhận cập nhật
        if (isset($data['updated_by_account_id'])) {
            $this->trackUpdate($id, $data['updated_by_account_id']);
        }
        
        return $result;
    }
    
    // Cập nhật kỹ năng việc làm
    /**
     * Thay thế toàn bộ kỹ năng của tin tuyển dụng bằng danh sách mới.
     */
    private function updateJobSkills($jobId, $skills) {
        // Xóa kỹ năng cũ
        Database::execute("DELETE FROM job_skills WHERE job_id = ?", [$jobId]);
        
        // Thêm kỹ năng mới
        if (!empty($skills)) {
            $sql = "INSERT INTO job_skills (job_id, skill_name) VALUES (?, ?)";
            foreach ($skills as $skill) {
                Database::execute($sql, [$jobId, trim($skill)]);
            }
        }
    }
    
    // Ghi nhận người cập nhật việc làm
    /**
     * Ghi lịch sử tài khoản đã cập nhật tin tuyển dụng.
     */
    private function trackUpdate($jobId, $accountId) {
        $sql = "INSERT INTO job_updates (job_id, account_id) VALUES (?, ?)";
        Database::execute($sql, [$jobId, $accountId]);
    }
    
    // Xóa mềm
    /**
     * Xóa mềm tin tuyển dụng và tùy chọn ghi lại tài khoản thực hiện.
     */
    public function delete($id, $accountId = null) {
        $sql = "UPDATE jobs SET deleted = 1, deleted_at = NOW()";
        $params = [];
        
        if ($accountId) {
            $sql .= ", deleted_by_account_id = ?";
            $params[] = $accountId;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        return Database::execute($sql, $params);
    }
    
    // Đếm số bản ghi
    /**
     * Đếm số tin tuyển dụng theo bộ lọc, dùng cho phân trang và thống kê.
     */
    public function countDocuments($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM jobs WHERE 1=1";
        $params = [];
        
        if (isset($filters['deleted'])) {
            $sql .= " AND deleted = ?";
            $params[] = $filters['deleted'] ? 1 : 0;
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['featured'])) {
            $sql .= " AND featured = ?";
            $params[] = $filters['featured'];
        }
        
        if (isset($filters['category_id'])) {
            if (is_array($filters['category_id'])) {
                $placeholders = str_repeat('?,', count($filters['category_id']) - 1) . '?';
                $sql .= " AND category_id IN ($placeholders)";
                $params = array_merge($params, $filters['category_id']);
            } else {
                $sql .= " AND category_id = ?";
                $params[] = $filters['category_id'];
            }
        }
        
        if (isset($filters['location'])) {
            $sql .= " AND location = ?";
            $params[] = $filters['location'];
        }
        
        if (isset($filters['type'])) {
            $sql .= " AND type = ?";
            $params[] = $filters['type'];
        }
        
        if (isset($filters['title_search'])) {
            $keyword = trim((string)$filters['title_search']);
            if ($keyword !== '') {
                $tokens = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($tokens)) {
                    foreach ($tokens as $token) {
                        $sql .= " AND title LIKE ?";
                        $params[] = '%' . Database::escapeLike($token) . '%';
                    }
                }
            }
        }
        
        if (isset($filters['created_by_account_id'])) {
            $sql .= " AND created_by_account_id = ?";
            $params[] = $filters['created_by_account_id'];
        }
        
        $result = Database::fetchOne($sql, $params);
        return $result ? $result->count : 0;
    }

    // Lấy giá trị duy nhất cho bộ lọc động (địa điểm/loại hình)
    /**
     * Lấy các giá trị duy nhất của trường được cho phép để dựng bộ lọc động.
     */
    public function getDistinctFieldValues($field, $filters = []) {
        $allowedFields = ['location', 'type'];
        if (!in_array($field, $allowedFields, true)) {
            return [];
        }

        $sql = "SELECT DISTINCT $field AS value FROM jobs WHERE 1=1";
        $params = [];

        if (isset($filters['deleted'])) {
            $sql .= " AND deleted = ?";
            $params[] = $filters['deleted'] ? 1 : 0;
        }

        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['category_id'])) {
            if (is_array($filters['category_id'])) {
                if (empty($filters['category_id'])) {
                    return [];
                }
                $placeholders = str_repeat('?,', count($filters['category_id']) - 1) . '?';
                $sql .= " AND category_id IN ($placeholders)";
                $params = array_merge($params, $filters['category_id']);
            } else {
                $sql .= " AND category_id = ?";
                $params[] = $filters['category_id'];
            }
        }

        if (isset($filters['created_by_account_id'])) {
            $sql .= " AND created_by_account_id = ?";
            $params[] = $filters['created_by_account_id'];
        }

        $sql .= " AND $field IS NOT NULL AND TRIM($field) <> '' ORDER BY $field ASC";

        $rows = Database::fetchAll($sql, $params);
        $values = [];

        foreach ($rows as $row) {
            $value = trim((string)($row->value ?? ''));
            if ($value !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }
    
    // Đổi trạng thái nhiều tin tuyển dụng
    /**
     * Cập nhật trạng thái hoặc xóa mềm nhiều tin tuyển dụng trong một truy vấn.
     */
    public function updateMany($ids, $data) {
        if (empty($ids)) {
            return 0;
        }
        
        $fields = [];
        $params = [];
        
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (isset($data['deleted'])) {
            $fields[] = "deleted = ?";
            $params[] = $data['deleted'] ? 1 : 0;
            
            if ($data['deleted']) {
                $fields[] = "deleted_at = NOW()";
                
                if (isset($data['deleted_by_account_id'])) {
                    $fields[] = "deleted_by_account_id = ?";
                    $params[] = $data['deleted_by_account_id'];
                }
            }
        }
        
        if (empty($fields)) {
            return 0;
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $params = array_merge($params, $ids);
        
        $sql = "UPDATE jobs SET " . implode(', ', $fields) . " WHERE id IN ($placeholders)";
        
        return Database::execute($sql, $params);
    }
}
