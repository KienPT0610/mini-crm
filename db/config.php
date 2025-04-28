<?php
/**
 * Database Configuration
 * 
 * Contains all database connection parameters
 */

// Database connection parameters
if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1'); // Thay đổi từ 'localhost' sang '127.0.0.1'
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');  // Thay đổi thành tên người dùng MySQL của bạn nếu cần
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');      // Thay đổi thành mật khẩu MySQL của bạn nếu có
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'mini_crm'); // Đảm bảo database này đã được tạo
}
if (!defined('DB_PORT')) {
    define('DB_PORT', 3307);    // Cập nhật port từ 3306 sang 3307 cho XAMPP
}

// Base URL for the application
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

// Các hằng số còn lại sử dụng kiểm tra điều kiện để tránh lỗi trùng lặp
// Upload paths
if (!defined('AVATAR_UPLOAD_PATH')) {
    define('AVATAR_UPLOAD_PATH', __DIR__ . '/../assets/uploads/avatars/');
}
if (!defined('AVATAR_URL_PATH')) {
    define('AVATAR_URL_PATH', BASE_URL . '/assets/uploads/avatars/');
}

// Maximum avatar file size (2MB in bytes)
if (!defined('MAX_AVATAR_SIZE')) {
    define('MAX_AVATAR_SIZE', 2 * 1024 * 1024);
}

// Maximum dimensions for resized avatars
if (!defined('MAX_AVATAR_WIDTH')) {
    define('MAX_AVATAR_WIDTH', 200);
}
if (!defined('MAX_AVATAR_HEIGHT')) {
    define('MAX_AVATAR_HEIGHT', 200);
}

// Items per page for pagination
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 10);
}
