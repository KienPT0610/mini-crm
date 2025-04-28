-- Mini CRM Database Structure
-- Tạo cấu trúc cơ sở dữ liệu cho hệ thống CRM Mini

-- Create database (uncomment if needed)
-- CREATE DATABASE mini_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE mini_crm;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customers table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_customer_name ON customers(name);
CREATE INDEX idx_customer_email ON customers(email);
CREATE INDEX idx_customer_phone ON customers(phone);
CREATE INDEX idx_customer_created ON customers(created_at);

-- Insert sample users
-- Default password: admin123 (hashed with bcrypt)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$jDkKOBpcaWPzVsu9aw2Vh.4TJKrO.eqHoFJfuMgCyKVV8.W5p8KW2', 'admin'),
-- Default password: staff123 (hashed with bcrypt)
('staff', '$2y$10$3rZoKOLsFLqLspulHr8G9ukEjSWUsvXwpG6JGjsN9.UUfoGg4HJMW', 'staff');

-- Insert sample customers
INSERT INTO customers (name, email, phone, address, created_at) VALUES
('Nguyễn Văn An', 'an@example.com', '0912345678', 'Hà Nội', '2023-01-15 08:30:00'),
('Trần Thị Bình', 'binh@example.com', '0923456789', 'Hồ Chí Minh', '2023-02-20 09:15:00'),
('Lê Văn Cường', 'cuong@example.com', '0934567890', 'Đà Nẵng', '2023-03-10 10:45:00'),
('Phạm Thị Dung', 'dung@example.com', '0945678901', 'Cần Thơ', '2023-04-05 14:20:00'),
('Hoàng Văn Em', 'em@example.com', '0956789012', 'Hải Phòng', '2023-05-12 16:30:00'),
('Ngô Thị Phương', 'phuong@example.com', '0967890123', 'Nha Trang', '2023-06-18 11:10:00'),
('Đặng Văn Giáp', 'giap@example.com', '0978901234', 'Huế', '2023-07-22 13:45:00'),
('Vũ Thị Hoa', 'hoa@example.com', '0989012345', 'Quảng Ninh', '2023-08-30 09:20:00'),
('Bùi Văn Kiên', 'kien@example.com', '0990123456', 'Bình Dương', '2023-09-15 15:40:00'),
('Mai Thị Lan', 'lan@example.com', '0901234567', 'Vũng Tàu', '2023-10-10 08:50:00'),
('Trịnh Văn Minh', 'minh@example.com', '0912345670', 'Đồng Nai', '2023-11-05 10:15:00'),
('Đinh Thị Nga', 'nga@example.com', '0923456780', 'Long An', '2023-12-20 14:30:00');

-- Note: These passwords are hashed with bcrypt, you can use them to log in:
-- admin/admin123
-- staff/staff123