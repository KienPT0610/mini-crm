<?php
/**
 * Customer Controller
 * 
 * Handles all customer-related operations
 */
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class CustomerController {
    private $customerModel;
    private $authController;
    
    /**
     * Constructor - initialize models
     */
    public function __construct() {
        $this->customerModel = new Customer();
        $this->authController = new AuthController();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * List all customers with pagination
     * 
     * @param int $page Current page number
     * @return array Customers data and pagination info
     */
    public function listCustomers($page = 1) {
        // Ensure page is at least 1
        $page = max(1, (int)$page);
        
        // Get total customer count
        $totalCustomers = $this->customerModel->count();
        
        // Calculate total pages
        $totalPages = ceil($totalCustomers / ITEMS_PER_PAGE);
        
        // Ensure page doesn't exceed total pages
        $page = min($page, max(1, $totalPages));
        
        // Get customers for current page
        $customers = $this->customerModel->getAllPaginated($page);
        
        return [
            'customers' => $customers,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'hasPrev' => $page > 1,
                'hasNext' => $page < $totalPages,
                'totalItems' => $totalCustomers
            ]
        ];
    }
    
    /**
     * Search customers with pagination
     * 
     * @param string $term Search term
     * @param int $page Current page number
     * @return array Search results and pagination info
     */
    public function searchCustomers($term, $page = 1) {
        // Ensure page is at least 1
        $page = max(1, (int)$page);
        
        // Get total search results count
        $totalResults = $this->customerModel->countSearchResults($term);
        
        // Calculate total pages
        $totalPages = ceil($totalResults / ITEMS_PER_PAGE);
        
        // Ensure page doesn't exceed total pages
        $page = min($page, max(1, $totalPages));
        
        // Get search results for current page
        $customers = $this->customerModel->search($term, $page);
        
        return [
            'customers' => $customers,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'hasPrev' => $page > 1,
                'hasNext' => $page < $totalPages,
                'totalItems' => $totalResults
            ]
        ];
    }
    
    /**
     * Process customer creation form
     * 
     * @param array $data Form data
     * @param array $files File upload data
     * @return bool|int New customer ID or false on failure
     */
    public function createCustomer($data, $files = []) {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !$this->authController->verifyCsrfToken($data['csrf_token'])) {
            $_SESSION['error'] = "Invalid form submission";
            return false;
        }
        
        // Validate required fields
        if (empty($data['name'])) {
            $_SESSION['error'] = "Name is required";
            return false;
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format";
            return false;
        }
        
        // Process avatar upload if present
        $avatarPath = '';
        if (!empty($files['avatar']['name'])) {
            $avatarPath = $this->processAvatarUpload($files['avatar']);
            if ($avatarPath === false) {
                // Error already set in processAvatarUpload
                return false;
            }
        }
        
        // Prepare customer data
        $customerData = [
            'name' => htmlspecialchars($data['name']),
            'email' => !empty($data['email']) ? htmlspecialchars($data['email']) : '',
            'phone' => !empty($data['phone']) ? htmlspecialchars($data['phone']) : '',
            'address' => !empty($data['address']) ? htmlspecialchars($data['address']) : '',
            'avatar' => $avatarPath
        ];
        
        // Create customer
        $customerId = $this->customerModel->create($customerData);
        
        if (!$customerId) {
            $_SESSION['error'] = "Failed to create customer";
            return false;
        }
        
        $_SESSION['success'] = "Customer created successfully";
        return $customerId;
    }
    
    /**
     * Process customer update form
     * 
     * @param int $id Customer ID
     * @param array $data Form data
     * @param array $files File upload data
     * @return bool Success or failure
     */
    public function updateCustomer($id, $data, $files = []) {
        // Validate CSRF token
        if (!isset($data['csrf_token']) || !$this->authController->verifyCsrfToken($data['csrf_token'])) {
            $_SESSION['error'] = "Invalid form submission";
            return false;
        }
        
        // Validate required fields
        if (empty($data['name'])) {
            $_SESSION['error'] = "Name is required";
            return false;
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format";
            return false;
        }
        
        // Get existing customer to check if update is needed
        $customer = $this->customerModel->getById($id);
        if (!$customer) {
            $_SESSION['error'] = "Customer not found";
            return false;
        }
        
        // Process avatar upload if present
        $avatarPath = '';
        if (!empty($files['avatar']['name'])) {
            $avatarPath = $this->processAvatarUpload($files['avatar']);
            if ($avatarPath === false) {
                // Error already set in processAvatarUpload
                return false;
            }
        }
        
        // Prepare customer data
        $customerData = [
            'name' => htmlspecialchars($data['name']),
            'email' => !empty($data['email']) ? htmlspecialchars($data['email']) : '',
            'phone' => !empty($data['phone']) ? htmlspecialchars($data['phone']) : '',
            'address' => !empty($data['address']) ? htmlspecialchars($data['address']) : '',
        ];
        
        // Only update avatar if a new one was uploaded
        if (!empty($avatarPath)) {
            $customerData['avatar'] = $avatarPath;
            
            // Delete old avatar if exists and not default
            if (!empty($customer['avatar']) && file_exists(AVATAR_UPLOAD_PATH . basename($customer['avatar']))) {
                unlink(AVATAR_UPLOAD_PATH . basename($customer['avatar']));
            }
        }
        
        // Update customer
        $result = $this->customerModel->update($id, $customerData);
        
        if (!$result) {
            $_SESSION['error'] = "Failed to update customer";
            return false;
        }
        
        $_SESSION['success'] = "Customer updated successfully";
        return true;
    }
    
    /**
     * Process customer deletion
     * 
     * @param int $id Customer ID
     * @param string $token CSRF token
     * @return bool Success or failure
     */
    public function deleteCustomer($id, $token) {
        // Validate CSRF token
        if (!$this->authController->verifyCsrfToken($token)) {
            $_SESSION['error'] = "Invalid request";
            return false;
        }
        
        // Get customer to delete their avatar
        $customer = $this->customerModel->getById($id);
        if (!$customer) {
            $_SESSION['error'] = "Customer not found";
            return false;
        }
        
        // Delete customer's avatar if exists
        if (!empty($customer['avatar']) && file_exists(AVATAR_UPLOAD_PATH . basename($customer['avatar']))) {
            unlink(AVATAR_UPLOAD_PATH . basename($customer['avatar']));
        }
        
        // Delete customer
        $result = $this->customerModel->delete($id);
        
        if (!$result) {
            $_SESSION['error'] = "Failed to delete customer";
            return false;
        }
        
        $_SESSION['success'] = "Customer deleted successfully";
        return true;
    }
    
    /**
     * Get customer by ID
     * 
     * @param int $id Customer ID
     * @return array|false Customer data or false if not found
     */
    public function getCustomer($id) {
        return $this->customerModel->getById($id);
    }
    
    /**
     * Export customers to CSV
     * 
     * @return bool Success or failure
     */
    public function exportCsv() {
        // Get all customers for export
        $customers = $this->customerModel->getAllForExport();
        
        if (empty($customers)) {
            $_SESSION['error'] = "No customers to export";
            return false;
        }
        
        // CSV headers
        $headers = ['ID', 'Name', 'Email', 'Phone', 'Address', 'Created At'];
        
        // Create filename with date
        $filename = 'customers_' . date('Ymd') . '.csv';
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers to CSV
        fputcsv($output, $headers);
        
        // Add customer data to CSV
        foreach ($customers as $customer) {
            $row = [
                $customer['id'],
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['address'],
                $customer['created_at']
            ];
            fputcsv($output, $row);
        }
        
        // Close the output stream
        fclose($output);
        
        return true;
    }
    
    /**
     * Get monthly customer statistics
     * 
     * @param int $year Year to get statistics for
     * @return array Monthly statistics
     */
    public function getMonthlyStats($year = null) {
        return $this->customerModel->getMonthlyStats($year);
    }
    
    /**
     * Process avatar upload
     * 
     * @param array $file $_FILES['avatar'] data
     * @return string|false Path to saved avatar or false on failure
     */
    private function processAvatarUpload($file) {
        // Check if file was uploaded without errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
                UPLOAD_ERR_NO_FILE => "No file was uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
            ];
            
            $_SESSION['error'] = "Upload error: " . ($uploadErrors[$file['error']] ?? "Unknown error");
            return false;
        }
        
        // Check file size (max 2MB)
        if ($file['size'] > MAX_AVATAR_SIZE) {
            $_SESSION['error'] = "Avatar must be less than 2MB";
            return false;
        }
        
        // Get file info
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($mime, $allowedTypes)) {
            $_SESSION['error'] = "Only JPG and PNG files are allowed";
            return false;
        }
        
        // Create unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('avatar_') . '.' . $extension;
        $destination = AVATAR_UPLOAD_PATH . $filename;
        
        // Ensure upload directory exists
        if (!is_dir(AVATAR_UPLOAD_PATH)) {
            mkdir(AVATAR_UPLOAD_PATH, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['error'] = "Failed to save avatar";
            return false;
        }
        
        // Resize image
        $this->resizeImage($destination, MAX_AVATAR_WIDTH, MAX_AVATAR_HEIGHT);
        
        return $filename;
    }
    
    /**
     * Resize an image to specified dimensions
     * 
     * @param string $imagePath Path to image
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return bool Success or failure
     */
    private function resizeImage($imagePath, $maxWidth, $maxHeight) {
        // Get image type
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        $mime = $imageInfo['mime'];
        
        // Create image resource based on type
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Calculate new dimensions while maintaining aspect ratio
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            // Create new image with calculated dimensions
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG images
            if ($mime === 'image/png') {
                imagecolortransparent($newImage, imagecolorallocate($newImage, 0, 0, 0));
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }
            
            // Resize image
            imagecopyresampled(
                $newImage, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight, $width, $height
            );
            
            // Save resized image
            switch ($mime) {
                case 'image/jpeg':
                    imagejpeg($newImage, $imagePath, 90);
                    break;
                case 'image/png':
                    imagepng($newImage, $imagePath, 9);
                    break;
            }
            
            // Free memory
            imagedestroy($newImage);
        }
        
        imagedestroy($image);
        
        return true;
    }
}
