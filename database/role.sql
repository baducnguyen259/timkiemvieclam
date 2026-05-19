-- Thêm vai trò mặc định
INSERT INTO roles (title, description, permissions, created_at, updated_at) VALUES
('Admin', 'Full system access', 
 '["jobs-view","jobs-create","jobs-edit","jobs-delete","categories-view","categories-create","categories-edit","categories-delete","accounts-view","accounts-edit","accounts-delete","roles-view","roles-edit","roles-delete"]',
 NOW(), NOW()),

('Employer', 'Can manage own jobs', 
 '["jobs-view","jobs-create","jobs-edit","jobs-delete"]',
 NOW(), NOW()),

('User', 'Basic user access', 
 '["jobs-view"]',
 NOW(), NOW());
