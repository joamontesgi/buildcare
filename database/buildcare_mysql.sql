-- ============================================================
-- BuildCare — Script MySQL 8+
-- Ejecutar: mysql -u root -p < buildcare_mysql.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS buildcare
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE buildcare;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- USUARIOS Y AUTENTICACIÓN
-- ============================================================

DROP TABLE IF EXISTS schedule_entries;
DROP TABLE IF EXISTS buildings;
DROP TABLE IF EXISTS catalog_items;
DROP TABLE IF EXISTS vendors;
DROP TABLE IF EXISTS zones;
DROP TABLE IF EXISTS managements;
DROP TABLE IF EXISTS personal_access_tokens;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS migrations;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL DEFAULT 'clerk',
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    PRIMARY KEY (id),
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY personal_access_tokens_token_unique (token),
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id),
    KEY personal_access_tokens_expires_at_index (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CATÁLOGOS
-- ============================================================

CREATE TABLE managements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY managements_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    state VARCHAR(10) NULL DEFAULT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vendors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY vendors_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalog_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY catalog_items_type_name_unique (type, name),
    KEY catalog_items_type_index (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE buildings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    address TEXT NOT NULL,
    state VARCHAR(10) NULL DEFAULT NULL,
    management_id BIGINT UNSIGNED NULL DEFAULT NULL,
    zone_id BIGINT UNSIGNED NULL DEFAULT NULL,
    management VARCHAR(255) NULL DEFAULT NULL,
    bc_clerk VARCHAR(255) NULL DEFAULT NULL,
    supervisor_name VARCHAR(255) NULL DEFAULT NULL,
    project_manager VARCHAR(255) NULL DEFAULT NULL,
    travel_expense TEXT NULL,
    service_manager_info TEXT NULL,
    property_manager_info TEXT NULL,
    paint_specs TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY buildings_management_id_foreign (management_id),
    KEY buildings_zone_id_foreign (zone_id),
    CONSTRAINT buildings_management_id_foreign FOREIGN KEY (management_id) REFERENCES managements (id) ON DELETE SET NULL,
    CONSTRAINT buildings_zone_id_foreign FOREIGN KEY (zone_id) REFERENCES zones (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SCHEDULE DE REPARACIONES
-- ============================================================

CREATE TABLE schedule_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL DEFAULT NULL,
    schedule_date DATE NULL DEFAULT NULL,
    area_section VARCHAR(500) NULL DEFAULT NULL,
    state VARCHAR(100) NULL DEFAULT NULL,
    management VARCHAR(255) NULL DEFAULT NULL,
    bc_clerk VARCHAR(255) NULL DEFAULT NULL,
    building_address TEXT NULL,
    unit_area VARCHAR(255) NULL DEFAULT NULL,
    size VARCHAR(255) NULL DEFAULT NULL,
    worksite_status VARCHAR(255) NULL DEFAULT NULL,
    job_description TEXT NULL,
    job_awarded_to_techs VARCHAR(255) NULL DEFAULT NULL,
    request_po_wtn_wo VARCHAR(255) NULL DEFAULT NULL,
    bc_work_order_estimate VARCHAR(255) NULL DEFAULT NULL,
    extras TEXT NULL,
    special_notes_sequence TEXT NULL,
    vendors_daily_status_report TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY schedule_entries_user_id_foreign (user_id),
    CONSTRAINT schedule_entries_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SISTEMA (cache, colas, migraciones)
-- ============================================================

CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY cache_locks_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL DEFAULT NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL DEFAULT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL,
    connection VARCHAR(255) NOT NULL,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY failed_jobs_uuid_unique (uuid),
    KEY failed_jobs_connection_queue_failed_at_index (connection, queue, failed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Usuario admin (password: password)
INSERT INTO users (name, email, role, password, created_at, updated_at) VALUES
('Admin BuildCare', 'admin@buildcare.com', 'admin', '$2y$12$cNX3TG5DRAgszdav8K7Nqu.6BMpkt7u4f/kUFisgl.qZz5K/CJVuO', NOW(), NOW());

-- Catálogo: states
INSERT INTO catalog_items (type, name, sort_order, is_active, created_at, updated_at) VALUES
('state', 'NJ', 1, 1, NOW(), NOW()),
('state', 'NY', 2, 1, NOW(), NOW()),
('state', 'PA', 3, 1, NOW(), NOW()),
('state', 'FL', 4, 1, NOW(), NOW());

-- Catálogo: sizes
INSERT INTO catalog_items (type, name, sort_order, is_active, created_at, updated_at) VALUES
('size', 'Studio', 1, 1, NOW(), NOW()),
('size', '1BDR', 2, 1, NOW(), NOW()),
('size', '1BDR + Den', 3, 1, NOW(), NOW()),
('size', '2BDR', 4, 1, NOW(), NOW()),
('size', '2BDR + Den', 5, 1, NOW(), NOW()),
('size', '3BDR', 6, 1, NOW(), NOW());

-- Catálogo: worksite status
INSERT INTO catalog_items (type, name, sort_order, is_active, created_at, updated_at) VALUES
('worksite_status', 'Occupied', 1, 1, NOW(), NOW()),
('worksite_status', 'Vacant', 2, 1, NOW(), NOW());

-- Catálogo: request types
INSERT INTO catalog_items (type, name, sort_order, is_active, created_at, updated_at) VALUES
('request_type', 'Email', 1, 1, NOW(), NOW()),
('request_type', 'Phone Call', 2, 1, NOW(), NOW()),
('request_type', 'P.O', 3, 1, NOW(), NOW()),
('request_type', 'WTN', 4, 1, NOW(), NOW()),
('request_type', 'W.O', 5, 1, NOW(), NOW());

-- Vendors
INSERT INTO vendors (name, is_active, created_at, updated_at) VALUES
('AJ Canas Solutions', 1, NOW(), NOW()),
('AJ Canas Solutions Javier', 1, NOW(), NOW()),
('AJ Canas Solutions Ricardo', 1, NOW(), NOW()),
('Alluda Anibal Yojero', 1, NOW(), NOW()),
('Alluda Project', 1, NOW(), NOW()),
('DVM - Atlas Techs', 1, NOW(), NOW()),
('Atlas Cleaning', 1, NOW(), NOW()),
('HG Services', 1, NOW(), NOW());

-- Ejemplo schedule
INSERT INTO schedule_entries (
    user_id, schedule_date, area_section, state, management, bc_clerk,
    building_address, unit_area, size, worksite_status, job_description,
    job_awarded_to_techs, request_po_wtn_wo, created_at, updated_at
) VALUES (
    1, '2026-09-04',
    'WATERFRONT AREA (Fort Lee - Edgewater - Cliffside Park)',
    'NJ', 'AvalonBay', 'Maria Lopez',
    'Avalon at Edgewater I - Old Building (River Mews).', '426', NULL, 'Occupied',
    'Continue Make opening in guest bedroom 3x3 to reinstall loose Hvac vent.',
    'AJ Canas Solutions Javier', 'Email', NOW(), NOW()
);
