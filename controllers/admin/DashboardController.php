<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Dashboard.
 * - Xử lý kiểm tra request, gọi model và hiển thị/chuyển hướng cho trang quản trị.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Application.php';

class AdminDashboardController {
    
    /**
     * Hiển thị dashboard quản trị với các chỉ số tổng quan về việc làm.
     */
    public function index() {
        $jobModel = new Job();
        $userModel = new User();
        $applicationModel = new Application();
        
        // Thống kê
        $totalJobs = $jobModel->countDocuments(['deleted' => false]);
        $activeJobs = $jobModel->countDocuments(['deleted' => false, 'status' => 'active']);
        
        $title = "Tổng quan";
        require_once __DIR__ . '/../../views/admin/dashboard/index.php';
    }
}
