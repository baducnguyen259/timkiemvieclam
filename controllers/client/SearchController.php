<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển phía người dùng cho luồng Search.
 * - Điều phối dữ liệu đầu vào, luật nghiệp vụ và phản hồi giao diện cho người dùng.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../helpers/JobType.php';

class SearchController {
    private $jobModel;
    
    /**
     * Khởi tạo model việc làm phục vụ tìm kiếm.
     */
    public function __construct() {
        $this->jobModel = new Job();
    }
    
    // Tuyến GET: /search
    /**
     * Xử lý trang kết quả tìm kiếm theo từ khóa trên query string.
     */
    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        
        if (empty($keyword)) {
            header('Location: ' . BASE_PATH . '/jobs');
            exit;
        }
        
        $filters = [
            'deleted' => false,
            'status' => 'active',
            'title_search' => $keyword
        ];
        
        $jobs = $this->jobModel->find($filters, ['limit' => 20]);
        
        $title = "Kết quả tìm kiếm: " . htmlspecialchars($keyword);
        require_once __DIR__ . '/../../views/client/search/index.php';
    }
}
