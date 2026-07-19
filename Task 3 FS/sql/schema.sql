-- ============================================================
-- Task 3: User Management System - Database Schema
-- ============================================================
-- Normalization:
--   1NF: All columns are atomic, no repeating groups.
--        Each cell holds a single value (no arrays or JSON).
--   2NF: Single-column PRIMARY KEY (id) on every table.
--        No partial dependency exists.
--   3NF: role_name is stored in the roles table only.
--        users references it via role_id (FK), removing transitive
--        dependency (user -> role_id -> role_name).
-- ============================================================

CREATE DATABASE IF NOT EXISTS user_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE user_management;

-- ----------------------------------------
-- Roles table (lookup for user roles)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------
-- Users table
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    role_id         INT          NOT NULL DEFAULT 2,
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign key ensures referential integrity
    CONSTRAINT fk_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    -- Indexes for performance
    INDEX idx_users_email (email),
    INDEX idx_users_role  (role_id),
    INDEX idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------
-- Seed roles
-- ----------------------------------------
INSERT IGNORE INTO roles (id, role_name) VALUES
(1, 'Admin'),
(2, 'User');

-- ----------------------------------------
-- Seed a default admin (password: admin123)
-- Hash generated with password_hash('admin123', PASSWORD_DEFAULT)
-- ----------------------------------------
INSERT IGNORE INTO users (username, email, password, role_id)
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
