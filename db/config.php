<?php
/**
 * Database Configuration
 * 
 * Contains all database connection parameters
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change to your MySQL username
define('DB_PASS', '');      // Change to your MySQL password
define('DB_NAME', 'mini_crm');

// Base URL for the application
define('BASE_URL', 'http://localhost:8000');

// Upload paths
define('AVATAR_UPLOAD_PATH', __DIR__ . '/../assets/uploads/avatars/');
define('AVATAR_URL_PATH', BASE_URL . '/assets/uploads/avatars/');

// Maximum avatar file size (2MB in bytes)
define('MAX_AVATAR_SIZE', 2 * 1024 * 1024);

// Maximum dimensions for resized avatars
define('MAX_AVATAR_WIDTH', 200);
define('MAX_AVATAR_HEIGHT', 200);

// Items per page for pagination
define('ITEMS_PER_PAGE', 10);
