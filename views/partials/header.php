<?php
// Đảm bảo session đã được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đường dẫn đến thư mục avatar - chỉ định nghĩa nếu chưa tồn tại
if (!defined('AVATAR_URL_PATH')) {
    define('AVATAR_URL_PATH', 'assets/uploads/avatars/');
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mini CRM</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Custom CSS -->
  <style>
  .avatar-sm {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
  }

  .sidebar {
    min-height: 100vh;
    background-color: #f8f9fa;
    border-right: 1px solid #dee2e6;
  }

  .sidebar .nav-link {
    color: #333;
    padding: 0.8rem 1rem;
  }

  .sidebar .nav-link.active {
    background-color: #007bff;
    color: white;
  }

  .sidebar .nav-link:hover:not(.active) {
    background-color: #e9ecef;
  }
  </style>
</head>

<body>

  <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php?page=dashboard">
        <i class="fas fa-headset me-2"></i>Mini CRM
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i>
              <?php echo htmlspecialchars($_SESSION['username']); ?>
              <span class="badge bg-secondary"><?php echo ucfirst($_SESSION['role']); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="index.php?action=logout">
                  <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 d-md-block sidebar">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link <?php echo ($page === 'dashboard') ? 'active' : ''; ?>"
                href="index.php?page=dashboard">
                <i class="fas fa-tachometer-alt me-2"></i>
                Tổng quan
              </a>
            </li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo ($page === 'create_customer') ? 'active' : ''; ?>"
                href="index.php?page=create_customer">
                <i class="fas fa-user-plus me-2"></i>
                Thêm khách hàng
              </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="index.php?page=export_csv">
                <i class="fas fa-file-export me-2"></i>
                Xuất CSV
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <!-- Thông báo flash -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php 
                        echo htmlspecialchars($_SESSION['error']); 
                        unset($_SESSION['error']);
                    ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php 
                        echo htmlspecialchars($_SESSION['success']); 
                        unset($_SESSION['success']);
                    ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="container">
          <?php endif; ?>