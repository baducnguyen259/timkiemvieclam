-- Thêm vai trò mặc định nếu chưa tồn tại.
-- File này an toàn khi chạy lại sau database/schema.sql.
INSERT INTO roles (title, description, created_at, updated_at)
SELECT 'Admin', 'Full system access', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE title = 'Admin' AND deleted = 0
);

INSERT INTO roles (title, description, created_at, updated_at)
SELECT 'Employer', 'Can manage own jobs', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE title = 'Employer' AND deleted = 0
);

INSERT INTO roles (title, description, created_at, updated_at)
SELECT 'User', 'Basic user access', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE title = 'User' AND deleted = 0
);
