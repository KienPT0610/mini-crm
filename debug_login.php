<?php
/**
 * Debug Login
 * 
 * File này giúp kiểm tra quá trình đăng nhập và database
 * Đã tích hợp các chức năng từ password_verify_test.php
 */

// Hiển thị tất cả lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Biến lưu kết quả cập nhật mật khẩu
$updateResult = null;

// Xử lý cập nhật mật khẩu trực tiếp
if (isset($_POST['update_password'])) {
    try {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $newHash = $_POST['new_hash'] ?? '';
        
        // Load cấu hình database để đảm bảo kết nối
        require_once __DIR__ . '/db/config.php';
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Cập nhật mật khẩu
        $stmt = $pdo->prepare("UPDATE users SET password = :new_hash WHERE username = :username");
        $stmt->execute([
            'new_hash' => $newHash,
            'username' => $username
        ]);
        
        if ($stmt->rowCount() > 0) {
            $updateResult = [
                'success' => true,
                'message' => "Cập nhật mật khẩu thành công! Giờ bạn có thể đăng nhập với username '$username' và mật khẩu '$password'."
            ];
        } else {
            $updateResult = [
                'success' => false,
                'message' => "Không thể cập nhật mật khẩu. Người dùng '$username' không tồn tại."
            ];
        }
    } catch (Exception $e) {
        $updateResult = [
            'success' => false,
            'message' => "Lỗi: " . $e->getMessage()
        ];
    }
}

// Load cấu hình database
require_once __DIR__ . '/db/config.php';
require_once __DIR__ . '/db/Database.php';
require_once __DIR__ . '/models/User.php';

// Bắt đầu session
session_start();

// Các biến debug
$debugInfo = [];
$debugInfo['session_id'] = session_id();
$debugInfo['session_status'] = session_status();
$debugInfo['session_data'] = $_SESSION;

// Kiểm tra kết nối database
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $debugInfo['dsn'] = $dsn;
    $debugInfo['connection_start'] = microtime(true);
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    $debugInfo['connection_end'] = microtime(true);
    $debugInfo['connection_time'] = $debugInfo['connection_end'] - $debugInfo['connection_start'];
    $debugInfo['connection_status'] = 'Connected successfully';
    
    // Kiểm tra bảng users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->rowCount() > 0;
    $debugInfo['users_table_exists'] = $tableExists;
    
    if ($tableExists) {
        // Kiểm tra user admin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $adminUser = $stmt->fetch();
        
        $debugInfo['admin_user_exists'] = !empty($adminUser);
        
        if (!empty($adminUser)) {
            $debugInfo['admin_user'] = [
                'id' => $adminUser['id'],
                'username' => $adminUser['username'],
                'role' => $adminUser['role'],
                'password_hash' => $adminUser['password'],
                'password_hash_length' => strlen($adminUser['password']),
            ];
            
            // Kiểm tra mật khẩu admin123
            $debugInfo['password_verify'] = password_verify('admin123', $adminUser['password']);
        }
        
        // Đếm tổng số user
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch();
        $debugInfo['total_users'] = $count['count'];
    }
    
} catch (PDOException $e) {
    $debugInfo['connection_error'] = $e->getMessage();
}

