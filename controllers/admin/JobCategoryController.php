<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Job Category.
 * - Xử lý kiểm tra request, gọi model và hiển thị/chuyển hướng cho trang quản trị.
 */
require_once __DIR__ . '/../../models/JobCategory.php';
require_once __DIR__ . '/../../helpers/CreateTree.php';
require_once __DIR__ . '/../../helpers/Generate.php';

class AdminJobCategoryController {
    private $categoryModel;
    
    /**
     * Khởi tạo model danh mục việc làm cho khu vực quản trị.
     */
    public function __construct() {
        $this->categoryModel = new JobCategory();
    }
    
    // Tuyến GET: /admin/job-category
    /**
     * Hiển thị danh sách danh mục việc làm dưới dạng cây.
     */
    public function index() {
        $categories = $this->categoryModel->find(['deleted' => false]);
        $categoryTree = CreateTree::build($categories);
        
        $title = "Quản lý danh mục công việc";
        require_once __DIR__ . '/../../views/admin/job-category/index.php';
    }
    
    // Tuyến GET: /admin/job-category/create
    /**
     * Hiển thị form tạo danh mục việc làm.
     */
    public function create() {
        $categories = $this->categoryModel->find(['deleted' => false]);
        $categoryTree = CreateTree::build($categories);
        
        $title = "Tạo danh mục mới";
        require_once __DIR__ . '/../../views/admin/job-category/create.php';
    }
    
    // Tuyến POST: /admin/job-category/create
    /**
     * Xử lý tạo danh mục mới sau khi validate tiêu đề và dữ liệu form.
     */
    public function createPost() {
        if (empty($_POST['title'])) {
            $_SESSION['flash_error'] = 'Tiêu đề không được để trống';
            header('Location: ' . BASE_PATH . '/admin/job-category/create');
            exit;
        }
        
        try {
            $slug = $this->generateSlug($_POST['title']);
            
            $data = [
                'title' => trim($_POST['title']),
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'thumbnail' => $_POST['thumbnail'] ?? null,
                'description' => trim($_POST['description'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'slug' => $slug,
                'position' => (int)($_POST['position'] ?? 0)
            ];
            
            $this->categoryModel->create($data);
            
            $_SESSION['flash_success'] = 'Tạo danh mục thành công!';
            header('Location: ' . BASE_PATH . '/admin/job-category');
            exit;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
            header('Location: ' . BASE_PATH . '/admin/job-category/create');
            exit;
        }
    }
    
    /**
     * Tạo slug từ tiêu đề danh mục và tự thêm hậu tố khi slug đã tồn tại.
     */
    private function generateSlug($title) {
        $baseSlug = Generate::slug((string)$title, 'danh-muc');

        return Generate::uniqueSlug($baseSlug, function($slug) {
            return (bool)$this->categoryModel->findOne(['slug' => $slug]);
        });
    }
    
    // Tuyến GET: /admin/job-category/edit/:id
    /**
     * Hiển thị form chỉnh sửa danh mục đang tồn tại.
     */
    public function edit($id) {
        $category = $this->categoryModel->findOne(['id' => $id, 'deleted' => false]);
        
        if (!$category) {
            $_SESSION['flash_error'] = 'Danh mục không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/job-category');
            exit;
        }
        
        $categories = $this->categoryModel->find(['deleted' => false]);
        $categoryTree = CreateTree::build($categories);
        
        $title = "Chỉnh sửa danh mục";
        require_once __DIR__ . '/../../views/admin/job-category/edit.php';
    }
    
    // Tuyến POST: /admin/job-category/edit/:id
    /**
     * Xử lý cập nhật danh mục việc làm.
     */
    public function editPost($id) {
        $category = $this->categoryModel->findOne(['id' => $id, 'deleted' => false]);
        
        if (!$category) {
            $_SESSION['flash_error'] = 'Danh mục không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/job-category');
            exit;
        }
        
        if (empty($_POST['title'])) {
            $_SESSION['flash_error'] = 'Tiêu đề không được để trống';
            header("Location: " . BASE_PATH . "/admin/job-category/edit/$id");
            exit;
        }
        
        try {
            $data = [
                'title' => trim($_POST['title']),
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'thumbnail' => $_POST['thumbnail'] ?? null,
                'description' => trim($_POST['description'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'position' => (int)($_POST['position'] ?? 0)
            ];
            
            $this->categoryModel->update($id, $data);
            
            $_SESSION['flash_success'] = 'Cập nhật danh mục thành công!';
            header("Location: " . BASE_PATH . "/admin/job-category/edit/$id");
            exit;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
            header("Location: " . BASE_PATH . "/admin/job-category/edit/$id");
            exit;
        }
    }
    
    // Tuyến POST: /admin/job-category/delete/:id
    /**
     * Fix #7: Xóa mềm danh mục việc làm — dùng model delete() thay vì SQL trực tiếp.
     */
    public function delete($id) {
        $category = $this->categoryModel->findOne(['id' => $id, 'deleted' => false]);
        if (!$category) {
            $_SESSION['flash_error'] = 'Danh mục không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/job-category');
            exit;
        }

        try {
            $this->categoryModel->delete($id);
            $_SESSION['flash_success'] = 'Xóa danh mục thành công!';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }
        
        header('Location: ' . BASE_PATH . '/admin/job-category');
        exit;
    }
}
