<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển phía người dùng cho luồng Job.
 * - Điều phối dữ liệu đầu vào, luật nghiệp vụ và phản hồi giao diện cho người dùng.
 */
require_once __DIR__ . '/../../models/Job.php';
require_once __DIR__ . '/../../models/JobCategory.php';
require_once __DIR__ . '/../../models/SavedJob.php';
require_once __DIR__ . '/../../models/Application.php';
require_once __DIR__ . '/../../helpers/Pagination.php';
require_once __DIR__ . '/../../helpers/ProductCategory.php';
require_once __DIR__ . '/../../helpers/JobType.php';

class JobController {
    private $jobModel;
    private $categoryModel;
    private $savedJobModel;
    private $applicationModel;
    
    /**
     * Khởi tạo các model cần cho danh sách, chi tiết, danh mục và trạng thái lưu/ứng tuyển.
     */
    public function __construct() {
        $this->jobModel = new Job();
        $this->categoryModel = new JobCategory();
        $this->savedJobModel = new SavedJob();
        $this->applicationModel = new Application();
    }
    
    // Tuyến GET: /jobs
    /**
     * Hiển thị danh sách việc làm công khai, có lọc, sắp xếp và phân trang.
     */
    public function index() {
        $filters = [
            'deleted' => false,
            'status' => 'active'
        ];

        $keyword = $this->getQueryString('keyword');
        if ($keyword !== '') {
            $filters['title_search'] = $keyword;
        }

        $location = $this->getQueryString('location');
        if ($location !== '') {
            $filters['location'] = $location;
        }

        $type = $this->getQueryString('type');
        if ($type !== '') {
            $filters['type'] = $type;
        }
        
        $sort = $this->buildSortFromQuery();

        $countJobs = $this->jobModel->countDocuments($filters);
        $pagination = Pagination::calculate(6, $_GET['page'] ?? 1, $countJobs);
        
        $options = [
            'sort' => $sort,
            'limit' => $pagination['limitItem'],
            'skip' => $pagination['skipItem']
        ];
        
        $jobs = $this->jobModel->find($filters, $options);

        $baseFiltersForOptions = [
            'deleted' => false,
            'status' => 'active'
        ];
        $filterLocation = $this->buildFilterOptions('location', $baseFiltersForOptions, $location, 'Tất cả địa điểm');
        $filterType = $this->buildFilterOptions('type', $baseFiltersForOptions, $type, 'Tất cả loại hình');
        
        $title = "Danh sách công việc";
        require_once __DIR__ . '/../../views/client/jobs/index.php';
    }
    
    // Tuyến GET: /jobs/detail/:slug
    /**
     * Hiển thị chi tiết việc làm theo slug và đánh dấu trạng thái đã lưu/đã ứng tuyển nếu có.
     */
    public function detail($slug) {
        try {
            $job = $this->jobModel->findOne([
                'slug' => $slug,
                'status' => 'active',
                'deleted' => false
            ]);
            
            if (!$job) {
                $_SESSION['flash_error'] = 'Công việc không tồn tại';
                header('Location: ' . BASE_PATH . '/jobs');
                exit;
            }
            
            $category = null;
            if ($job->category_id) {
                $category = $this->categoryModel->findById($job->category_id);
            }
            
            $job->saved_job = false;
            $savedJob = $GLOBALS['current_saved_job'] ?? null;
            if ($savedJob && $this->savedJobModel->hasJob($savedJob->id, $job->id)) {
                $job->saved_job = true;
            }

            $job->applied = false;
            if (isset($GLOBALS['current_user'])) {
                $job->applied = $this->applicationModel->hasApplied($job->id, $GLOBALS['current_user']->id);
            }
            
            $title = "Chi tiết công việc";
            require_once __DIR__ . '/../../views/client/jobs/detail.php';
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
            header('Location: ' . BASE_PATH . '/jobs');
            exit;
        }
    }
    
