<?php
/**
 * Customer List/Dashboard page
 * 
 * Display all customers with search, pagination and stats
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1>Quản lý khách hàng</h1>
  <div class="d-flex">
    <?php if ($isAdmin): ?>
    <a href="index.php?page=create_customer" class="btn btn-success me-2">
      <i class="fas fa-user-plus me-1"></i> Thêm khách hàng
    </a>
    <?php endif; ?>
    <a href="index.php?page=export_csv" class="btn btn-secondary">
      <i class="fas fa-file-export me-1"></i> Xuất CSV
    </a>
  </div>
</div>

<!-- Form tìm kiếm -->
<div class="card mb-4">
  <div class="card-body">
    <form action="index.php" method="get" class="row g-2">
      <input type="hidden" name="page" value="dashboard">

      <div class="col-md-10">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="text" class="form-control" name="search"
            placeholder="Tìm kiếm theo tên, email hoặc số điện thoại..."
            value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        </div>
      </div>

      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
      </div>
    </form>
  </div>
</div>

<!-- Bảng danh sách khách hàng -->
<div class="card mb-4">
  <div class="card-header bg-light">
    <i class="fas fa-users me-1"></i> Danh sách khách hàng
  </div>
  <div class="card-body">
    <?php if (empty($customers)): ?>
    <div class="alert alert-info">Không có khách hàng nào được tìm thấy.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Avatar</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Số điện thoại</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $customer): ?>
          <tr>
            <td><?php echo $customer['id']; ?></td>
            <td>
              <?php if (!empty($customer['avatar'])): ?>
              <img src="<?php echo AVATAR_URL_PATH . htmlspecialchars($customer['avatar']); ?>" class="avatar-sm"
                alt="Avatar">
              <?php else: ?>
              <img src="assets\uploads\avatars\image.png" class="avatar-sm" alt="No Avatar">
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($customer['name']); ?></td>
            <td><?php echo htmlspecialchars($customer['email']); ?></td>
            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?></td>
            <td>
              <?php if ($isAdmin): ?>
              <a href="index.php?page=edit_customer&id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i>
              </a>
              <button type="button" class="btn btn-sm btn-danger delete-customer"
                data-id="<?php echo $customer['id']; ?>" data-csrf="<?php echo $_SESSION['csrf_token']; ?>"
                data-name="<?php echo htmlspecialchars($customer['name']); ?>">
                <i class="fas fa-trash"></i>
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>