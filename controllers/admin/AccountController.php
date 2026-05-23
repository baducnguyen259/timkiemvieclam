<?php
/**
 * Giải thích mã:
 * - Bộ điều khiển khu vực quản trị cho chức năng Account.
 * - Quản lý danh sách, chỉnh sửa, khóa/mở khóa và xóa mềm tài khoản hệ thống hoặc ứng viên.
 */
require_once __DIR__ . '/../../models/Account.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../models/Company.php';
require_once __DIR__ . '/../../helpers/Redirect.php';

require_once __DIR__ . '/../../helpers/Pagination.php';

class AdminAccountController {
    private $accountModel;
    private $userModel;
    private $roleModel;
    private $companyModel;

    /**
     * Khởi tạo các model cần để quản lý tài khoản, vai trò và công ty liên kết.
     */
    public function __construct() {
        $this->accountModel = new Account();
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->companyModel = new Company();
    }

    /**
     * Hiển thị danh sách hợp nhất giữa tài khoản nội bộ và tài khoản ứng viên, có phân trang.
     */
    public function index() {
        // Đếm tổng số bản ghi từ cả 2 bảng để tính phân trang
        $countSql = "SELECT COUNT(*) AS total FROM (
            SELECT a.id FROM accounts a WHERE a.deleted = 0
            UNION ALL
            SELECT u.id FROM users u WHERE u.deleted = 0
        ) AS combined";
        $countRow = Database::fetchOne($countSql);
        $totalAccounts = $countRow ? (int)$countRow->total : 0;

        $pagination = Pagination::calculate(15, $_GET['page'] ?? 1, $totalAccounts);

        // Lấy trang hiện tại bằng UNION với LIMIT/OFFSET — tránh load toàn bộ vào bộ nhớ PHP
        $sql = "SELECT
                    a.id,
                    a.full_name,
                    a.email,
                    a.status,
                    a.created_at,
                    r.title as role_title,
                    'account' as entity_type
                FROM accounts a
                LEFT JOIN roles r ON a.role_id = r.id
                WHERE a.deleted = 0
            UNION ALL
                SELECT
                    u.id,
                    u.full_name,
                    u.email,
                    u.status,
                    u.created_at,
                    'seeker' as role_title,
                    'user' as entity_type
                FROM users u
                WHERE u.deleted = 0
            ORDER BY created_at DESC, id DESC
            LIMIT ? OFFSET ?";

        $accounts = Database::fetchAll($sql, [$pagination['limitItem'], $pagination['skipItem']]);

        $title = 'Quản lý tài khoản';
        require_once __DIR__ . '/../../views/admin/account/index.php';
    }

    /**
     * Hiển thị form chỉnh sửa một tài khoản nội bộ theo id.
     */
    public function edit($id) {
        $account = $this->accountModel->findById($id);

        if (!$account) {
            $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/account');
            exit;
        }

        $roles = $this->roleModel->findAll();
        $companies = $this->companyModel->findAll();

        $title = 'Chỉnh sửa tài khoản';
        require_once __DIR__ . '/../../views/admin/account/edit.php';
    }

    /**
     * Xử lý cập nhật tài khoản nội bộ, bao gồm đổi mật khẩu nếu có nhập mật khẩu mới.
     */
    public function editPost($id) {
        $account = $this->accountModel->findById($id);

        if (!$account) {
            $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/account');
            exit;
        }

        try {
            $data = [
                'full_name' => trim($_POST['fullName'] ?? ''),
                'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
                'phone' => trim($_POST['phone'] ?? ''),
                'role_id' => !empty($_POST['role_id']) ? (int)$_POST['role_id'] : null,
                'company_id' => !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null,
                'status' => $_POST['status'] ?? 'active'
            ];

            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 8) {
                    $_SESSION['flash_error'] = 'Mật khẩu phải có ít nhất 8 ký tự';
                    header('Location: ' . BASE_PATH . "/admin/account/edit/$id");
                    exit;
                }

                $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }

            $this->accountModel->update($id, $data);

            $_SESSION['flash_success'] = 'Cập nhật tài khoản thành công!';
            header('Location: ' . BASE_PATH . "/admin/account/edit/$id");
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
            header('Location: ' . BASE_PATH . "/admin/account/edit/$id");
            exit;
        }
    }

    /**
     * Đổi trạng thái active/inactive của tài khoản nội bộ hoặc ứng viên.
     */
    public function toggleStatus($id) {
        $id = (int)$id;
        $entityType = $_POST['entity_type'] ?? 'account';
        $entityType = $entityType === 'user' ? 'user' : 'account';

        if ($entityType === 'account' && $id === (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Không thể khóa tài khoản của chính mình';
            header('Location: ' . BASE_PATH . '/admin/account');
            exit;
        }

        $account = $entityType === 'user'
            ? $this->userModel->findById($id)
            : $this->accountModel->findById($id);

        if (!$account || !isset($account->status)) {
            $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/account');
            exit;
        }

        $newStatus = $account->status === 'active' ? 'inactive' : 'active';

        try {
            if ($entityType === 'user') {
                $this->userModel->update($id, ['status' => $newStatus]);
            } else {
                $this->accountModel->update($id, ['status' => $newStatus]);
            }

            $_SESSION['flash_success'] = $newStatus === 'active' ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        $referer = Redirect::back(BASE_PATH . '/admin/account');
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Xóa mềm tài khoản nội bộ hoặc ứng viên, đồng thời chặn admin tự xóa chính mình.
     */
    public function delete($id) {
        $id = (int)$id;
        $entityType = $_POST['entity_type'] ?? 'account';
        $entityType = $entityType === 'user' ? 'user' : 'account';

        if ($entityType === 'account' && $id === (int)$GLOBALS['current_user']->id) {
            $_SESSION['flash_error'] = 'Không thể xóa tài khoản của chính mình';
            header('Location: ' . BASE_PATH . '/admin/account');
            exit;
        }

        $account = $entityType === 'user'
            ? $this->userModel->findById($id)
            : $this->accountModel->findById($id);

        if (!$account) {
            $_SESSION['flash_error'] = 'Tài khoản không tồn tại';
            header('Location: ' . BASE_PATH . '/admin/account');
            exit;
        }

        try {
            if ($entityType === 'user') {
                $this->userModel->delete($id);
            } else {
                $this->accountModel->delete($id);
            }

            $_SESSION['flash_success'] = 'Xóa tài khoản thành công!';
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['flash_error'] = 'Có lỗi xảy ra';
        }

        header('Location: ' . BASE_PATH . '/admin/account');
        exit;
    }
}
