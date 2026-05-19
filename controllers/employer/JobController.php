<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển cổng nhà tuyển dụng cho tính năng Job.
 * - Xử lý thao tác nhà tuyển dụng và trả về giao diện bảng điều khiển, việc làm, ứng tuyển tương ứng.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../models/JobCategory.php';
require_once __DIR__ . '/../../helpers/Pagination.php';
require_once __DIR__ . '/../../helpers/CreateTree.php';
require_once __DIR__ . '/../../helpers/FileUpload.php';
require_once __DIR__ . '/../../helpers/Generate.php';
require_once __DIR__ . '/../../helpers/JobType.php';
require_once __DIR__ . '/../../helpers/Redirect.php';

class EmployerJobController {
    private $jobModel;
    private $categoryModel;

    /**
     * Khởi tạo model việc làm và danh mục cho các thao tác tin tuyển dụng của nhà tuyển dụng.
     */
    public function __construct() {
        $this->jobModel = new Job();
        $this->categoryModel = new JobCategory();
    }

    // Tuyến GET: /employer/job
    /**
     * Hiển thị danh sách tin tuyển dụng thuộc tài khoản nhà tuyển dụng hiện tại.
     */
    public function index() {
        $accountId = $GLOBALS['current_user']->id;
        $filters = [
            'deleted' => false,
            'created_by_account_id' => $accountId
        ];

        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $filters['status'] = $_GET['status'];
        }

        if (isset($_GET['keyword']) && $_GET['keyword'] !== '') {
            $filters['title_search'] = $_GET['keyword'];
            $keyword = $_GET['keyword'];
        }

        if (isset($_GET['location']) && $_GET['location'] !== '') {
            $filters['location'] = $_GET['location'];
            $location = $_GET['location'];
        }

        $sort = ['created_at' => -1];
        if (isset($_GET['sortKey']) && isset($_GET['sortValue']) && $_GET['sortKey'] !== '') {
            $sort = [$_GET['sortKey'] => $_GET['sortValue']];
        }

        $countJobs = $this->jobModel->countDocuments($filters);
        $pagination = Pagination::calculate(8, $_GET['page'] ?? 1, $countJobs);

        $options = [
            'sort' => $sort,
            'limit' => $pagination['limitItem'],
            'skip' => $pagination['skipItem']
        ];

        $jobs = $this->jobModel->find($filters, $options);

        $title = 'Quản lý tin tuyển dụng';
        $filterStatus = [
            ['name' => '', 'value' => '', 'selected' => !isset($_GET['status']) || $_GET['status'] === ''],
            ['name' => 'Hoạt động', 'value' => 'active', 'selected' => ($_GET['status'] ?? '') === 'active'],
            ['name' => 'Không hoạt động', 'value' => 'inactive', 'selected' => ($_GET['status'] ?? '') === 'inactive']
        ];

