-- Bổ sung thông tin công ty và hạn nộp cho bảng jobs
ALTER TABLE jobs
    ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) NULL AFTER title,
    ADD COLUMN IF NOT EXISTS company_logo VARCHAR(500) NULL AFTER company_name,
    ADD COLUMN IF NOT EXISTS candidate_requirements TEXT NULL AFTER description,
    ADD COLUMN IF NOT EXISTS benefits TEXT NULL AFTER candidate_requirements,
    ADD COLUMN IF NOT EXISTS address_detail TEXT NULL AFTER location,
    ADD COLUMN IF NOT EXISTS application_deadline DATE NULL AFTER experience;