// Kiểm tra thử đăng nhập trực tiếp
if (isset($_POST['test_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $debugInfo['login_attempt'] = [
        'username' => $username,
        'password_length' => strlen($password),
        'password' => $password, // Lưu mật khẩu để hiển thị khi đăng nhập thất bại
    ];
    
    try {
        $user = new User();
        
        // Lấy thông tin user (nếu có)
        $userInfo = $user->findByUsername($username);
        if ($userInfo) {
            $debugInfo['user_found'] = true;
            $debugInfo['stored_hash'] = $userInfo['password'];
            
            // Tạo hash mới từ mật khẩu nhập vào để so sánh
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $debugInfo['new_hash'] = $newHash;
            
            // Kiểm tra mật khẩu
            $passwordVerified = password_verify($password, $userInfo['password']);
            $debugInfo['password_verified'] = $passwordVerified;
        } else {
            $debugInfo['user_found'] = false;
        }
        
        // Xác thực đăng nhập
        $result = $user->authenticate($username, $password);
        
        $debugInfo['login_result'] = !empty($result);
        $debugInfo['login_user_data'] = $result ? [
            'id' => $result['id'],
            'username' => $result['username'],
            'role' => $result['role'],
        ] : null;
        
    } catch (Exception $e) {
        $debugInfo['login_error'] = $e->getMessage();
    }
}

// Đảm bảo phản hồi không bị cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login - Mini CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .password-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Debug Login - Mini CRM</h1>
        
        <?php if ($updateResult): ?>
            <div class="alert alert-<?= $updateResult['success'] ? 'success' : 'danger' ?> alert-dismissible fade show mb-4">
                <?= htmlspecialchars($updateResult['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Kiểm tra đăng nhập</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="admin">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="text" class="form-control" id="password" name="password" value="admin123">
                            </div>
                            <button type="submit" name="test_login" class="btn btn-primary">Kiểm tra đăng nhập</button>
                        </form>
                        
                        <?php if (isset($debugInfo['login_attempt'])): ?>
                            <div class="mt-4">
                                <h6>Kết quả kiểm tra:</h6>
                                <?php if ($debugInfo['login_result']): ?>
                                    <div class="alert alert-success">
                                        <strong>Đăng nhập thành công!</strong> 
                                        User: <?= htmlspecialchars($debugInfo['login_user_data']['username']) ?>
                                        (<?= htmlspecialchars($debugInfo['login_user_data']['role']) ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <strong>Đăng nhập thất bại!</strong>
                                        <?php if (isset($debugInfo['login_error'])): ?>
                                            <br>Lỗi: <?= htmlspecialchars($debugInfo['login_error']) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Thêm thông tin chi tiết về mật khẩu khi đăng nhập thất bại -->
                                    <?php if (isset($debugInfo['user_found']) && $debugInfo['user_found']): ?>
                                        <div class="password-section mt-3">
                                            <h6>Chi tiết mật khẩu:</h6>
                                            <ul>
                                                <li><strong>Mật khẩu nhập vào:</strong> <?= htmlspecialchars($debugInfo['login_attempt']['password']) ?></li>
                                                <li><strong>Hash đang lưu trong DB:</strong> <?= htmlspecialchars($debugInfo['stored_hash']) ?></li>
                                                <li><strong>Hash mới tạo từ mật khẩu nhập vào:</strong> <?= htmlspecialchars($debugInfo['new_hash']) ?></li>
                                                <li><strong>Kết quả xác thực:</strong> 
                                                    <?php if ($debugInfo['password_verified']): ?>
                                                        <span class="success">ĐÚNG (mật khẩu khớp với hash trong DB)</span>
                                                    <?php else: ?>
                                                        <span class="error">SAI (mật khẩu KHÔNG khớp với hash trong DB)</span>
                                                    <?php endif; ?>
                                                </li>
                                            </ul>
                                            
                                            <?php if (!$debugInfo['password_verified']): ?>
                                                <div class="alert alert-warning mt-2">
                                                    <strong>Giải pháp:</strong> Cập nhật mật khẩu trong database bằng lệnh SQL:<br>
                                                    <code>UPDATE users SET password = '<?= htmlspecialchars($debugInfo['new_hash']) ?>' WHERE username = '<?= htmlspecialchars($debugInfo['login_attempt']['username']) ?>';</code>
                                                    
                                                    <!-- Thêm nút cập nhật mật khẩu trực tiếp -->
                                                    <form method="post" class="mt-3">
                                                        <input type="hidden" name="username" value="<?= htmlspecialchars($debugInfo['login_attempt']['username']) ?>">
                                                        <input type="hidden" name="password" value="<?= htmlspecialchars($debugInfo['login_attempt']['password']) ?>">
                                                        <input type="hidden" name="new_hash" value="<?= htmlspecialchars($debugInfo['new_hash']) ?>">
                                                        <button type="submit" name="update_password" class="btn btn-warning">
                                                            <i class="fas fa-key"></i> Cập nhật mật khẩu trực tiếp
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mt-2">
                                            <strong>Lỗi:</strong> Không tìm thấy người dùng với username "<?= htmlspecialchars($debugInfo['login_attempt']['username']) ?>"
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Thông tin Session</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Session ID:</strong> <?= $debugInfo['session_id'] ?></p>
                        <p><strong>Session Status:</strong> 
                            <?php
                            switch($debugInfo['session_status']) {
                                case PHP_SESSION_DISABLED:
                                    echo '<span class="error">PHP_SESSION_DISABLED</span>';
                                    break;
                                case PHP_SESSION_NONE:
                                    echo '<span class="error">PHP_SESSION_NONE</span>';
                                    break;
                                case PHP_SESSION_ACTIVE:
                                    echo '<span class="success">PHP_SESSION_ACTIVE</span>';
                                    break;
                            }
                            ?>
                        </p>
                        
                        <h6>Dữ liệu Session:</h6>
                        <pre><?php print_r($debugInfo['session_data']); ?></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Kết nối Database</h5>
            </div>
            <div class="card-body">
                <p><strong>DSN:</strong> <?= htmlspecialchars($debugInfo['dsn'] ?? 'N/A') ?></p>
                
                <?php if (isset($debugInfo['connection_error'])): ?>
                    <div class="alert alert-danger">
                        <strong>Lỗi kết nối:</strong> <?= htmlspecialchars($debugInfo['connection_error']) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <strong>Kết nối thành công!</strong>
                        Thời gian kết nối: <?= round(($debugInfo['connection_time'] * 1000), 2) ?> ms
                    </div>
                <?php endif; ?>
                
                <h6 class="mt-3">Kiểm tra bảng users:</h6>
                <?php if (isset($debugInfo['users_table_exists']) && $debugInfo['users_table_exists']): ?>
                    <div class="alert alert-success">
                        <strong>Bảng users tồn tại!</strong>
                        Tổng số người dùng: <?= $debugInfo['total_users'] ?>
                    </div>
                    
                    <h6>Kiểm tra user admin:</h6>
                    <?php if ($debugInfo['admin_user_exists']): ?>
                        <div class="alert alert-success">
                            <strong>User admin tồn tại!</strong>
                            <ul>
                                <li>ID: <?= $debugInfo['admin_user']['id'] ?></li>
                                <li>Role: <?= $debugInfo['admin_user']['role'] ?></li>
                                <li>Password hash length: <?= $debugInfo['admin_user']['password_hash_length'] ?></li>
                                <li>Password verify "admin123": 
                                    <?php if ($debugInfo['password_verify']): ?>
                                        <span class="success">Đúng</span>
                                    <?php else: ?>
                                        <span class="error">Sai</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <strong>User admin không tồn tại!</strong>
                            Bạn cần import lại database.sql.
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-danger">
                        <strong>Bảng users không tồn tại!</strong>
                        Bạn cần import file database.sql để tạo bảng và dữ liệu mẫu.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Các vấn đề thường gặp và cách khắc phục</h5>
            </div>
            <div class="card-body">
                <h6>1. Không kết nối được database</h6>
                <ul>
                    <li>Kiểm tra MySQL đã chạy chưa trong XAMPP Control Panel</li>
                    <li>Xác nhận port MySQL (hiện tại đang dùng: <?= DB_PORT ?>)</li>
                    <li>Đảm bảo database "<?= DB_NAME ?>" đã được tạo</li>
                    <li>Kiểm tra thông tin đăng nhập MySQL trong file db/config.php</li>
                </ul>
                
                <h6>2. Đăng nhập không thành công</h6>
                <ul>
                    <li>Đảm bảo bảng users đã được tạo và có dữ liệu</li>
                    <li>Mật khẩu đúng là "admin123" (không có dấu ngoặc kép)</li>
                    <li>Kiểm tra xem password_verify có hoạt động đúng không</li>
                    <li>Kiểm tra session có hoạt động không (session_start)</li>
                </ul>
                
                <h6>3. Tạo lại tất cả với dữ liệu mẫu</h6>
                <ol>
                    <li>Truy cập phpMyAdmin (<?= htmlspecialchars("http://localhost/phpmyadmin") ?>)</li>
                    <li>Tạo database "<?= DB_NAME ?>" với collation utf8mb4_unicode_ci (nếu chưa có)</li>
                    <li>Import file database.sql từ dự án mini-crm</li>
                </ol>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>