    // Tuyến GET: /jobs/:slugCategory
    /**
     * Hiển thị việc làm theo danh mục và toàn bộ danh mục con của danh mục đó.
     */
    public function category($slugCategory) {
        $category = $this->categoryModel->findOne([
            'slug' => $slugCategory,
            'deleted' => false,
            'status' => 'active'
        ]);
        
        if (!$category) {
            $_SESSION['flash_error'] = 'Danh mục không tồn tại';
            header('Location: ' . BASE_PATH . '/jobs');
            exit;
        }
        
        $subCategories = ProductCategory::getSubCategories($category->id);
        $categoryIds = array_merge([$category->id], array_map(function($c) {
            return $c->id;
        }, $subCategories));

        $filters = [
            'deleted' => false,
            'status' => 'active',
            'category_id' => $categoryIds
        ];
        
        $keyword = $this->getQueryString('keyword');
        if ($keyword !== '') {
            $filters['title_search'] = $keyword;
        }
        
        $location = $this->getQueryString('location');
        if ($location !== '') {
            $filters['location'] = $location;
        }
        
        $type = $this->getQueryString('type');
        if ($type !== '') {
            $filters['type'] = $type;
        }
        
        $sort = $this->buildSortFromQuery();
        
        $countJobs = $this->jobModel->countDocuments($filters);
        $pagination = Pagination::calculate(6, $_GET['page'] ?? 1, $countJobs);
        
        $options = [
            'sort' => $sort,
            'limit' => $pagination['limitItem'],
            'skip' => $pagination['skipItem']
        ];
        
        $jobs = $this->jobModel->find($filters, $options);

        $baseFiltersForOptions = [
            'deleted' => false,
            'status' => 'active',
            'category_id' => $categoryIds
        ];
        $filterLocation = $this->buildFilterOptions('location', $baseFiltersForOptions, $location, 'Tất cả địa điểm');
        $filterType = $this->buildFilterOptions('type', $baseFiltersForOptions, $type, 'Tất cả loại hình');
        
        $title = $category->title;
        require_once __DIR__ . '/../../views/client/jobs/index.php';
    }

    /**
     * Lấy một giá trị query string dạng chuỗi đã trim, trả chuỗi rỗng nếu không hợp lệ.
     */
    private function getQueryString($key) {
        if (!isset($_GET[$key]) || !is_string($_GET[$key])) {
            return '';
        }
        return trim($_GET[$key]);
    }

    /**
     * Chuyển tham số sortKey/sortValue trên URL thành cấu hình sort an toàn cho model Job.
     */
    private function buildSortFromQuery() {
        $sort = ['position' => -1];
        $sortKey = $this->getQueryString('sortKey');
        $sortValue = strtolower($this->getQueryString('sortValue'));

        $allowedSortKeys = ['position', 'created_at', 'salary_min', 'salary_max'];
        if ($sortKey !== '' && in_array($sortKey, $allowedSortKeys, true)) {
            $sort = [$sortKey => $sortValue === 'asc' ? 'ASC' : 'DESC'];
        }

        return $sort;
    }

    /**
     * Tạo danh sách option cho bộ lọc động như địa điểm và loại hình công việc.
     */
    private function buildFilterOptions($field, $baseFilters, $selectedValue, $allLabel) {
        $values = $this->jobModel->getDistinctFieldValues($field, $baseFilters);

        if ($selectedValue !== '' && !in_array($selectedValue, $values, true)) {
            $values[] = $selectedValue;
            sort($values, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $options = [
            ['name' => '', 'selected' => $selectedValue === '' ? 'selected' : '', 'label' => $allLabel]
        ];

        foreach ($values as $value) {
            $options[] = [
                'name' => $value,
                'selected' => $selectedValue === $value ? 'selected' : '',
                'label' => $field === 'type' ? JobType::label($value, $value) : $value
            ];
        }

        return $options;
    }
}
