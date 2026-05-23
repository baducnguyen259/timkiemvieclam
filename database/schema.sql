-- Cơ sở dữ liệu
-- cPanel/phpMyAdmin: tạo database trong cPanel, chọn database đó rồi import file này.
-- Local CLI: tạo và chọn database trước khi import, ví dụ:
-- CREATE DATABASE IF NOT EXISTS job_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE job_portal;

-- Bảng: users (thay cho collection users)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    token_user VARCHAR(128) UNIQUE NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    cv_file VARCHAR(500),
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token_user),
    INDEX idx_status (status, deleted)
) ENGINE=InnoDB;

-- Bảng: job_categories (thay cho collection jobs-category)
CREATE TABLE job_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    thumbnail VARCHAR(500),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    slug VARCHAR(255) UNIQUE NOT NULL,
    position INT DEFAULT 0,
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES job_categories(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status, deleted)
) ENGINE=InnoDB;

-- Bảng: companies (thay cho collection company)
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    logo VARCHAR(500),
    description TEXT,
    website VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng: roles (phải tạo trước accounts do có khóa ngoại)
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng: accounts (tài khoản quản trị)
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    token VARCHAR(128) UNIQUE NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(500),
    role_id INT,
    company_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB;

-- Bảng: jobs (thay cho collection jobs)
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255),
    company_logo VARCHAR(500),
    description TEXT,
    candidate_requirements TEXT,
    benefits TEXT,
    location VARCHAR(255),
    address_detail TEXT,
    category_id INT,
    thumbnail VARCHAR(500),
    salary_min DECIMAL(15,2),
    salary_max DECIMAL(15,2),
    featured ENUM('0', '1') DEFAULT '0',
    type ENUM('Full-time', 'Part-time', 'Contract', 'Internship'),
    experience VARCHAR(100),
    application_deadline DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    slug VARCHAR(255) UNIQUE NOT NULL,
    position INT DEFAULT 0,
    created_by_account_id INT,
    deleted BOOLEAN DEFAULT FALSE,
    deleted_by_account_id INT NULL,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status, deleted),
    INDEX idx_featured (featured),
    INDEX idx_location (location),
    FULLTEXT idx_title (title)
) ENGINE=InnoDB;

-- Bảng: job_skills (quan hệ nhiều-nhiều)
CREATE TABLE job_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX idx_job (job_id)
) ENGINE=InnoDB;

-- Bảng: job_updates (theo dõi người cập nhật việc làm)
CREATE TABLE job_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    account_id INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng: saved_jobs (thay cho collection saved-jobs)
CREATE TABLE saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB;

-- Bảng: saved_job_items (mã việc làm trong saved_jobs)
CREATE TABLE saved_job_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    saved_job_id INT NOT NULL,
    job_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (saved_job_id) REFERENCES saved_jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved_job (saved_job_id, job_id)
) ENGINE=InnoDB;

-- Bảng: applications (hồ sơ ứng tuyển)
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    cv_file VARCHAR(500),
    cv_link VARCHAR(1000),
    cover_letter TEXT,
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    deleted BOOLEAN DEFAULT FALSE,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_job (job_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Bảng: forgot_password (OTP để đặt lại mật khẩu)
CREATE TABLE forgot_password (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    expire_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_otp (email, otp),
    INDEX idx_expire (expire_at)
) ENGINE=InnoDB;

-- Dữ liệu mặc định
INSERT INTO roles (title, description) VALUES
('Admin', 'Full access'),
('Employer', 'Can manage own jobs'),
('User', 'Basic user');
