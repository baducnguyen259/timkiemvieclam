<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Dashboard.
 * - Xử lý kiểm tra request, gọi model và hiển thị/chuyển hướng cho trang quản trị.
 */
require_once __DIR__ . '/../../models/Job.php';

class AdminDashboardController {
    
    /**
     * Hiển thị dashboard quản trị với các chỉ số tổng quan về việc làm.
     */
    public function index() {
        $jobModel = new Job();
        
        // Fix #23: Xóa khởi tạo User và Application vì không sử dụng
        // Thống kê
        $totalJobs = $jobModel->countDocuments(['deleted' => false]);
        $activeJobs = $jobModel->countDocuments(['deleted' => false, 'status' => 'active']);
        
        $title = "Tổng quan";
        require_once __DIR__ . '/../../views/admin/dashboard/index.php';
    }
}
