CREATE DATABASE IF NOT EXISTS hr_management
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE hr_management;

-- -- 1. Create a dedicated application user
-- CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'YourStrongRandomPassword123!';

-- -- 2. Grant permissions ONLY for the specific database
-- GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX 
-- ON my_company_db.* 
-- TO 'app_user'@'localhost';

-- -- 3. Apply the new permissions immediately
-- FLUSH PRIVILEGES;

-- -- 4. Every table contains this columns
-- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
-- updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

-- Ensure clean creation script execution
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payroll, leaves, departments, attendance, shifts, employees, users, roles;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- LEVEL 1: Independent Master Data
-- ==========================================

-- 1. Roles Table
CREATE TABLE IF NOT EXISTS role (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- LEVEL 2: User Account Data
-- ==========================================

-- 2. Users Table (Belongs to a Role)
CREATE TABLE IF NOT EXISTS user (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) 
        REFERENCES role(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- LEVEL 3: Core Business Entity
-- ==========================================

-- 6. Departments Table (Created here so employee can reference it)
CREATE TABLE IF NOT EXISTS department (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    budget DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Employees Table (Links User Profile & Department)
CREATE TABLE IF NOT EXISTS employee (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE, -- 1:1 match with user account
    department_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    hire_date DATE NOT NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_employee_user FOREIGN KEY (user_id) 
        REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_employee_department FOREIGN KEY (department_id) 
        REFERENCES department(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- LEVEL 4: Operational Data (Dependent on Employee)
-- ==========================================

-- 4. Shifts Table
CREATE TABLE IF NOT EXISTS shift (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_shift_employee FOREIGN KEY (employee_id) 
        REFERENCES employee(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_shift_date (shift_date) -- Optimized for daily schedule lookups
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL,
    status ENUM('present', 'absent', 'late', 'half_day') NOT NULL DEFAULT 'present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id) 
        REFERENCES employee(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_employee_date (employee_id, work_date) -- Prevents duplicate check-ins on same day
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Leaves Table
CREATE TABLE IF NOT EXISTS `leave` ( -- 'leave' is a reserved SQL keyword, wrapping in backticks
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    leave_type ENUM('sick', 'casual', 'vacation', 'unpaid') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_leave_employee FOREIGN KEY (employee_id) 
        REFERENCES employee(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Payroll Table (Dependent on Department or Employee)
CREATE TABLE IF NOT EXISTS payroll (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL, -- Directly bound to employee via department flow
    pay_period_start DATE NOT NULL,
    pay_period_end DATE NOT NULL,
    base_salary DECIMAL(10, 2) NOT NULL,
    allowances DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    deductions DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    net_pay DECIMAL(10, 2) GENERATED ALWAYS AS (base_salary + allowances - deductions) STORED,
    payment_status ENUM('pending', 'processed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_payroll_employee FOREIGN KEY (employee_id) 
        REFERENCES employee(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
ADD CONSTRAINT `fk_employees_department`
FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
ON DELETE SET NULL
ON UPDATE CASCADE;