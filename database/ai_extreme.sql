-- AI EXTREME 2026 Database Schema
-- Database: ai_extreme_2026

CREATE DATABASE IF NOT EXISTS ai_extreme_2026
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ai_extreme_2026;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teams table
CREATE TABLE IF NOT EXISTS teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id VARCHAR(20) NOT NULL UNIQUE,
    team_name VARCHAR(100) NOT NULL,
    registration_fee DECIMAL(10, 2) NOT NULL,
    payment_screenshot VARCHAR(255) DEFAULT NULL,
    payment_status ENUM('PENDING', 'VERIFIED', 'REJECTED') DEFAULT 'PENDING',
    payment_rejection_reason TEXT DEFAULT NULL,
    registration_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    registration_rejection_reason TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_registration_id (registration_id),
    INDEX idx_team_name (team_name),
    INDEX idx_payment_status (payment_status),
    INDEX idx_registration_status (registration_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Team members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    prn VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') NOT NULL,
    is_leader TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_team_id (team_id),
    INDEX idx_prn (prn),
    INDEX idx_email (email),
    INDEX idx_is_leader (is_leader)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) DEFAULT 'AI Extreme 2026',
    event_date DATE DEFAULT '2026-10-14',
    problem_release_date DATE DEFAULT '2026-10-02',
    early_bird_fee DECIMAL(10, 2) DEFAULT 200.00,
    early_bird_deadline DATE DEFAULT '2026-09-20',
    regular_fee DECIMAL(10, 2) DEFAULT 250.00,
    upi_id VARCHAR(100) DEFAULT 'YOUR-UPI-ID@upi',
    qr_image VARCHAR(255) DEFAULT 'assets/images/payment-qr.png',
    official_email VARCHAR(100) DEFAULT 'OFFICIAL_EMAIL',
    official_phone VARCHAR(20) DEFAULT 'OFFICIAL_PHONE',
    problem_statements_published TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Problem statements table
CREATE TABLE IF NOT EXISTS problem_statements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'challenge',
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default site settings
INSERT INTO site_settings (
    event_name, event_date, problem_release_date,
    early_bird_fee, early_bird_deadline, regular_fee,
    upi_id, qr_image, official_email, official_phone,
    problem_statements_published
) VALUES (
    'AI Extreme 2026', '2026-10-14', '2026-10-02',
    200.00, '2026-09-20', 250.00,
    'YOUR-UPI-ID@upi', 'assets/images/payment-qr.png',
    'OFFICIAL_EMAIL', 'OFFICIAL_PHONE', 0
);

-- Admin setup: Use setup/create-admin.php to create the first admin account.
-- Do NOT insert a default admin password here.
