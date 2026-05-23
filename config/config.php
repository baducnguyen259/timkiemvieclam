<?php
/**
 * Cấu hình khởi tạo ứng dụng:
 * - Nạp biến môi trường từ .env (không làm lộ dữ liệu ra đầu ra).
 * - Chuẩn hóa APP_ENV/APP_DEBUG và hằng URL cho cả local lẫn production.
 * - Cung cấp các hằng dùng chung cho bộ điều khiển/giao diện.
 */

if (!function_exists('loadEnvFile')) {
    /**
     * Đọc file .env dạng KEY=VALUE và nạp vào $_ENV/$_SERVER để phần còn lại của ứng dụng dùng cấu hình thống nhất.
     */
    function loadEnvFile(string $envPath): void {
        if (!is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($value !== '') {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $_ENV[$key] = $value;
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }
    }
}

if (!function_exists('envBool')) {
    /**
     * Lấy một biến môi trường dạng boolean, có giá trị mặc định khi biến không tồn tại hoặc không parse được.
     */
    function envBool(string $key, bool $default = false): bool {
        if (!array_key_exists($key, $_ENV)) {
            return $default;
        }
        $value = filter_var($_ENV[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $value === null ? $default : $value;
    }
}

loadEnvFile(__DIR__ . '/../.env');

$appEnv = strtolower(trim((string)($_ENV['APP_ENV'] ?? 'production')));
$appDebug = envBool('APP_DEBUG', $appEnv !== 'production');
$appUrl = trim((string)($_ENV['APP_URL'] ?? ''));

if (!defined('APP_ENV')) {
    define('APP_ENV', $appEnv);
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $appDebug);
}

$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/\\');
$basePath = ($basePath === '/' || $basePath === '.') ? '' : $basePath;

if ($appUrl !== '' && filter_var($appUrl, FILTER_VALIDATE_URL)) {
    $baseUrl = rtrim($appUrl, '/');
    $parsedPath = parse_url($baseUrl, PHP_URL_PATH);
    if (is_string($parsedPath) && $parsedPath !== '') {
        $basePath = rtrim($parsedPath, '/\\');
        if ($basePath === '/') {
            $basePath = '';
        }
    }
} else {
    $forwardedProto = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $forwardedProto === 'https'
        || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443');
    $protocol = $isHttps ? 'https' : 'http';

    $hostHeader = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $host = trim(explode(',', $hostHeader)[0]);
    $baseUrl = $protocol . '://' . $host . $basePath;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $baseUrl);
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $basePath);
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
}
// Fix #29: Đảm bảo thư mục upload tồn tại
if (!is_dir(UPLOAD_PATH)) {
    @mkdir(UPLOAD_PATH, 0755, true);
}

$appTimezone = trim((string)($_ENV['APP_TIMEZONE'] ?? 'Asia/Ho_Chi_Minh'));
if ($appTimezone !== '' && in_array($appTimezone, timezone_identifiers_list(), true)) {
    date_default_timezone_set($appTimezone);
}
