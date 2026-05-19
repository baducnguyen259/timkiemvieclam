<?php
/**
 * Giải thích mã:
 * - Điểm vào chính của quá trình khởi tạo ứng dụng.
 * - Khởi tạo session, nạp cấu hình, chuẩn hóa URI và chuyển request vào router.
 */
// Nạp cấu hình
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/Csrf.php';

// Cấu hình hiển thị lỗi
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}

// Tăng cường bảo mật cookie session cho production
$sessionName = trim((string)($_ENV['SESSION_NAME'] ?? 'job_portal_session'));

if (session_status() === PHP_SESSION_NONE) {
    session_name($sessionName !== '' ? $sessionName : 'job_portal_session');
    session_set_cookie_params(Security::sessionCookieParams());
    session_start();
}

// Chặn request POST thiếu hoặc sai CSRF token trước khi request đi vào router.
Csrf::enforceForPostRequest();

// Nạp routes
require_once __DIR__ . '/../routes/web.php';

// Lấy URI và phương thức request
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Chuẩn hóa base path khi cài trong thư mục con (ví dụ: /job-portal-php/public/)
$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
if ($basePath && $basePath !== '/' && strpos($request_uri, $basePath) === 0) {
    $request_uri = substr($request_uri, strlen($basePath));
}
if (empty($request_uri) || $request_uri[0] !== '/') {
    $request_uri = '/' . $request_uri;
}
// Bỏ dấu / ở cuối (trừ trang gốc)
if ($request_uri !== '/' && substr($request_uri, -1) === '/') {
    $request_uri = rtrim($request_uri, '/');
}

$request_method = $_SERVER['REQUEST_METHOD'];

// Hỗ trợ ghi đè method từ form
if ($request_method === 'POST' && isset($_POST['_method'])) {
    $request_method = strtoupper($_POST['_method']);
}

// Điều hướng request
try {
    route($request_uri, $request_method);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "500 - Lỗi máy chủ nội bộ";
}
