<?php

/**
 * Tạo URL chuyển hướng an toàn, chỉ cho phép quay về đường dẫn nội bộ của ứng dụng.
 */
class Redirect {
    public static function back(string $fallback): string {
        return self::safeUrl($_SERVER['HTTP_REFERER'] ?? '', $fallback);
    }

    public static function safeUrl($url, string $fallback): string {
        $fallback = self::normalizeFallback($fallback);
        if (!is_string($url)) {
            return $fallback;
        }

        $url = trim($url);
        if ($url === '' || preg_match('/[\r\n]/', $url)) {
            return $fallback;
        }

        if (str_starts_with($url, '/')) {
            return self::isAllowedPath($url) ? $url : $fallback;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $fallback;
        }

        if (!self::isCurrentHost($parts)) {
            return $fallback;
        }

        $path = $parts['path'] ?? '/';
        if (!self::isAllowedPath($path)) {
            return $fallback;
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        return $path . $query;
    }

    private static function normalizeFallback(string $fallback): string {
        if ($fallback === '' || $fallback[0] !== '/') {
            return defined('BASE_PATH') && BASE_PATH !== '' ? BASE_PATH . '/' : '/';
        }

        return $fallback;
    }

    private static function isAllowedPath(string $path): bool {
        if ($path === '' || $path[0] !== '/' || str_starts_with($path, '//')) {
            return false;
        }

        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if ($basePath === '') {
            return true;
        }

        return $path === $basePath || str_starts_with($path, $basePath . '/');
    }

    private static function isCurrentHost(array $parts): bool {
        $requestHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($requestHost === '') {
            return false;
        }

        $current = parse_url('http://' . $requestHost);
        if ($current === false || empty($current['host'])) {
            return false;
        }

        $targetHost = strtolower((string)$parts['host']);
        $currentHost = strtolower((string)$current['host']);
        $targetPort = isset($parts['port']) ? (int)$parts['port'] : null;
        $currentPort = isset($current['port']) ? (int)$current['port'] : null;

        return $targetHost === $currentHost && $targetPort === $currentPort;
    }
}
