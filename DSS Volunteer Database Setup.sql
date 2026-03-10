-- DSS Volunteer Database Setup
-- Run this SQL script to create the necessary database and table

-- Create database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS dss_volunteers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE dss_volunteers;

-- Create volunteers table
CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    interests JSON NOT NULL,
    experience TEXT,
    availability VARCHAR(50),
    status ENUM('pending', 'contacted', 'active', 'inactive') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin notes table (optional - for tracking volunteer interactions)
CREATE TABLE IF NOT EXISTS volunteer_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    note TEXT NOT NULL,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    INDEX idx_volunteer_id (volunteer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample query to view all volunteers
-- SELECT * FROM volunteers ORDER BY created_at DESC;

-- Sample query to view volunteers by interest
-- SELECT * FROM volunteers WHERE JSON_CONTAINS(interests, '"tech_tools"') ORDER BY created_at DESC;

-- Sample query to count volunteers by status
-- SELECT status, COUNT(*) as count FROM volunteers GROUP BY status;