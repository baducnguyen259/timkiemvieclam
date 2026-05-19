-- Bổ sung đường dẫn CV cho bảng applications
ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS cv_link VARCHAR(1000) NULL AFTER cv_file;
