-- =============================================
-- Smart Inventory Management System
-- Database Schema
-- MySQL Database for XAMPP
-- =============================================

-- Create database
CREATE DATABASE IF NOT EXISTS smart_inventory_db;
USE smart_inventory_db;

-- =============================================
-- Table: users
-- Stores system users (Admin and Staff)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: categories
-- Product categories for organization
-- =============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: products
-- Main product inventory table
-- =============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT NOT NULL DEFAULT 10,
    expiry_date DATE NULL,
    supplier_name VARCHAR(200),
    product_image VARCHAR(255) NULL,
    description TEXT,
    unit_price DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_category (category_id),
    INDEX idx_expiry (expiry_date),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: stock_logs
-- Tracks inventory movements for AI analysis
-- =============================================
CREATE TABLE IF NOT EXISTS stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type ENUM('add', 'remove', 'update', 'expired', 'sold') NOT NULL,
    quantity_change INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_created (created_at),
    INDEX idx_action (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: alerts
-- System-generated alerts and notifications
-- =============================================
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    alert_type ENUM('low_stock', 'near_expiry', 'expired') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_type (alert_type),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Insert Sample Data
-- =============================================

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, full_name) VALUES
('admin', 'admin@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator'),
('staff1', 'staff1@inventory.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'John Doe');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Electronic items and gadgets'),
('Food & Beverages', 'Food products and drinks'),
('Clothing', 'Apparel and textiles'),
('Medicine', 'Pharmaceutical products'),
('Office Supplies', 'Stationery and office equipment'),
('Household Items', 'Daily household necessities');

-- Insert sample products
INSERT INTO products (name, category_id, quantity, min_stock_level, expiry_date, supplier_name, unit_price) VALUES
('Laptop Computer', 1, 25, 5, NULL, 'TechSupplier Inc.', 899.99),
('Wireless Mouse', 1, 150, 20, NULL, 'TechSupplier Inc.', 29.99),
('Milk (1L)', 2, 48, 15, DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Fresh Foods Ltd.', 3.50),
('Bread', 2, 30, 10, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Fresh Foods Ltd.', 2.00),
('Pain Reliever', 4, 60, 10, DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'PharmaCorp', 8.99),
('Notebooks', 5, 200, 50, NULL, 'OfficeMax', 4.99),
('Coffee Beans', 2, 15, 5, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Coffee World', 12.99),
('T-Shirt', 3, 80, 20, NULL, 'Fashion Store', 19.99);
