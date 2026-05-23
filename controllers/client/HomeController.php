<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển phía người dùng cho luồng Home.
 * - Điều phối dữ liệu đầu vào, luật nghiệp vụ và phản hồi giao diện cho người dùng.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../helpers/Pagination.php';
require_once __DIR__ . '/../../helpers/JobType.php';

class HomeController {
    private $jobModel;
    
    /**
     * Khởi tạo model việc làm để trang chủ lấy dữ liệu hiển thị.
     */
    public function __construct() {
        $this->jobModel = new Job();
    }
    
    // Tuyến GET: /
    /**
     * Hiển thị trang chủ với danh sách việc nổi bật và việc mới nhất.
     */
    public function index() {
        // Việc nổi bật
        $jobsFeatured = $this->jobModel->find([
            'featured' => '1',
            'deleted' => false,
            'status' => 'active'
        ], [
            'limit' => 6
        ]);
        
        // Việc mới
        $jobsNewFilters = [
            'deleted' => false,
            'status' => 'active'
        ];
        $countJobsNew = $this->jobModel->countDocuments($jobsNewFilters);
        $jobsNewPagination = Pagination::calculate(6, $_GET['page'] ?? 1, $countJobsNew);
        $jobsNew = $this->jobModel->find($jobsNewFilters, [
            'sort' => ['created_at' => -1],
            'limit' => $jobsNewPagination['limitItem'],
            'skip' => $jobsNewPagination['skipItem']
        ]);
        
        $title = "Trang Chủ";
        require_once __DIR__ . '/../../views/client/home/index.php';
    }
}
