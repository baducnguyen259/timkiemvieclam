<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Role.
 * - Xử lý kiểm tra request, gọi model và hiển thị/chuyển hướng cho trang quản trị.
 */
require_once __DIR__ . '/../../models/Role.php';

class AdminRoleController {
    private $roleModel;

    /**
     * Khởi tạo model vai trò cho màn phân quyền.
     */
    public function __construct() {
        $this->roleModel = new Role();
    }

    // Tuyến GET: /admin/role
    /**
     * Hiển thị danh sách vai trò và bộ quyền có thể gán.
     */
    public function index() {
        $roles = $this->roleModel->findAll();
        $availablePermissions = $this->availablePermissions();

        $title = 'Quản lý phân quyền';
        require_once __DIR__ . '/../../views/admin/role/index.php';
    }

    // Tuyến GET: /admin/role/edit/:id
    /**
     * Hiển thị form chỉnh sửa vai trò và các quyền hiện có của vai trò đó.
     */
    public function edit($id) {
        $role = $this->roleModel->findById($id);

        if (!$role) {
            $_SESSION['flash_error'] = 'Vai trò không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/role');
            exit;
        }

        $title = 'Chỉnh sửa vai trò';
        $availablePermissions = $this->availablePermissions();

        $currentPermissions = json_decode($role->permissions, true) ?? [];
        $isSystemAdminRole = $this->isSystemAdminRole($role);

        require_once __DIR__ . '/../../views/admin/role/edit.php';
    }

    // Tuyến POST: /admin/role/edit/:id
    /**
     * Xử lý cập nhật vai trò, chỉ nhận các quyền nằm trong danh sách cho phép.
     */
    public function editPost($id) {
        $role = $this->roleModel->findById($id);

        if (!$role) {
            $_SESSION['flash_error'] = 'Không thể chỉnh sửa vai trò này';
            header('Location: ' . BASE_PATH . '/admin/role');
            exit;
        }

        try {
            $availablePermissions = $this->availablePermissions();
            $permissions = array_values(array_intersect($_POST['permissions'] ?? [], array_keys($availablePermissions)));
            $roleTitle = trim((string)($_POST['title'] ?? ''));

            if ($roleTitle === '') {
                $_SESSION['flash_error'] = 'Tên vai trò không được để trống';
                header('Location: ' . BASE_PATH . "/admin/role/edit/$id");
                exit;
            }

            if ($this->isSystemAdminRole($role)) {
                $roleTitle = 'Admin';
            }

            $data = [
                'title' => htmlspecialchars($roleTitle),
                'description' => htmlspecialchars(trim($_POST['description'] ?? '')),
                'permissions' => $permissions,
            ];

            $this->roleModel->update($id, $data);

            $_SESSION['flash_success'] = 'Cập nhật vai trò thành công!';
            header('Location: ' . BASE_PATH . "/admin/role/edit/$id");
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
            header('Location: ' . BASE_PATH . "/admin/role/edit/$id");
            exit;
        }
    }

    // Tuyến POST: /admin/role/delete/:id
    /**
     * Xóa mềm vai trò, chặn xóa vai trò Admin mặc định.
     */
    public function delete($id) {
        $role = $this->roleModel->findById($id);

        if (!$role) {
            $_SESSION['flash_error'] = 'Vai trò không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/role');
            exit;
        }

        if ($this->isSystemAdminRole($role)) {
            $_SESSION['flash_error'] = 'Không thể xóa vai trò quản trị viên';
            header('Location: ' . BASE_PATH . '/admin/role');
            exit;
        }

        try {
            $sql = 'UPDATE roles SET deleted = 1, deleted_at = NOW() WHERE id = ?';
            Database::execute($sql, [$id]);
            $_SESSION['flash_success'] = 'Xóa vai trò thành công!';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        header('Location: ' . BASE_PATH . '/admin/role');
        exit;
    }

    /**
     * Vai trò Admin là vai trò hệ thống, không cho đổi tên hoặc xóa để tránh tự khóa cổng quản trị.
     */
    private function isSystemAdminRole($role) {
        return strtolower(trim((string)($role->title ?? ''))) === 'admin';
    }

    /**
     * Trả về danh sách mã quyền mà hệ thống quản trị hỗ trợ.
     */
    private function availablePermissions() {
        return [
            'jobs-view' => 'Xem công việc',
            'jobs-create' => 'Tạo công việc',
            'jobs-edit' => 'Sửa công việc',
            'jobs-delete' => 'Xóa công việc',
            'categories-view' => 'Xem danh mục',
            'categories-create' => 'Tạo danh mục',
            'categories-edit' => 'Sửa danh mục',
            'categories-delete' => 'Xóa danh mục',
            'accounts-view' => 'Xem tài khoản',
            'accounts-edit' => 'Sửa tài khoản',
            'accounts-delete' => 'Xóa tài khoản',
            'roles-view' => 'Xem vai trò',
            'roles-edit' => 'Sửa vai trò',
            'roles-delete' => 'Xóa vai trò',
        ];
    }
}
