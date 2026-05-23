<?php
/**
 * Giải thích mã:
 * - Bảng định tuyến trung tâm cho client, employer, admin và API.
 * - Ánh xạ URI + phương thức HTTP tới controller và middleware bảo vệ tương ứng.
 */
// Nạp toàn bộ controller
require_once __DIR__ . '/../controllers/client/HomeController.php';
require_once __DIR__ . '/../controllers/client/JobController.php';
require_once __DIR__ . '/../controllers/client/UserController.php';
require_once __DIR__ . '/../controllers/client/SearchController.php';
require_once __DIR__ . '/../controllers/client/SavedJobController.php';
require_once __DIR__ . '/../controllers/client/ApplicationController.php';

require_once __DIR__ . '/../controllers/admin/JobController.php';
require_once __DIR__ . '/../controllers/admin/AuthController.php';
require_once __DIR__ . '/../controllers/admin/DashboardController.php';
require_once __DIR__ . '/../controllers/admin/AccountController.php';
require_once __DIR__ . '/../controllers/admin/JobCategoryController.php';
require_once __DIR__ . '/../controllers/employer/AuthController.php';
require_once __DIR__ . '/../controllers/employer/DashboardController.php';
require_once __DIR__ . '/../controllers/employer/JobController.php';
require_once __DIR__ . '/../controllers/employer/ApplicationController.php';

require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/AdminAuthMiddleware.php';
require_once __DIR__ . '/../middlewares/EmployerAuthMiddleware.php';
require_once __DIR__ . '/../middlewares/CategoryMiddleware.php';
require_once __DIR__ . '/../middlewares/SaveJobMiddleware.php';
require_once __DIR__ . '/../middlewares/UserMiddleware.php';

/**
 * Điều phối request theo URI và HTTP method, áp middleware phù hợp rồi gọi controller tương ứng.
 */
