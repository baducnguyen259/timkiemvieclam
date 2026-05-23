<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển phía người dùng cho luồng Saved Job.
 * - Điều phối dữ liệu đầu vào, luật nghiệp vụ và phản hồi giao diện cho người dùng.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../models/SavedJob.php';

class SavedJobController {
    private $jobModel;
    private $savedJobModel;
    
    /**
     * Khởi tạo model việc làm và danh sách lưu cho các thao tác saved jobs.
     */
    public function __construct() {
        $this->jobModel = new Job();
        $this->savedJobModel = new SavedJob();
    }

    /**
     * Lấy phiên lưu việc hiện tại đã được SaveJobMiddleware chuẩn bị.
     */
    private function getCurrentSavedJob() {
        return $GLOBALS['current_saved_job'] ?? null;
    }
    
    // Tuyến GET: /saved-jobs
    /**
     * Hiển thị danh sách việc làm đã lưu của phiên hiện tại.
     */
    public function index() {
        $savedJob = $this->getCurrentSavedJob();
        if ($savedJob && !empty($savedJob->job_ids)) {
            $jobs = $this->jobModel->find([
                'id' => $savedJob->job_ids,
                'deleted' => false,
                'status' => 'active'
            ]);
        } else {
            $jobs = [];
        }
        
        $title = "Công việc đã lưu";
        require_once __DIR__ . '/../../views/client/saved-jobs/index.php';
    }
    
    // Tuyến POST: /saved-jobs/add/:jobId
    /**
     * API thêm việc làm vào danh sách lưu và trả JSON cho giao diện.
     */
    public function add($jobId) {
        header('Content-Type: application/json');
        $savedJob = $this->getCurrentSavedJob();
        
        if (!$savedJob) {
            try {
                require_once __DIR__ . '/../../helpers/Generate.php';
                require_once __DIR__ . '/../../helpers/Security.php';
                
                $sessionId = Generate::randomString(32);
                $currentUserId = isset($GLOBALS['current_user']) ? (int)$GLOBALS['current_user']->id : null;
                $newSavedJobId = $this->savedJobModel->create($sessionId, $currentUserId);
                $savedJob = $this->savedJobModel->findById($newSavedJobId);
                
                if ($savedJob) {
                    Security::setCookie('saveJobId', $savedJob->session_id, Security::persistentCookieExpiresAt());
                    $GLOBALS['current_saved_job'] = $savedJob;
                    $GLOBALS['miniSavedJobs'] = $savedJob;
                } else {
                    throw new Exception('Không thể khởi tạo phiên lưu việc');
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo phiên lưu']);
                exit;
            }
        }
        
        try {
            $this->savedJobModel->addJob($savedJob->id, $jobId);
            
            echo json_encode(['success' => true, 'message' => 'Đã lưu công việc']);
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        exit;
    }
    
    // Tuyến DELETE: /saved-jobs/remove/:jobId
    /**
     * API xóa việc làm khỏi danh sách lưu và trả JSON cho giao diện.
     */
    public function remove($jobId) {
        header('Content-Type: application/json');
        $savedJob = $this->getCurrentSavedJob();
        if (!$savedJob) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy session']);
            exit;
        }
        
        try {
            $this->savedJobModel->removeJob($savedJob->id, $jobId);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa công việc']);
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        exit;
    }
}
