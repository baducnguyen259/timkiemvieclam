<?php
/**
 * Giải thích mã:
 * - Model truy cập dữ liệu cho thực thể việc đã lưu.
 * - Chứa các truy vấn SQL và xử lý dữ liệu trả về cho lớp nghiệp vụ.
 */
require_once __DIR__ . '/../config/database.php';

class SavedJob {
    
    /**
     * Tạo phiên lưu việc mới cho khách vãng lai hoặc người dùng đã đăng nhập.
     */
    public function create($sessionId, $userId = null) {
        $sql = "INSERT INTO saved_jobs (user_id, session_id) VALUES (?, ?)";
        Database::execute($sql, [$userId, $sessionId]);
        return Database::lastInsertId();
    }
    
    /**
     * Tìm phiên lưu việc theo id và nạp thêm danh sách job_id đã lưu.
     */
    public function findById($id) {
        $sql = "SELECT * FROM saved_jobs WHERE id = ? LIMIT 1";
        $savedJob = Database::fetchOne($sql, [$id]);
        
        if ($savedJob) {
            $savedJob->job_ids = $this->getJobIds($id);
            $savedJob->total_saved_jobs = count($savedJob->job_ids);
        }
        
        return $savedJob;
    }
    
    /**
     * Tìm phiên lưu việc của một người dùng và nạp tổng số việc đã lưu.
     */
    public function findByUserId($userId) {
        $sql = "SELECT * FROM saved_jobs WHERE user_id = ? LIMIT 1";
        $savedJob = Database::fetchOne($sql, [$userId]);
        
        if ($savedJob) {
            $savedJob->job_ids = $this->getJobIds($savedJob->id);
            $savedJob->total_saved_jobs = count($savedJob->job_ids);
        }
        
        return $savedJob;
    }
    
    /**
     * Tìm phiên lưu việc theo session_id trong cookie.
     */
    public function findBySessionId($sessionId) {
        $sql = "SELECT * FROM saved_jobs WHERE session_id = ? LIMIT 1";
        $savedJob = Database::fetchOne($sql, [$sessionId]);
        
        if ($savedJob) {
            $savedJob->job_ids = $this->getJobIds($savedJob->id);
            $savedJob->total_saved_jobs = count($savedJob->job_ids);
        }
        
        return $savedJob;
    }
    
    /**
     * Lấy danh sách id việc làm trong một phiên lưu việc.
     */
    private function getJobIds($savedJobId) {
        $sql = "SELECT job_id FROM saved_job_items WHERE saved_job_id = ?";
        $items = Database::fetchAll($sql, [$savedJobId]);
        return array_map(function($item) { return $item->job_id; }, $items);
    }
    
    /**
     * Thêm việc làm vào danh sách lưu, bỏ qua nếu cặp saved_job/job đã tồn tại.
     */
    public function addJob($savedJobId, $jobId) {
        $sql = "INSERT IGNORE INTO saved_job_items (saved_job_id, job_id) VALUES (?, ?)";
        return Database::execute($sql, [$savedJobId, $jobId]);
    }
    
    /**
     * Xóa một việc làm khỏi danh sách lưu.
     */
    public function removeJob($savedJobId, $jobId) {
        $sql = "DELETE FROM saved_job_items WHERE saved_job_id = ? AND job_id = ?";
        return Database::execute($sql, [$savedJobId, $jobId]);
    }
    
    /**
     * Fix #13: Kiểm tra một việc làm đã nằm trong danh sách lưu hay chưa.
     */
    public function hasJob($savedJobId, $jobId) {
        $sql = "SELECT COUNT(*) as count FROM saved_job_items 
                WHERE saved_job_id = ? AND job_id = ?";
        $result = Database::fetchOne($sql, [$savedJobId, $jobId]);
        return $result && $result->count > 0;
    }
    
    /**
     * Gắn phiên lưu việc ẩn danh với user sau khi người dùng đăng nhập.
     */
    public function updateUserId($sessionId, $userId) {
        $sql = "UPDATE saved_jobs SET user_id = ? WHERE session_id = ?";
        return Database::execute($sql, [$userId, $sessionId]);
    }
    
    /**
     * Gộp danh sách lưu ẩn danh vào danh sách lưu của người dùng đã đăng nhập.
     */
    public function mergeJobs($fromSavedJobId, $toSavedJobId) {
        $sql = "INSERT IGNORE INTO saved_job_items (saved_job_id, job_id)
                SELECT ?, job_id FROM saved_job_items WHERE saved_job_id = ?";
        return Database::execute($sql, [$toSavedJobId, $fromSavedJobId]);
    }
}
