<?php
/**
 * Application Configuration
 * 
 * Contains global constants and settings
 */

// Kiểm tra xem các hằng số đã được định nghĩa chưa trước khi định nghĩa
// Path settings
if (!defined('AVATAR_UPLOAD_PATH')) {
    define('AVATAR_UPLOAD_PATH', __DIR__ . '/assets/uploads/avatars/');
}
if (!defined('AVATAR_URL_PATH')) {
    define('AVATAR_URL_PATH', 'assets/uploads/avatars/');
}

// Pagination settings
if (!defined('ITEMS_PER_PAGE')) {
    define('ITEMS_PER_PAGE', 10);
}

// Upload settings
if (!defined('MAX_AVATAR_SIZE')) {
    define('MAX_AVATAR_SIZE', 2 * 1024 * 1024); // 2MB
}
if (!defined('MAX_AVATAR_WIDTH')) {
    define('MAX_AVATAR_WIDTH', 200);
}
if (!defined('MAX_AVATAR_HEIGHT')) {
    define('MAX_AVATAR_HEIGHT', 200);
}