<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/PasswordResetSession.php';
require_once __DIR__ . '/../helpers/Redirect.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/SavedJob.php';

/**
 * Khẳng định điều kiện phải đúng, nếu sai thì ném lỗi để test hiện tại thất bại.
 */
function assert_true($condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * So sánh nghiêm ngặt giá trị kỳ vọng và giá trị thực tế trong test.
 */
function assert_same($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' | expected=' . var_export($expected, true) . ' actual=' . var_export($actual, true));
    }
}

/**
 * Chạy một test case và in trạng thái PASS khi callback hoàn tất không ném exception.
 */
function run_test(string $name, callable $test): void {
    $test();
    echo "[PASS] {$name}\n";
}
