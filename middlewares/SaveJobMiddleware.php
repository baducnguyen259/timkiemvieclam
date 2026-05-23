<?php
/**
 * Giải thích mã:
 * - Middleware bảo vệ luồng lưu việc.
 * - Chạy trước controller để kiểm tra đăng nhập, quyền truy cập và ngữ cảnh request.
 */
require_once __DIR__ . '/../models/SavedJob.php';
require_once __DIR__ . '/../helpers/Generate.php';
require_once __DIR__ . '/../helpers/Security.php';

class SaveJobMiddleware {
    /**
     * Đảm bảo mỗi request có phiên lưu việc (nếu có), đồng bộ danh sách ẩn danh với user khi đã đăng nhập.
     * Fix #21: Không tự động tạo bản ghi mới trong DB cho khách vãng lai để tránh phình dữ liệu do bot/crawler.
     */
    public static function handle() {
        $savedJobModel = new SavedJob();
        $cookieSessionId = trim((string)($_COOKIE['saveJobId'] ?? ''));
        $currentUserId = isset($GLOBALS['current_user']) ? (int)$GLOBALS['current_user']->id : null;

        try {
            $cookieSavedJob = null;
            if (preg_match('/^[a-f0-9]{32}$/i', $cookieSessionId)) {
                $cookieSavedJob = $savedJobModel->findBySessionId($cookieSessionId);
            }

            $savedJob = null;
            if ($currentUserId !== null) {
                $savedJob = $savedJobModel->findByUserId($currentUserId);

                if (
                    $savedJob &&
                    $cookieSavedJob &&
                    (int)$cookieSavedJob->id !== (int)$savedJob->id &&
                    empty($cookieSavedJob->user_id)
                ) {
                    $savedJobModel->mergeJobs($cookieSavedJob->id, $savedJob->id);
                    $savedJob = $savedJobModel->findById($savedJob->id);
                } elseif (
                    !$savedJob &&
                    $cookieSavedJob &&
                    empty($cookieSavedJob->user_id)
                ) {
                    $savedJobModel->updateUserId($cookieSavedJob->session_id, $currentUserId);
                    $savedJob = $savedJobModel->findById($cookieSavedJob->id);
                }

                // Nếu user đã đăng nhập nhưng vẫn chưa có phiên lưu việc, ta tự động tạo cho họ (hợp lệ vì là user thật)
                if (!$savedJob) {
                    $sessionId = Generate::randomString(32);
                    $newSavedJobId = $savedJobModel->create($sessionId, $currentUserId);
                    $savedJob = $savedJobModel->findById($newSavedJobId);
                }
            } elseif ($cookieSavedJob && empty($cookieSavedJob->user_id)) {
                $savedJob = $cookieSavedJob;
            }

            // Fix #21: Nếu không có $savedJob (khách vãng lai chưa lưu việc bao giờ), ta KHÔNG tạo DB record.
            if ($savedJob) {
                if ($cookieSessionId !== $savedJob->session_id) {
                    Security::setCookie('saveJobId', $savedJob->session_id, Security::persistentCookieExpiresAt());
                }
                $GLOBALS['current_saved_job'] = $savedJob;
                $GLOBALS['miniSavedJobs'] = $savedJob;
            } else {
                $GLOBALS['current_saved_job'] = null;
                $GLOBALS['miniSavedJobs'] = (object)[
                    'id' => null,
                    'session_id' => null,
                    'total_saved_jobs' => 0,
                    'job_ids' => []
                ];
            }
        } catch (Exception $e) {
            error_log("Lỗi SaveJobMiddleware: " . $e->getMessage());
            $GLOBALS['current_saved_job'] = null;
            $GLOBALS['miniSavedJobs'] = (object)[
                'id' => null,
                'session_id' => null,
                'total_saved_jobs' => 0,
                'job_ids' => []
            ];
        }
    }
}
