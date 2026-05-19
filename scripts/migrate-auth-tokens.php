<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$sql = file_get_contents(__DIR__ . '/../database/update_auth_tokens_hashed.sql');
if ($sql === false) {
    fwrite(STDERR, "Không thể đọc tệp SQL chuyển đổi token.\n");
    exit(1);
}

$statements = array_filter(array_map('trim', explode(';', $sql)));

try {
    foreach ($statements as $statement) {
        Database::connect()->exec($statement);
    }

    echo "Đã áp dụng chuyển đổi token xác thực.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Chuyển đổi token xác thực thất bại: ' . $e->getMessage() . "\n");
    exit(1);
}
