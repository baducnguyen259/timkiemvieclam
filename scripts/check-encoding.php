<?php

declare(strict_types=1);

/**
 * Kiểm tra các tệp văn bản trong dự án để phát hiện BOM hoặc dấu hiệu mã hóa UTF-16.
 */
$root = dirname(__DIR__);

$skipDirs = [
    '.git',
    'vendor',
    'node_modules',
    'logs',
    'storage',
    'cache',
];

$checkExtensions = [
    'php', 'phtml', 'html', 'htm',
    'css', 'scss', 'js', 'ts', 'json',
    'xml', 'yml', 'yaml', 'md', 'txt',
    'sql', 'env',
];

$checkNames = [
    '.env',
    '.env.example',
    '.gitignore',
    '.htaccess',
];

$violations = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $fullPath = $file->getPathname();
    $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $fullPath);
    $relativePath = str_replace('\\', '/', $relativePath);

    $skip = false;
    foreach ($skipDirs as $dir) {
        if (str_starts_with($relativePath, $dir . '/')) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }

    $name = $file->getFilename();
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $checkExtensions, true) && !in_array($name, $checkNames, true)) {
        continue;
    }

    $handle = @fopen($fullPath, 'rb');
    if ($handle === false) {
        continue;
    }
    $header = fread($handle, 4);
    fclose($handle);

    if ($header === false) {
        continue;
    }

    $bytes = array_values(unpack('C*', $header));

    if (count($bytes) >= 2) {
        if ($bytes[0] === 0xFF && $bytes[1] === 0xFE) {
            $violations[] = [$relativePath, 'UTF-16 LE BOM'];
            continue;
        }
        if ($bytes[0] === 0xFE && $bytes[1] === 0xFF) {
            $violations[] = [$relativePath, 'UTF-16 BE BOM'];
            continue;
        }
    }

    if (count($bytes) >= 3 && $bytes[0] === 0xEF && $bytes[1] === 0xBB && $bytes[2] === 0xBF) {
        $violations[] = [$relativePath, 'UTF-8 BOM'];
        continue;
    }

    $content = file_get_contents($fullPath, false, null, 0, 4096);
    if ($content === false || $content === '') {
        continue;
    }

    $sampleLength = strlen($content);
    $nullByteCount = substr_count($content, "\0");
    if ($sampleLength > 0 && ($nullByteCount / $sampleLength) > 0.20) {
        $violations[] = [$relativePath, 'Likely UTF-16 (no BOM)'];
    }
}

if ($violations === []) {
    echo "Encoding check passed: no UTF-16/UTF-8 BOM files found.\n";
    exit(0);
}

echo "Encoding check failed. Convert these files to UTF-8 (without BOM):\n";
foreach ($violations as [$path, $reason]) {
    echo "- {$path} [{$reason}]\n";
}
exit(1);
