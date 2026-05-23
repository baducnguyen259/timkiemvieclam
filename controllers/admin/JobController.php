<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Job.
 * - Xử lý kiểm tra request, gọi model và hiển thị/chuyển hướng cho trang quản trị.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../models/JobCategory.php';
require_once __DIR__ . '/../../models/Account.php';
require_once __DIR__ . '/../../helpers/Pagination.php';
require_once __DIR__ . '/../../helpers/CreateTree.php';
require_once __DIR__ . '/../../helpers/JobType.php';
require_once __DIR__ . '/../../helpers/Redirect.php';

class AdminJobController {
    private $jobModel;
    private $categoryModel;
    private $accountModel;

    /**
     * Khởi tạo model việc làm, danh mục và tài khoản cho màn quản lý tin tuyển dụng.
     */
    public function __construct() {
        $this->jobModel = new Job();
        $this->categoryModel = new JobCategory();
        $this->accountModel = new Account();
    }

    /**
     * Chặn các thao tác tạo/sửa tin từ admin vì admin chỉ được đổi trạng thái hoặc xóa tin.
     */
    private function denyJobModification() {
        $_SESSION['flash_error'] = 'Quản trị viên chỉ có quyền xóa hoặc đổi trạng thái tin tuyển dụng.';
        header('Location: ' . BASE_PATH . '/admin/job');
        exit;
    }

    // Tuyến GET: /admin/job
    /**
     * Hiển thị danh sách tin tuyển dụng cho admin với bộ lọc, phân trang và thông tin người tạo.
     */
    public function index() {
        $filters = ['deleted' => false];

        // Lọc theo trạng thái
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        // Lọc theo từ khóa
        if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
            $filters['title_search'] = $_GET['keyword'];
            $keyword = $_GET['keyword'];
        }

        // Lọc theo địa điểm
        if (isset($_GET['location']) && !empty($_GET['location'])) {
            $filters['location'] = $_GET['location'];
            $location = $_GET['location'];
        }

        // Lọc theo quyền
        if (isset($GLOBALS['current_role']) && $GLOBALS['current_role']->title === 'Employer') {
            $filters['created_by_account_id'] = $GLOBALS['current_user']->id;
        }

        // Sắp xếp — Fix #5: Validate sortKey/sortValue chống SQL injection
        $sort = ['created_at' => -1, 'id' => -1];
        if (isset($_GET['sortKey']) && isset($_GET['sortValue'])) {
            $allowedSortKeys = ['id', 'title', 'company_name', 'location', 'salary_min', 'salary_max', 'status', 'position', 'created_at'];
            $sortKey = $_GET['sortKey'];
            $sortValue = strtoupper($_GET['sortValue'] ?? '');
            if (in_array($sortKey, $allowedSortKeys, true)) {
                $sort = [$sortKey => ($sortValue === 'ASC' ? 'ASC' : 'DESC')];
            }
        }

        // Phân trang
        $countJobs = $this->jobModel->countDocuments($filters);
        $pagination = Pagination::calculate(4, $_GET['page'] ?? 1, $countJobs);

        $options = [
            'sort' => $sort,
            'limit' => $pagination['limitItem'],
            'skip' => $pagination['skipItem']
        ];

        $jobs = $this->jobModel->find($filters, $options);

        // SỬA HIỆU NĂNG: Nạp toàn bộ tài khoản một lần thay vì truy vấn N+1
        $accountIds = array_unique(array_filter(array_map(function($job) {
            return $job->created_by_account_id;
        }, $jobs)));

        $accounts = [];
        if (!empty($accountIds)) {
            $accountsData = $this->accountModel->findByIds($accountIds);
            foreach ($accountsData as $account) {
                $accounts[$account->id] = $account;
            }
        }

        // Gắn thông tin tài khoản vào danh sách việc làm
        foreach ($jobs as $job) {
            if (isset($accounts[$job->created_by_account_id])) {
                $job->account_full_name = $accounts[$job->created_by_account_id]->full_name;
            }
        }

        $title = 'Quản lý công việc';
        $filterStatus = [
            ['name' => '', 'value' => '', 'selected' => !isset($_GET['status'])],
            ['name' => 'Hoạt động', 'value' => 'active', 'selected' => ($_GET['status'] ?? '') === 'active'],
            ['name' => 'Dừng hoạt động', 'value' => 'inactive', 'selected' => ($_GET['status'] ?? '') === 'inactive']
        ];

