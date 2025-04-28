<?php
/**
 * Database Connection Test
 * 
 * This file is used to test the connection to the database
 * Visit http://localhost/mini-crm/db_test.php to run the test
 */

// Hiển thị tất cả lỗi để dễ dàng debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load cấu hình database
require_once __DIR__ . '/db/config.php';

echo "<h1>Kiểm tra kết nối cơ sở dữ liệu</h1>";
echo "<hr>";

// Hiển thị thông tin kết nối hiện tại
echo "<h2>Thông tin cấu hình:</h2>";
echo "<ul>";
echo "<li>Host: " . DB_HOST . "</li>";
echo "<li>Port: " . DB_PORT . "</li>";
echo "<li>Database: " . DB_NAME . "</li>";
echo "<li>Username: " . DB_USER . "</li>";
echo "<li>Password: " . (empty(DB_PASS) ? "(trống)" : "******") . "</li>";
echo "</ul>";

// Thử kết nối trực tiếp bằng PDO
echo "<h2>Thử kết nối PDO:</h2>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Timeout sau 5 giây nếu không kết nối được
    ];
    
    echo "Đang kết nối tới: $dsn<br>";
    $startTime = microtime(true);
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    $endTime = microtime(true);
    $timeTaken = round(($endTime - $startTime) * 1000, 2);
    
    echo "<div style='color: green; font-weight: bold;'>✅ Kết nối thành công! (thời gian: {$timeTaken}ms)</div>";
    
    // Kiểm tra và hiển thị phiên bản MySQL/MariaDB
    $stmt = $conn->query("SELECT version() as version");
    $version = $stmt->fetch();
    echo "Phiên bản database: " . $version['version'] . "<br>";
    
    // Kiểm tra xem database mini_crm đã tồn tại chưa
    echo "<h2>Kiểm tra database:</h2>";
    
    // Kiểm tra bảng users
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    if ($tableExists) {
        echo "<div style='color: green;'>✅ Bảng 'users' đã tồn tại.</div>";
        
        // Đếm số lượng user
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch();
        echo "Số lượng người dùng: " . $count['count'] . "<br>";
    } else {
        echo "<div style='color: red;'>❌ Bảng 'users' chưa tồn tại.</div>";
        echo "Vui lòng import file database.sql để tạo cấu trúc database.<br>";
    }
    
    // Kiểm tra bảng customers
    $stmt = $conn->query("SHOW TABLES LIKE 'customers'");
    $tableExists = $stmt->rowCount() > 0;
    if ($tableExists) {
        echo "<div style='color: green;'>✅ Bảng 'customers' đã tồn tại.</div>";
        
        // Đếm số lượng khách hàng
        $stmt = $conn->query("SELECT COUNT(*) as count FROM customers");
        $count = $stmt->fetch();
        echo "Số lượng khách hàng: " . $count['count'] . "<br>";
    } else {
        echo "<div style='color: red;'>❌ Bảng 'customers' chưa tồn tại.</div>";
        echo "Vui lòng import file database.sql để tạo cấu trúc database.<br>";
    }
    
    $conn = null; // Đóng kết nối
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Lỗi kết nối: " . $e->getMessage() . "</div>";
    
    // Hiển thị các gợi ý để sửa lỗi
    echo "<h2>Các nguyên nhân có thể:</h2>";
    echo "<ol>";
    echo "<li>MySQL/MariaDB chưa được khởi động (kiểm tra XAMPP Control Panel).</li>";
    echo "<li>Cổng kết nối không đúng (hiện tại là: " . DB_PORT . ").</li>";
    echo "<li>Thông tin đăng nhập không đúng.</li>";
    echo "<li>Database '" . DB_NAME . "' chưa được tạo.</li>";
    echo "</ol>";
    
    echo "<h2>Cách giải quyết:</h2>";
    echo "<ol>";
    echo "<li>Đảm bảo dịch vụ MySQL trong XAMPP đã được khởi động.</li>";
    echo "<li>Kiểm tra port MySQL trong XAMPP (thường hiển thị ở XAMPP Control Panel).</li>";
    echo "<li>Kiểm tra file db/config.php và đảm bảo thông tin kết nối chính xác.</li>";
    echo "<li>Tạo database '" . DB_NAME . "' thông qua phpMyAdmin.</li>";
    echo "<li>Import file database.sql vào database đã tạo.</li>";
    echo "</ol>";
}
?>