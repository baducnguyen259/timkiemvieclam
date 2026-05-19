<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển cổng nhà tuyển dụng cho tính năng Dashboard.
 * - Xử lý thao tác nhà tuyển dụng và trả về giao diện bảng điều khiển, việc làm, ứng tuyển tương ứng.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../config/database.php';

class EmployerDashboardController {
    /**
     * Hiển thị dashboard nhà tuyển dụng với số lượng tin và đơn ứng tuyển theo tài khoản hiện tại.
     */
    public function index() {
        $jobModel = new Job();
        $accountId = $GLOBALS['current_user']->id;

        $totalJobs = $jobModel->countDocuments([
            'deleted' => false,
            'created_by_account_id' => $accountId
        ]);

        $activeJobs = $jobModel->countDocuments([
            'deleted' => false,
            'status' => 'active',
            'created_by_account_id' => $accountId
        ]);

        $inactiveJobs = $jobModel->countDocuments([
            'deleted' => false,
            'status' => 'inactive',
            'created_by_account_id' => $accountId
        ]);

        $totalApplicationsRow = Database::fetchOne(
            "SELECT COUNT(*) AS count
             FROM applications a
             INNER JOIN jobs j ON j.id = a.job_id
             WHERE j.created_by_account_id = ? AND j.deleted = 0 AND a.deleted = 0",
            [$accountId]
        );

        $pendingApplicationsRow = Database::fetchOne(
            "SELECT COUNT(*) AS count
             FROM applications a
             INNER JOIN jobs j ON j.id = a.job_id
             WHERE j.created_by_account_id = ? AND j.deleted = 0 AND a.deleted = 0 AND a.status = 'pending'",
            [$accountId]
        );

        $totalApplications = $totalApplicationsRow ? (int)$totalApplicationsRow->count : 0;
        $pendingApplications = $pendingApplicationsRow ? (int)$pendingApplicationsRow->count : 0;

        $title = 'Bảng điều khiển nhà tuyển dụng';
        require_once __DIR__ . '/../../views/employer/dashboard/index.php';
    }
}
