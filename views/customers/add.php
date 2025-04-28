<?php
/**
 * Add Customer Form
 * 
 * Form to create a new customer with validation
 */
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Thêm khách hàng mới</h1>
        <a href="index.php?page=dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-user-plus me-1"></i> Thông tin khách hàng mới
        </div>
        <div class="card-body">
            <form action="index.php?page=create_customer" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_customer">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        <?php if (isset($_SESSION['errors']['name'])): ?>
                            <div class="text-danger"><?php echo $_SESSION['errors']['name']; unset($_SESSION['errors']['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <?php if (isset($_SESSION['errors']['email'])): ?>
                            <div class="text-danger"><?php echo $_SESSION['errors']['email']; unset($_SESSION['errors']['email']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        <?php if (isset($_SESSION['errors']['phone'])): ?>
                            <div class="text-danger"><?php echo $_SESSION['errors']['phone']; unset($_SESSION['errors']['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="avatar" class="form-label">Avatar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/jpeg,image/png">
                        <small class="form-text text-muted">Chỉ chấp nhận file .jpg, .png, dung lượng tối đa 2MB</small>
                        <?php if (isset($_SESSION['errors']['avatar'])): ?>
                            <div class="text-danger"><?php echo $_SESSION['errors']['avatar']; unset($_SESSION['errors']['avatar']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    <?php if (isset($_SESSION['errors']['address'])): ?>
                        <div class="text-danger"><?php echo $_SESSION['errors']['address']; unset($_SESSION['errors']['address']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php?page=dashboard" class="btn btn-secondary me-md-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu khách hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>