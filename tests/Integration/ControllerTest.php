<?php

run_test('invalid csrf post is blocked before controller execution', function (): void {
    $script = tempnam(sys_get_temp_dir(), 'csrf-invalid-');
    file_put_contents($script, <<<'PHP'
<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = [];
$_POST['_csrf_token'] = 'invalid';
require getcwd() . '/helpers/Csrf.php';
Csrf::enforceForPostRequest();
echo 'NOT_REACHED';
PHP);

    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script), $output, $status);
    @unlink($script);
    $joinedOutput = implode("\n", $output);

    assert_true(str_contains($joinedOutput, '403 - Token CSRF không hợp lệ'), 'Invalid CSRF request should return 403 response body');
    assert_true(!str_contains($joinedOutput, 'NOT_REACHED'), 'Invalid CSRF request must stop execution');
});

run_test('valid csrf post passes through', function (): void {
    $script = tempnam(sys_get_temp_dir(), 'csrf-valid-');
    file_put_contents($script, <<<'PHP'
<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = [];
require getcwd() . '/helpers/Csrf.php';
$_POST['_csrf_token'] = Csrf::token();
Csrf::enforceForPostRequest();
echo 'OK';
PHP);

    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script), $output, $status);
    @unlink($script);
    $joinedOutput = implode("\n", $output);

    assert_same('OK', $joinedOutput, 'Valid CSRF request should continue');
});
