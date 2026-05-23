<?php

run_test('auth token hashing keeps raw token out of storage', function (): void {
    $token = Security::generateAuthToken();
    $hash = Security::hashAuthToken($token);

    assert_same(64, strlen($token), 'Raw auth token length should be 64 hex chars');
    assert_same(64, strlen($hash), 'Stored auth token hash should be 64 hex chars');
    assert_true($token !== $hash, 'Raw auth token must differ from stored hash');
});

run_test('csrf tokens validate and rotate', function (): void {
    $_SESSION = [];

    $token = Csrf::token();
    assert_true(Csrf::isValid($token), 'Issued CSRF token should validate');
    assert_true(!Csrf::isValid('bad-token'), 'Wrong CSRF token should fail validation');

    $rotatedToken = Csrf::rotate();
    assert_true($token !== $rotatedToken, 'CSRF token should change after rotation');
    assert_true(!Csrf::isValid($token), 'Old CSRF token should fail after rotation');
    assert_true(Csrf::isValid($rotatedToken), 'Rotated CSRF token should validate');
});

run_test('password reset sessions expire after ten minutes', function (): void {
    $payload = PasswordResetSession::payload('user@example.test', 'user');

    assert_same(600, PasswordResetSession::TTL_SECONDS, 'Password reset session TTL should be 600 seconds');
    assert_true(PasswordResetSession::isValid($payload), 'Fresh password reset session should be valid');

    $payload['expires_at'] = time() - 1;
    assert_true(!PasswordResetSession::isValid($payload), 'Expired password reset session should be invalid');
});

run_test('vietnamese slugs normalize to ascii and avoid duplicates', function (): void {
    $existing = [
        'lap-trinh-vien-php' => true,
        'lap-trinh-vien-php-2' => true,
    ];

    assert_same('lap-trinh-vien-php', Generate::slug('Lập trình viên PHP'), 'Vietnamese title should become ASCII slug');
    assert_same(
        'lap-trinh-vien-php-3',
        Generate::uniqueSlug('Lập trình viên PHP', static fn($slug): bool => isset($existing[$slug])),
        'Duplicate slug should get next numeric suffix'
    );
});

run_test('redirect helper keeps only internal same-host targets', function (): void {
    $previousHost = $_SERVER['HTTP_HOST'] ?? null;
    $_SERVER['HTTP_HOST'] = 'example.test';

    try {
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $fallback = ($basePath !== '' ? $basePath : '') . '/admin/job';
        $safeTarget = 'http://example.test' . $fallback . '?page=2';

        assert_same($fallback . '?page=2', Redirect::safeUrl($safeTarget, $fallback), 'Same-host internal referer should be kept');
        assert_same($fallback, Redirect::safeUrl('https://evil.test/admin/job', $fallback), 'External referer should fall back');
        assert_same($fallback, Redirect::safeUrl("//evil.test{$fallback}", $fallback), 'Protocol-relative referer should fall back');
    } finally {
        if ($previousHost === null) {
            unset($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = $previousHost;
        }
    }
});

run_test('file upload validation accepts real jpeg and rejects fake jpeg', function (): void {
    if (!function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
        return;
    }

    $realJpeg = tempnam(sys_get_temp_dir(), 'real-jpeg-');
    $fakeJpeg = tempnam(sys_get_temp_dir(), 'fake-jpeg-');
    $renamedPng = tempnam(sys_get_temp_dir(), 'renamed-png-');

    try {
        $image = imagecreatetruecolor(1, 1);
        assert_true((bool)$image, 'Should create test image');
        imagejpeg($image, $realJpeg);
        if (function_exists('imagepng')) {
            imagepng($image, $renamedPng);
        }
        imagedestroy($image);
        file_put_contents($fakeJpeg, 'not a jpeg');

        $uploader = new FileUpload(['jpg', 'jpeg', 'png']);
        $method = new ReflectionMethod(FileUpload::class, 'validateMimeType');
        $method->setAccessible(true);

        assert_same('jpg', $method->invoke($uploader, $realJpeg, 'jpg'), 'Real JPEG content should validate as jpg');
        if (function_exists('imagepng')) {
            assert_same('png', $method->invoke($uploader, $renamedPng, 'jpg'), 'Real PNG content should be detected even when the original extension is wrong');
        }

        $rejectedFakeJpeg = false;
        try {
            $method->invoke($uploader, $fakeJpeg, 'jpg');
        } catch (ReflectionException $e) {
            throw $e;
        } catch (Throwable $e) {
            $rejectedFakeJpeg = true;
        }

        assert_true($rejectedFakeJpeg, 'Fake JPEG content should be rejected');
    } finally {
        if (is_string($realJpeg) && file_exists($realJpeg)) {
            unlink($realJpeg);
        }
        if (is_string($fakeJpeg) && file_exists($fakeJpeg)) {
            unlink($fakeJpeg);
        }
        if (is_string($renamedPng) && file_exists($renamedPng)) {
            unlink($renamedPng);
        }
    }
});
