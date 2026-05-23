<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển phía người dùng cho luồng Application.
 * - Điều phối dữ liệu đầu vào, luật nghiệp vụ và phản hồi giao diện cho người dùng.
 */
require_once __DIR__ . '/../../models/Application.php';
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../helpers/FileUpload.php';

class ApplicationController {
    private $applicationModel;
    private $jobModel;
    
    /**
     * Khởi tạo model đơn ứng tuyển và việc làm cho luồng ứng tuyển của ứng viên.
     */
    public function __construct() {
        $this->applicationModel = new Application();
        $this->jobModel = new Job();
    }
    
    // Tuyến GET: /applications
    /**
     * Hiển thị danh sách đơn ứng tuyển của người dùng đang đăng nhập.
     */
    public function index() {
        $userId = $GLOBALS['current_user']->id;
        $applications = $this->applicationModel->findByUserId($userId);
        
        $title = "Đơn ứng tuyển của tôi";
        require_once __DIR__ . '/../../views/client/applications/index.php';
    }
    
    // Tuyến GET: /applications/create/:jobId
    /**
     * Hiển thị form ứng tuyển cho một việc làm hợp lệ và chặn ứng tuyển trùng.
     */
    public function create($jobId) {
        $job = $this->jobModel->findOne(['id' => $jobId, 'deleted' => false, 'status' => 'active']);
        
        if (!$job) {
            $_SESSION['flash_error'] = 'Công việc không tồn tại';
            header('Location: ' . BASE_PATH . '/jobs');
            exit;
        }
        
        // Kiểm tra đã ứng tuyển chưa
        $userId = $GLOBALS['current_user']->id;
        if ($this->applicationModel->hasApplied($jobId, $userId)) {
            $_SESSION['flash_error'] = 'Bạn đã ứng tuyển công việc này rồi';
            header('Location: ' . BASE_PATH . '/jobs/detail/' . $job->slug);
            exit;
        }
        
        $title = "Ứng tuyển: " . $job->title;
        require_once __DIR__ . '/../../views/client/applications/create.php';
    }
    
    // Tuyến POST: /applications/create/:jobId
    /**
     * Xử lý gửi đơn ứng tuyển, kiểm tra dữ liệu, nhận CV dạng file hoặc link và lưu đơn.
     */
    public function createPost($jobId) {
        $job = $this->jobModel->findOne(['id' => $jobId, 'deleted' => false, 'status' => 'active']);
        
        if (!$job) {
            $_SESSION['flash_error'] = 'Công việc không tồn tại';
            header('Location: ' . BASE_PATH . '/jobs');
            exit;
        }
        
        $userId = $GLOBALS['current_user']->id;
        
        // Kiểm tra đã ứng tuyển chưa
        if ($this->applicationModel->hasApplied($jobId, $userId)) {
            $_SESSION['flash_error'] = 'Bạn đã ứng tuyển công việc này rồi';
            header('Location: ' . BASE_PATH . '/jobs/detail/' . $job->slug);
            exit;
        }
        
        // Kiểm tra dữ liệu
        $errors = [];
        
        if (empty($_POST['fullName'])) {
            $errors[] = 'Họ tên không được để trống';
        }
        
        if (empty($_POST['email'])) {
            $errors[] = 'Email không được để trống';
        }
        
        if (empty($_POST['phone'])) {
            $errors[] = 'Số điện thoại không được để trống';
        }

        $cvLink = trim($_POST['cvLink'] ?? '');
        if ($cvLink !== '') {
            $cvLink = filter_var($cvLink, FILTER_SANITIZE_URL);
            if (!filter_var($cvLink, FILTER_VALIDATE_URL)) {
                $errors[] = 'Liên kết CV không hợp lệ';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            header('Location: ' . $this->resolveApplyBackUrl($job));
            exit;
        }
        
        try {
            // Tải tệp CV nếu có
            $cvFile = null;
            if (isset($_FILES['cvFile']) && $_FILES['cvFile']['error'] === UPLOAD_ERR_OK) {
                $uploader = new FileUpload(['pdf', 'doc', 'docx']);
                $cvFile = $uploader->upload($_FILES['cvFile'], 'public/uploads/cv/');
            } elseif (isset($_FILES['cvFile']) && $_FILES['cvFile']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception('Tải CV lên thất bại');
            }

            if ($cvFile === null && $cvLink === '') {
                $_SESSION['flash_error'] = 'Vui lòng dán liên kết CV hoặc tải tệp CV lên';
                header('Location: ' . $this->resolveApplyBackUrl($job));
                exit;
            }
            
            $data = [
                'job_id' => $jobId,
                'user_id' => $userId,
                'full_name' => trim($_POST['fullName']),
                'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                'phone' => trim($_POST['phone']),
                'cv_file' => $cvFile,
                'cv_link' => $cvLink !== '' ? $cvLink : null,
                'cover_letter' => trim($_POST['coverLetter'] ?? ''),
                'status' => 'pending'
            ];
            
            $this->applicationModel->create($data);
            
            $_SESSION['flash_success'] = 'Ứng tuyển thành công!';
            header('Location: ' . BASE_PATH . '/applications');
            exit;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra, vui lòng thử lại';
            header('Location: ' . $this->resolveApplyBackUrl($job));
            exit;
        }
    }

    /**
     * Chọn URL quay lại phù hợp khi form ứng tuyển lỗi, ưu tiên trang chi tiết nếu người dùng đến từ đó.
     */
    private function resolveApplyBackUrl($job) {
        $detailUrl = BASE_PATH . '/jobs/detail/' . $job->slug;
        $createUrl = BASE_PATH . '/applications/create/' . $job->id;
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (is_string($referer) && strpos($referer, '/jobs/detail/') !== false) {
            return $detailUrl;
        }

        return $createUrl;
    }
}
