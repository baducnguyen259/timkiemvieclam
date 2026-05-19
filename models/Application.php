<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể Application.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class Application {
    // Lưu kết quả kiểm tra cột để tránh đọc metadata của bảng nhiều lần trong cùng request.
    private static $hasCvLinkColumn = null;

    /**
     * Kiểm tra schema hiện tại có cột cv_link hay không để giữ tương thích với database cũ.
     */
    private function hasCvLinkColumn() {
        if (self::$hasCvLinkColumn !== null) {
            return self::$hasCvLinkColumn;
        }

        // Giữ tương thích với cả database cũ chưa có cột cv_link.
        $columns = Database::getTableColumns('applications');
        self::$hasCvLinkColumn = in_array('cv_link', $columns, true);

        return self::$hasCvLinkColumn;
    }
    
    /**
     * Tạo đơn ứng tuyển mới, tự thêm cv_link nếu database hỗ trợ cột này.
     */
    public function create($data) {
        $columns = ['job_id', 'user_id', 'full_name', 'email', 'phone', 'cv_file'];
        $params = [
            $data['job_id'],
            $data['user_id'] ?? null,
            $data['full_name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['cv_file'] ?? null
        ];

        // Chỉ ghi cv_link khi schema hiện tại hỗ trợ cột này.
        if ($this->hasCvLinkColumn()) {
            $columns[] = 'cv_link';
            $params[] = $data['cv_link'] ?? null;
        }

        $columns[] = 'cover_letter';
        $columns[] = 'status';
        $params[] = $data['cover_letter'] ?? null;
        $params[] = $data['status'] ?? 'pending';

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO applications (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        Database::execute($sql, $params);
        return Database::lastInsertId();
    }
    
    /**
     * Lấy danh sách đơn ứng tuyển của một tin tuyển dụng.
     */
    public function findByJobId($jobId) {
        $sql = "SELECT * FROM applications WHERE job_id = ? AND deleted = 0 ORDER BY created_at DESC";
        return Database::fetchAll($sql, [$jobId]);
    }
    
    /**
     * Lấy các đơn ứng tuyển của một ứng viên kèm thông tin tiêu đề/slug việc làm.
     */
    public function findByUserId($userId) {
        $sql = "SELECT a.*, j.title as job_title, j.slug as job_slug 
                FROM applications a 
                LEFT JOIN jobs j ON a.job_id = j.id 
                WHERE a.user_id = ? AND a.deleted = 0 
                ORDER BY a.created_at DESC";
        return Database::fetchAll($sql, [$userId]);
    }

    /**
     * Lấy đơn ứng tuyển thuộc các tin do nhà tuyển dụng tạo, có hỗ trợ lọc và phân trang.
     */
    public function findByEmployerId($accountId, $filters = [], $options = []) {
        $sql = "SELECT a.*, j.title AS job_title
                FROM applications a
                INNER JOIN jobs j ON j.id = a.job_id
                WHERE j.created_by_account_id = ? AND j.deleted = 0 AND a.deleted = 0";
        $params = [$accountId];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['job_id']) && $filters['job_id'] !== '') {
            $sql .= " AND a.job_id = ?";
            $params[] = (int)$filters['job_id'];
        }

        if (isset($filters['keyword'])) {
            $keyword = trim((string)$filters['keyword']);
            if ($keyword !== '') {
                $tokens = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($tokens)) {
                    foreach ($tokens as $token) {
                        $sql .= " AND (a.full_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ? OR j.title LIKE ?)";
                        $like = '%' . Database::escapeLike($token) . '%';
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                    }
                }
            }
        }

        $sql .= " ORDER BY a.created_at DESC";

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

    /**
     * Đếm số đơn ứng tuyển của nhà tuyển dụng theo cùng bộ lọc với danh sách.
     */
    public function countByEmployerId($accountId, $filters = []) {
        $sql = "SELECT COUNT(*) AS count
                FROM applications a
                INNER JOIN jobs j ON j.id = a.job_id
                WHERE j.created_by_account_id = ? AND j.deleted = 0 AND a.deleted = 0";
        $params = [$accountId];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['job_id']) && $filters['job_id'] !== '') {
            $sql .= " AND a.job_id = ?";
            $params[] = (int)$filters['job_id'];
        }

        if (isset($filters['keyword'])) {
            $keyword = trim((string)$filters['keyword']);
            if ($keyword !== '') {
                $tokens = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($tokens)) {
                    foreach ($tokens as $token) {
                        $sql .= " AND (a.full_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ? OR j.title LIKE ?)";
                        $like = '%' . Database::escapeLike($token) . '%';
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                    }
                }
            }
        }

        $result = Database::fetchOne($sql, $params);
        return $result ? (int)$result->count : 0;
    }
    
    /**
     * Tìm một đơn ứng tuyển theo id nếu chưa bị xóa mềm.
     */
    public function findById($id) {
        $sql = "SELECT * FROM applications WHERE id = ? AND deleted = 0 LIMIT 1";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Tìm đơn ứng tuyển theo id nhưng chỉ trả về khi đơn thuộc tin của nhà tuyển dụng hiện tại.
     */
    public function findByIdForEmployer($id, $accountId) {
        $sql = "SELECT a.*, j.title AS job_title
                FROM applications a
                INNER JOIN jobs j ON j.id = a.job_id
                WHERE a.id = ? AND a.deleted = 0
                  AND j.deleted = 0
                  AND j.created_by_account_id = ?
                LIMIT 1";
        return Database::fetchOne($sql, [(int)$id, (int)$accountId]);
    }
    
    /**
     * Cập nhật trạng thái xét duyệt của đơn ứng tuyển.
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        return Database::execute($sql, [$status, $id]);
    }
    
    /**
     * Xóa mềm đơn ứng tuyển.
     */
    public function delete($id) {
        $sql = "UPDATE applications SET deleted = 1, deleted_at = NOW() WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
    
    // Kiểm tra người dùng đã ứng tuyển công việc này hay chưa
    /**
     * Kiểm tra ứng viên đã ứng tuyển vào một tin tuyển dụng hay chưa.
     */
    public function hasApplied($jobId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM applications 
                WHERE job_id = ? AND user_id = ? AND deleted = 0";
        $result = Database::fetchOne($sql, [$jobId, $userId]);
        return $result->count > 0;
    }
}