        require_once __DIR__ . '/../../views/admin/job/index.php';
    }

    // Tuyến GET: /admin/job/create
    /**
     * Chặn route tạo tin từ khu vực admin.
     */
    public function create() {
        $this->denyJobModification();
    }

    // Tuyến POST: /admin/job/create
    /**
     * Chặn request POST tạo tin từ khu vực admin.
     */
    public function createPost() {
        $this->denyJobModification();
    }

    // Tuyến GET: /admin/job/edit/:id
    /**
     * Chặn route chỉnh sửa tin từ khu vực admin.
     */
    public function edit($id) {
        $this->denyJobModification();
    }

    // Tuyến POST: /admin/job/edit/:id
    /**
     * Chặn request POST chỉnh sửa tin từ khu vực admin.
     */
    public function editPost($id) {
        $this->denyJobModification();
    }

    // Tuyến POST: /admin/job/delete/:id
    /**
     * Xóa mềm tin tuyển dụng và ghi lại admin thực hiện.
     */
    public function delete($id) {
        try {
            $this->jobModel->delete($id, $GLOBALS['current_user']->id);
            $_SESSION['flash_success'] = 'Xóa thành công!';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        header('Location: ' . BASE_PATH . '/admin/job');
        exit;
    }

    // Tuyến POST: /admin/job/change-status/:status/:id
    /**
     * Đổi trạng thái một tin tuyển dụng từ khu vực admin.
     */
    public function changeStatus($status, $id) {
        try {
            $this->jobModel->update($id, [
                'status' => $status,
                'updated_by_account_id' => $GLOBALS['current_user']->id
            ]);

            $_SESSION['flash_success'] = 'Cập nhật trạng thái thành công!';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        $referer = Redirect::back(BASE_PATH . '/admin/job');
        header('Location: ' . $referer);
        exit;
    }

    // Tuyến POST: /admin/job/change-multi
    /**
     * Xử lý đổi trạng thái hoặc xóa mềm nhiều tin tuyển dụng đã chọn.
     */
    public function changeMulti() {
        $type = $_POST['type'] ?? '';
        $ids = array_filter(explode(', ', $_POST['ids'] ?? ''));

        if (empty($ids)) {
            $_SESSION['flash_error'] = 'Không có mục nào được chọn';
            header('Location: ' . BASE_PATH . '/admin/job');
            exit;
        }

        try {
            switch ($type) {
                case 'active':
                    $this->jobModel->updateMany($ids, ['status' => 'active']);
                    $_SESSION['flash_success'] = 'Cập nhật trạng thái cho ' . count($ids) . ' tin tuyển dụng thành công!';
                    break;

                case 'inactive':
                    $this->jobModel->updateMany($ids, ['status' => 'inactive']);
                    $_SESSION['flash_success'] = 'Cập nhật trạng thái cho ' . count($ids) . ' tin tuyển dụng thành công!';
                    break;

                case 'delete-all':
                    $this->jobModel->updateMany($ids, [
                        'deleted' => true,
                        'deleted_by_account_id' => $GLOBALS['current_user']->id
                    ]);
                    $_SESSION['flash_success'] = 'Xóa thành công ' . count($ids) . ' tin tuyển dụng!';
                    break;

                default:
                    $_SESSION['flash_error'] = 'Chỉ hỗ trợ đổi trạng thái hoặc xóa tin tuyển dụng.';
                    break;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        $referer = Redirect::back(BASE_PATH . '/admin/job');
        header('Location: ' . $referer);
        exit;
    }

    // Tuyến GET: /admin/job/detail/:id
    /**
     * Hiển thị chi tiết tin tuyển dụng cho admin, kèm danh mục và tài khoản tạo tin.
     */
    public function detail($id) {
        $job = $this->jobModel->findOne(['id' => $id, 'deleted' => false]);

        if (!$job) {
            $_SESSION['flash_error'] = 'Công việc không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/job');
            exit;
        }

        // Lấy thông tin danh mục
        $category = null;
        if ($job->category_id) {
            $category = $this->categoryModel->findOne(['id' => $job->category_id, 'deleted' => false]);
        }

        // Lấy thông tin tài khoản tạo bài
        $creator = null;
        if ($job->created_by_account_id) {
            $creator = $this->accountModel->findById($job->created_by_account_id);
        }
        $job->creator = $creator;

        $title = 'Chi tiết: ' . $job->title;
        require_once __DIR__ . '/../../views/admin/job/detail.php';
    }

}