        require_once __DIR__ . '/../../views/employer/job/index.php';
    }

    // Tuyến GET: /employer/job/create
    /**
     * Hiển thị form đăng tin tuyển dụng mới.
     */
    public function create() {
        $categories = $this->categoryModel->find(['deleted' => false]);
        $categoryTree = CreateTree::build($categories);

        $title = 'Đăng tin tuyển dụng mới';
        require_once __DIR__ . '/../../views/employer/job/create.php';
    }

    // Tuyến POST: /employer/job/create
    /**
     * Xử lý đăng tin tuyển dụng, validate dữ liệu, upload logo và lưu kỹ năng.
     */
    public function createPost() {
        $skills = array_filter(array_map('trim', explode(',', $_POST['skill'] ?? '')));
        $title = trim($_POST['title'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $addressDetail = trim($_POST['address_detail'] ?? '');
        $candidateRequirements = trim($_POST['candidate_requirements'] ?? '');
        $benefits = trim($_POST['benefits'] ?? '');
        $applicationDeadline = $_POST['application_deadline'] ?? '';

        if ($title === '') {
            $_SESSION['flash_error'] = 'Tiêu đề không được để trống';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }

        if ($companyName === '') {
            $_SESSION['flash_error'] = 'Tên công ty không được để trống';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }

        if ($location === '') {
            $_SESSION['flash_error'] = 'Địa điểm không được để trống';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }

        if ($addressDetail === '') {
            $_SESSION['flash_error'] = 'Địa chỉ chi tiết không được để trống';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }

        if (!$this->isValidDate($applicationDeadline)) {
            $_SESSION['flash_error'] = 'Hạn nộp hồ sơ không hợp lệ';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }

        if (count($skills) > 5) {
            $_SESSION['flash_error'] = 'Chỉ được tối đa 5 kỹ năng';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }

        foreach ($skills as $skill) {
            if (strlen($skill) > 20) {
                $_SESSION['flash_error'] = 'Mỗi kỹ năng tối đa 20 ký tự';
                header('Location: ' . BASE_PATH . '/employer/job/create');
                exit;
            }
        }

        try {
            $slug = $this->generateSlug($title);
            $position = $_POST['position'] ?? null;
            if (!$position) {
                $position = $this->jobModel->countDocuments(['deleted' => false]) + 1;
            }
            $salaryValue = isset($_POST['salary']) && trim((string)$_POST['salary']) !== ''
                ? max(0, (float)$_POST['salary'])
                : 0;

            $companyLogo = null;
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                if (!$this->isValidLogoExtension($_FILES['company_logo']['name'] ?? '')) {
                    throw new Exception('Logo phải có định dạng JPG, JPEG hoặc PNG');
                }
                $uploader = new FileUpload(['jpg', 'jpeg', 'png']);
                $uploadedLogoPath = $uploader->upload($_FILES['company_logo'], 'public/uploads/company-logo/');
                $companyLogo = str_replace('public/', '', $uploadedLogoPath);
            } elseif (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception('Tải logo lên thất bại');
            }

            $data = [
                'title' => $title,
                'company_name' => $companyName,
                'company_logo' => $companyLogo,
                'description' => $_POST['description'] ?? null,
                'candidate_requirements' => $candidateRequirements !== '' ? $candidateRequirements : null,
                'benefits' => $benefits !== '' ? $benefits : null,
                'location' => $location,
                'address_detail' => $addressDetail,
                'category_id' => $_POST['category_id'] ?? null,
                'salary_min' => $salaryValue,
                'salary_max' => $salaryValue,
                'type' => $_POST['type'] ?? null,
                'experience' => $_POST['experience'] ?? null,
                'application_deadline' => $applicationDeadline,
                'status' => $_POST['status'] ?? 'active',
                'slug' => $slug,
                'position' => (int)$position,
                'skills' => $skills,
                'created_by_account_id' => $GLOBALS['current_user']->id
            ];

            $this->jobModel->create($data);

            $_SESSION['flash_success'] = 'Đăng tin tuyển dụng thành công!';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi đăng tin';
            header('Location: ' . BASE_PATH . '/employer/job/create');
            exit;
        }
    }

    // Tuyến GET: /employer/job/edit/:id
    /**
     * Hiển thị form chỉnh sửa tin tuyển dụng nếu tin thuộc tài khoản hiện tại.
     */
    public function edit($id) {
        $job = $this->jobModel->findOne(['id' => $id, 'deleted' => false]);

        if (!$job || (int)$job->created_by_account_id !== (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Bạn không có quyền chỉnh sửa tin này';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        }

        $categories = $this->categoryModel->find(['deleted' => false]);
        $categoryTree = CreateTree::build($categories);

        $title = 'Cập nhật tin tuyển dụng';
        require_once __DIR__ . '/../../views/employer/job/edit.php';
    }

    // Tuyến POST: /employer/job/edit/:id
    /**
     * Xử lý cập nhật tin tuyển dụng, kiểm tra quyền sở hữu và đồng bộ logo/kỹ năng.
     */
    public function editPost($id) {
        $job = $this->jobModel->findOne(['id' => $id, 'deleted' => false]);
        if (!$job || (int)$job->created_by_account_id !== (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Bạn không có quyền chỉnh sửa tin này';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        }

        $skills = array_filter(array_map('trim', explode(',', $_POST['skill'] ?? '')));
        $title = trim($_POST['title'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $addressDetail = trim($_POST['address_detail'] ?? '');
        $candidateRequirements = trim($_POST['candidate_requirements'] ?? '');
        $benefits = trim($_POST['benefits'] ?? '');
        $applicationDeadline = $_POST['application_deadline'] ?? '';

        if ($title === '') {
            $_SESSION['flash_error'] = 'Tiêu đề không được để trống';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }

        if ($companyName === '') {
            $_SESSION['flash_error'] = 'Tên công ty không được để trống';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }

        if ($location === '') {
            $_SESSION['flash_error'] = 'Địa điểm không được để trống';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }

        if ($addressDetail === '') {
            $_SESSION['flash_error'] = 'Địa chỉ chi tiết không được để trống';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }

        if (!$this->isValidDate($applicationDeadline)) {
            $_SESSION['flash_error'] = 'Hạn nộp hồ sơ không hợp lệ';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }

        if (count($skills) > 5) {
            $_SESSION['flash_error'] = 'Chỉ được tối đa 5 kỹ năng';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }

        foreach ($skills as $skill) {
            if (strlen($skill) > 20) {
                $_SESSION['flash_error'] = 'Mỗi kỹ năng tối đa 20 ký tự';
                header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
                exit;
            }
        }

        try {
            $companyLogo = $job->company_logo ?? null;
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                if (!$this->isValidLogoExtension($_FILES['company_logo']['name'] ?? '')) {
                    throw new Exception('Logo phải có định dạng JPG, JPEG hoặc PNG');
                }
                $uploader = new FileUpload(['jpg', 'jpeg', 'png']);
                $uploadedLogoPath = $uploader->upload($_FILES['company_logo'], 'public/uploads/company-logo/');
                $companyLogo = str_replace('public/', '', $uploadedLogoPath);
            } elseif (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception('Tải logo lên thất bại');
            }
            $salaryValue = isset($_POST['salary']) && trim((string)$_POST['salary']) !== ''
                ? max(0, (float)$_POST['salary'])
                : 0;

            $data = [
                'title' => $title,
                'company_name' => $companyName,
                'company_logo' => $companyLogo,
                'description' => $_POST['description'] ?? null,
                'candidate_requirements' => $candidateRequirements !== '' ? $candidateRequirements : null,
                'benefits' => $benefits !== '' ? $benefits : null,
                'location' => $location,
                'address_detail' => $addressDetail,
                'category_id' => $_POST['category_id'] ?? null,
                'salary_min' => $salaryValue,
                'salary_max' => $salaryValue,
                'type' => $_POST['type'] ?? null,
                'experience' => $_POST['experience'] ?? null,
                'application_deadline' => $applicationDeadline,
                'status' => $_POST['status'] ?? 'active',
                'position' => (int)($_POST['position'] ?? 0),
                'skills' => $skills,
                'updated_by_account_id' => $GLOBALS['current_user']->id
            ];

            $this->jobModel->update($id, $data);

            $_SESSION['flash_success'] = 'Cập nhật tin tuyển dụng thành công!';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra khi cập nhật tin';
            header('Location: ' . BASE_PATH . "/employer/job/edit/$id");
            exit;
        }
    }

    // Tuyến POST: /employer/job/delete/:id
    /**
     * Xóa mềm tin tuyển dụng nếu tin thuộc nhà tuyển dụng hiện tại.
     */
    public function delete($id) {
        $job = $this->jobModel->findOne(['id' => $id, 'deleted' => false]);
        if (!$job || (int)$job->created_by_account_id !== (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Bạn không có quyền xóa tin này';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        }

        try {
            $this->jobModel->delete($id, $GLOBALS['current_user']->id);
            $_SESSION['flash_success'] = 'Xóa tin tuyển dụng thành công!';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        header('Location: ' . BASE_PATH . '/employer/job');
        exit;
    }

    // Tuyến POST: /employer/job/change-status/:status/:id
    /**
     * Đổi trạng thái active/inactive của tin tuyển dụng thuộc nhà tuyển dụng hiện tại.
     */
    public function changeStatus($status, $id) {
        if (!in_array($status, ['active', 'inactive'], true)) {
            $_SESSION['flash_error'] = 'Trạng thái không hợp lệ';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        }

        $job = $this->jobModel->findOne(['id' => $id, 'deleted' => false]);
        if (!$job || (int)$job->created_by_account_id !== (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Bạn không có quyền cập nhật tin này';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        }

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

        $referer = Redirect::back(BASE_PATH . '/employer/job');
        header('Location: ' . $referer);
        exit;
    }

    // Tuyến GET: /employer/job/detail/:id
    /**
     * Hiển thị chi tiết tin tuyển dụng nếu người xem là chủ tin.
     */
    public function detail($id) {
        $job = $this->jobModel->findOne(['id' => $id, 'deleted' => false]);
        if (!$job || (int)$job->created_by_account_id !== (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Bạn không có quyền xem tin này';
            header('Location: ' . BASE_PATH . '/employer/job');
            exit;
        }

        $category = null;
        if ($job->category_id) {
            $category = $this->categoryModel->findOne(['id' => $job->category_id, 'deleted' => false]);
        }

        $title = 'Chi tiết tin: ' . $job->title;
        require_once __DIR__ . '/../../views/employer/job/detail.php';
    }

    /**
     * Kiểm tra chuỗi ngày có đúng định dạng Y-m-d hay không.
     */
    private function isValidDate($date) {
        if (!is_string($date) || trim($date) === '') {
            return false;
        }

        $dateObject = DateTime::createFromFormat('Y-m-d', $date);
        return $dateObject && $dateObject->format('Y-m-d') === $date;
    }

    /**
     * Kiểm tra phần mở rộng logo có nằm trong nhóm ảnh được phép.
     */
    private function isValidLogoExtension($fileName) {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png'], true);
    }

    /**
     * Tạo slug từ tiêu đề tin tuyển dụng và tự thêm hậu tố khi slug đã tồn tại.
     */
    private function generateSlug($title) {
        $baseSlug = Generate::slug((string)$title, 'tin-tuyen-dung');

        return Generate::uniqueSlug($baseSlug, function($slug) {
            return (bool)$this->jobModel->findOne(['slug' => $slug]);
        });
    }
}