function route($uri, $method) {
    // Fix #14: Chỉ chạy middleware client cho các route không phải admin/employer/api
    $isAdminRoute = str_starts_with($uri, '/admin');
    $isEmployerRoute = str_starts_with($uri, '/employer');
    $isApiRoute = str_starts_with($uri, '/api');

    if (!$isAdminRoute && !$isEmployerRoute && !$isApiRoute) {
        UserMiddleware::handle();
        SaveJobMiddleware::handle();
        CategoryMiddleware::handle();
    }
    
    // ==================== ROUTE NGƯỜI DÙNG ====================
    
    // Trang chủ
    if ($uri === '/' && $method === 'GET') {
        $controller = new HomeController();
        $controller->index();
        return;
    }
    
    // Việc làm - SỬA LỖI: Đặt route cụ thể trước route động
    if ($uri === '/jobs' && $method === 'GET') {
        $controller = new JobController();
        $controller->index();
        return;
    }
    
    // Chi tiết việc làm - Phải đặt trước route danh mục
    if (preg_match('#^/jobs/detail/([a-z0-9-]+)$#', $uri, $matches) && $method === 'GET') {
        $controller = new JobController();
        $controller->detail($matches[1]);
        return;
    }
    
    // Việc làm theo danh mục
    if (preg_match('#^/jobs/([a-z0-9-]+)$#', $uri, $matches) && $method === 'GET') {
        $controller = new JobController();
        $controller->category($matches[1]);
        return;
    }
    
    // Tìm kiếm
    if ($uri === '/search' && $method === 'GET') {
        $controller = new SearchController();
        $controller->index();
        return;
    }
    
    // Việc đã lưu
    if ($uri === '/saved-jobs' && $method === 'GET') {
        $controller = new SavedJobController();
        $controller->index();
        return;
    }
    
    if (preg_match('#^/saved-jobs/add/(\d+)$#', $uri, $matches) && $method === 'POST') {
        $controller = new SavedJobController();
        $controller->add($matches[1]);
        return;
    }
    
    if (preg_match('#^/saved-jobs/remove/(\d+)$#', $uri, $matches) && $method === 'POST') {
        $controller = new SavedJobController();
        $controller->remove($matches[1]);
        return;
    }
    
    // Người dùng - Đăng ký
    if ($uri === '/user/register' && $method === 'GET') {
        $controller = new UserController();
        $controller->register();
        return;
    }
    
    if ($uri === '/user/register' && $method === 'POST') {
        $controller = new UserController();
        $controller->registerPost();
        return;
    }
    
    // Người dùng - Đăng nhập
    if ($uri === '/user/login' && $method === 'GET') {
        $controller = new UserController();
        $controller->login();
        return;
    }
    
    if ($uri === '/user/login' && $method === 'POST') {
        $controller = new UserController();
        $controller->loginPost();
        return;
    }
    
    // Người dùng - Đăng xuất
    if ($uri === '/user/logout' && $method === 'POST') {
        $controller = new UserController();
        $controller->logout();
        return;
    }
    
    // Người dùng - Quên mật khẩu
    if ($uri === '/user/password/forgot' && $method === 'GET') {
        $controller = new UserController();
        $controller->forgotPassword();
        return;
    }
    
    if ($uri === '/user/password/forgot' && $method === 'POST') {
        $controller = new UserController();
        $controller->forgotPasswordPost();
        return;
    }
    
    // Người dùng - OTP
    if ($uri === '/user/password/otp' && $method === 'GET') {
        $controller = new UserController();
        $controller->otpPassword();
        return;
    }
    
    if ($uri === '/user/password/otp' && $method === 'POST') {
        $controller = new UserController();
        $controller->otpPasswordPost();
        return;
    }
    
    // Người dùng - Đặt lại mật khẩu
    if ($uri === '/user/password/reset' && $method === 'GET') {
        $controller = new UserController();
        $controller->resetPassword();
        return;
    }
    
    if ($uri === '/user/password/reset' && $method === 'POST') {
        $controller = new UserController();
        $controller->resetPasswordPost();
        return;
    }
    
    // Người dùng - Thông tin (cần đăng nhập)
    if ($uri === '/user/info' && $method === 'GET') {
        AuthMiddleware::requireAuth();
        $controller = new UserController();
        $controller->info();
        return;
    }
    
    // Ứng tuyển (cần đăng nhập)
    if ($uri === '/applications' && $method === 'GET') {
        AuthMiddleware::requireAuth();
        $controller = new ApplicationController();
        $controller->index();
        return;
    }
    
    if (preg_match('#^/applications/create/(\d+)$#', $uri, $matches) && $method === 'GET') {
        AuthMiddleware::requireAuth();
        $controller = new ApplicationController();
        $controller->create($matches[1]);
        return;
    }
    
    if (preg_match('#^/applications/create/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AuthMiddleware::requireAuth();
        $controller = new ApplicationController();
        $controller->createPost($matches[1]);
        return;
    }

    // ==================== ROUTE NHÀ TUYỂN DỤNG ====================

    // Nhà tuyển dụng - Xác thực
    if ($uri === '/employer/auth/login' && $method === 'GET') {
        $controller = new EmployerAuthController();
        $controller->login();
        return;
    }

    if ($uri === '/employer/auth/login' && $method === 'POST') {
        $controller = new UserController();
        $controller->loginPost();
        return;
    }

    if ($uri === '/employer/auth/logout' && $method === 'POST') {
        $controller = new EmployerAuthController();
        $controller->logout();
        return;
    }

    // Nhà tuyển dụng - Bảng điều khiển (cần đăng nhập)
    if (($uri === '/employer' || $uri === '/employer/dashboard') && $method === 'GET') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerDashboardController();
        $controller->index();
        return;
    }

    // Nhà tuyển dụng - Việc làm (cần đăng nhập)
    if ($uri === '/employer/job' && $method === 'GET') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->index();
        return;
    }

    if ($uri === '/employer/job/create' && $method === 'GET') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->create();
        return;
    }

    if ($uri === '/employer/job/create' && $method === 'POST') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->createPost();
        return;
    }

    if (preg_match('#^/employer/job/edit/(\d+)$#', $uri, $matches) && $method === 'GET') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->edit($matches[1]);
        return;
    }

    if (preg_match('#^/employer/job/edit/(\d+)$#', $uri, $matches) && $method === 'POST') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->editPost($matches[1]);
        return;
    }

    if (preg_match('#^/employer/job/delete/(\d+)$#', $uri, $matches) && $method === 'POST') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->delete($matches[1]);
        return;
    }

    if (preg_match('#^/employer/job/change-status/(active|inactive)/(\d+)$#', $uri, $matches) && $method === 'POST') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->changeStatus($matches[1], $matches[2]);
        return;
    }

    if (preg_match('#^/employer/job/detail/(\d+)$#', $uri, $matches) && $method === 'GET') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerJobController();
        $controller->detail($matches[1]);
        return;
    }

    // Nhà tuyển dụng - Ứng tuyển (cần đăng nhập)
    if ($uri === '/employer/application' && $method === 'GET') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerApplicationController();
        $controller->index();
        return;
    }

    if (preg_match('#^/employer/application/change-status/(pending|reviewed|accepted|rejected)/(\d+)$#', $uri, $matches) && $method === 'POST') {
        EmployerAuthMiddleware::requireAuth();
        $controller = new EmployerApplicationController();
        $controller->changeStatus($matches[1], $matches[2]);
        return;
    }
    
    // ==================== ROUTE QUẢN TRỊ ====================
    
    // Quản trị - Xác thực
    if ($uri === '/admin/auth/login' && $method === 'GET') {
        $controller = new AdminAuthController();
        $controller->login();
        return;
    }
    
    if ($uri === '/admin/auth/login' && $method === 'POST') {
        $controller = new AdminAuthController();
        $controller->loginPost();
        return;
    }
    
    if ($uri === '/admin/auth/logout' && $method === 'POST') {
        $controller = new AdminAuthController();
        $controller->logout();
        return;
    }
    
    // Quản trị - Bảng điều khiển (cần đăng nhập)
    if (($uri === '/admin' || $uri === '/admin/dashboard') && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminDashboardController();
        $controller->index();
        return;
    }
    
    // Quản trị - Việc làm (cần đăng nhập)
    if ($uri === '/admin/job' && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->index();
        return;
    }
    
    if ($uri === '/admin/job/create' && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->create();
        return;
    }
    
    if ($uri === '/admin/job/create' && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->createPost();
        return;
    }
    
    if (preg_match('#^/admin/job/edit/(\d+)$#', $uri, $matches) && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->edit($matches[1]);
        return;
    }
    
    if (preg_match('#^/admin/job/edit/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->editPost($matches[1]);
        return;
    }
    
    if (preg_match('#^/admin/job/delete/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->delete($matches[1]);
        return;
    }
    
    if (preg_match('#^/admin/job/change-status/(active|inactive)/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->changeStatus($matches[1], $matches[2]);
        return;
    }
    
    if ($uri === '/admin/job/change-multi' && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->changeMulti();
        return;
    }
    
    if (preg_match('#^/admin/job/detail/(\d+)$#', $uri, $matches) && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobController();
        $controller->detail($matches[1]);
        return;
    }
    
    // Quản trị - Danh mục việc làm
    if ($uri === '/admin/job-category' && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobCategoryController();
        $controller->index();
        return;
    }
    
    if ($uri === '/admin/job-category/create' && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobCategoryController();
        $controller->create();
        return;
    }
    
    if ($uri === '/admin/job-category/create' && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobCategoryController();
        $controller->createPost();
        return;
    }
    
    if (preg_match('#^/admin/job-category/edit/(\d+)$#', $uri, $matches) && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobCategoryController();
        $controller->edit($matches[1]);
        return;
    }
    
    if (preg_match('#^/admin/job-category/edit/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobCategoryController();
        $controller->editPost($matches[1]);
        return;
    }
    
    if (preg_match('#^/admin/job-category/delete/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminJobCategoryController();
        $controller->delete($matches[1]);
        return;
    }

    // Quản trị - Tài khoản
    if ($uri === '/admin/account' && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminAccountController();
        $controller->index();
        return;
    }

    if (preg_match('#^/admin/account/edit/(\d+)$#', $uri, $matches) && $method === 'GET') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminAccountController();
        $controller->edit($matches[1]);
        return;
    }

    if (preg_match('#^/admin/account/edit/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminAccountController();
        $controller->editPost($matches[1]);
        return;
    }

    if (preg_match('#^/admin/account/toggle-status/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminAccountController();
        $controller->toggleStatus($matches[1]);
        return;
    }

    if (preg_match('#^/admin/account/delete/(\d+)$#', $uri, $matches) && $method === 'POST') {
        AdminAuthMiddleware::requireAuth();
        $controller = new AdminAccountController();
        $controller->delete($matches[1]);
        return;
    }
    
    // Fix #15: Tuyến API — chỉ trả jobs active, giới hạn trường và phân trang
    if ($uri === '/api/jobs' && $method === 'GET') {
        $jobModel = new Job();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $skip = ($page - 1) * $limit;

        $jobs = $jobModel->find(
            ['deleted' => false, 'status' => 'active'],
            ['limit' => $limit, 'skip' => $skip, 'sort' => ['created_at' => -1]]
        );

        header('Content-Type: application/json');
        echo json_encode(['data' => $jobs, 'page' => $page, 'limit' => $limit]);
        return;
    }
    
    // Không tìm thấy trang (404)
    http_response_code(404);
    require_once __DIR__ . '/../views/errors/404.php';
}
