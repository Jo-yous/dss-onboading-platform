-- DSS Recruitment Form - Database Setup
-- Run this script in MySQL/MariaDB to create the database and tables for the recruitment form.

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS dss_volunteers
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dss_volunteers;

-- Applications from dss_recruitment_enhanced.html form
CREATE TABLE IF NOT EXISTS recruitment_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    interests JSON NOT NULL COMMENT 'Array of: tech_tools, content_distribution, data_reports, training_support',
    experience TEXT NULL,
    availability VARCHAR(20) NULL COMMENT '1-5, 5-10, 10-20, 20+',
    status ENUM('pending', 'contacted', 'active', 'inactive') NOT NULL DEFAULT 'pending',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: volunteer notes for admins
CREATE TABLE IF NOT EXISTS volunteer_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    created_by VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notes_application
        FOREIGN KEY (application_id) REFERENCES recruitment_applications(id) ON DELETE CASCADE,
    INDEX idx_application_id (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example queries:
-- SELECT * FROM recruitment_applications ORDER BY created_at DESC;
-- SELECT * FROM recruitment_applications WHERE JSON_CONTAINS(interests, '"tech_tools"') ORDER BY created_at DESC;
-- SELECT status, COUNT(*) AS count FROM recruitment_applications GROUP BY status;
