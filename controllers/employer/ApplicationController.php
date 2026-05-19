<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển cổng nhà tuyển dụng cho tính năng Application.
 * - Xử lý thao tác nhà tuyển dụng và trả về giao diện bảng điều khiển, việc làm, ứng tuyển tương ứng.
 */
require_once __DIR__ . '/../../models/Application.php';
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../helpers/Pagination.php';
require_once __DIR__ . '/../../helpers/Redirect.php';

class EmployerApplicationController {
    private $applicationModel;
    private $jobModel;
    private $allowedStatuses = ['pending', 'reviewed', 'accepted', 'rejected'];

    /**
     * Khởi tạo model đơn ứng tuyển và việc làm cho nhà tuyển dụng.
     */
    public function __construct() {
        $this->applicationModel = new Application();
        $this->jobModel = new Job();
    }

    // Tuyến GET: /employer/application
    /**
     * Hiển thị danh sách đơn ứng tuyển thuộc các tin của nhà tuyển dụng hiện tại.
     */
    public function index() {
        $accountId = (int)$GLOBALS['current_user']->id;
        $filters = [];

        $status = trim((string)($_GET['status'] ?? ''));
        if ($status !== '' && in_array($status, $this->allowedStatuses, true)) {
            $filters['status'] = $status;
        }

        $jobId = trim((string)($_GET['job_id'] ?? ''));
        if ($jobId !== '' && ctype_digit($jobId)) {
            $filters['job_id'] = (int)$jobId;
        }

        $keyword = trim((string)($_GET['keyword'] ?? ''));
        if ($keyword !== '') {
            $filters['keyword'] = $keyword;
        }

        $totalApplications = $this->applicationModel->countByEmployerId($accountId, $filters);
        $pagination = Pagination::calculate(10, $_GET['page'] ?? 1, $totalApplications);

        $applications = $this->applicationModel->findByEmployerId($accountId, $filters, [
            'limit' => $pagination['limitItem'],
            'skip' => $pagination['skipItem']
        ]);

        $jobs = $this->jobModel->find(
            [
                'deleted' => false,
                'created_by_account_id' => $accountId
            ],
            [
                'sort' => ['created_at' => -1]
            ]
        );

        $filterStatus = [
            ['name' => 'Tất cả trạng thái', 'value' => '', 'selected' => $status === ''],
            ['name' => 'Chờ xử lý', 'value' => 'pending', 'selected' => $status === 'pending'],
            ['name' => 'Đã xem', 'value' => 'reviewed', 'selected' => $status === 'reviewed'],
            ['name' => 'Chấp nhận', 'value' => 'accepted', 'selected' => $status === 'accepted'],
            ['name' => 'Từ chối', 'value' => 'rejected', 'selected' => $status === 'rejected']
        ];

        $title = 'Xét duyệt ứng tuyển';
        require_once __DIR__ . '/../../views/employer/application/index.php';
    }

    // Tuyến POST: /employer/application/change-status/:status/:id
    /**
     * Đổi trạng thái xét duyệt của một đơn ứng tuyển nếu đơn thuộc nhà tuyển dụng hiện tại.
     */
    public function changeStatus($status, $id) {
        if (!in_array($status, $this->allowedStatuses, true)) {
            $_SESSION['flash_error'] = 'Trạng thái không hợp lệ';
            header('Location: ' . BASE_PATH . '/employer/application');
            exit;
        }

        $accountId = (int)$GLOBALS['current_user']->id;
        $application = $this->applicationModel->findByIdForEmployer((int)$id, $accountId);

        if (!$application) {
            $_SESSION['flash_error'] = 'Bạn không có quyền cập nhật đơn ứng tuyển này';
            header('Location: ' . BASE_PATH . '/employer/application');
            exit;
        }

        try {
            $this->applicationModel->updateStatus((int)$id, $status);

            $statusLabels = [
                'pending' => 'chờ xử lý',
                'reviewed' => 'đã xem',
                'accepted' => 'chấp nhận',
                'rejected' => 'từ chối'
            ];
            $_SESSION['flash_success'] = 'Đã cập nhật trạng thái đơn sang "' . ($statusLabels[$status] ?? $status) . '"';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi cập nhật trạng thái đơn';
        }

        $referer = Redirect::back(BASE_PATH . '/employer/application');
        header('Location: ' . $referer);
        exit;
    }
}
