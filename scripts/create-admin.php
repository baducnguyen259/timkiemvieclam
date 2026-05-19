<?php
/**
 * Script khởi tạo tài khoản quản trị viên đầu tiên.
 * Chỉ chạy trong môi trường CLI.
 * Cách dùng:
 * php scripts/create-admin.php [email] [password] [name]
 */

// Nạp các thành phần cấu hình và model cần dùng
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/Role.php';

echo "=== Tạo tài khoản quản trị viên ===\n\n";

// Giá trị mặc định nếu không truyền tham số từ CLI
$defaultEmail = 'admin@jobportal.com';
$defaultPassword = 'admin123456';
$defaultName = 'Quản trị viên';

// Chỉ cho phép chạy từ dòng lệnh và lấy tham số đầu vào
if (php_sapi_name() === 'cli') {
    $email = $argv[1] ?? $defaultEmail;
    $password = $argv[2] ?? $defaultPassword;
    $name = $argv[3] ?? $defaultName;
} else {
    die("Script này chỉ chạy từ dòng lệnh.\nSử dụng: php scripts/create-admin.php [email] [password] [name]\n");
}

try {
    // Kiểm tra khả năng kết nối cơ sở dữ liệu trước khi xử lý
    if (!Database::testConnection()) {
        die("Không thể kết nối cơ sở dữ liệu. Hãy kiểm tra file .env và đảm bảo MySQL đang chạy.\n");
    }

    $accountModel = new Account();
    $roleModel = new Role();

    // Dừng nếu email admin đã tồn tại
    $existing = $accountModel->findByEmail($email);
    if ($existing) {
        die("Tài khoản với email '$email' đã tồn tại!\n");
    }

    // Lấy vai trò quản trị viên hiện có hoặc tạo mới nếu chưa có
    $adminRole = Database::fetchOne("SELECT * FROM roles WHERE title = 'Admin' AND deleted = 0 LIMIT 1");

    if (!$adminRole) {
        echo "Tạo vai trò quản trị viên...\n";
        $permissions = json_encode([
            'jobs-view', 'jobs-create', 'jobs-edit', 'jobs-delete',
            'categories-view', 'categories-create', 'categories-edit', 'categories-delete',
            'accounts-view', 'accounts-edit', 'accounts-delete',
            'roles-view', 'roles-edit', 'roles-delete'
        ]);

        Database::execute(
            "INSERT INTO roles (title, description, permissions) VALUES (?, ?, ?)",
            ['Admin', 'Toàn quyền quản trị', $permissions]
        );
        $roleId = Database::lastInsertId();
        echo "Đã tạo vai trò quản trị viên (ID: $roleId)\n";
    } else {
        $roleId = $adminRole->id;
        echo "Vai trò quản trị viên đã tồn tại (ID: $roleId)\n";
    }

    // Tạo tài khoản admin mới với mật khẩu đã băm
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $data = [
        'full_name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role_id' => $roleId,
        'status' => 'active'
    ];

    $result = $accountModel->create($data);

    echo "\n✅ Tạo tài khoản quản trị viên thành công!\n";
    echo "   Email: $email\n";
    echo "   Mật khẩu: $password\n";
    echo "   Tên: $name\n";
    echo "\nĐăng nhập tại: /admin/auth/login\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
