<?php
/**
 * Mini CRM - Main entry point
 * 
 * This file handles authentication and routing for the entire application
 */
require_once 'config.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/CustomerController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controllers
$authController = new AuthController();
$customerController = new CustomerController();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($authController->login($username, $password)) {
        // Successful login, redirect to dashboard
        header('Location: index.php?page=dashboard');
        exit;
    }
    // On failure, the error message is set in the session by the login method
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $authController->logout();
    // logout() already handles redirection
}

// Check if user is logged in
$isLoggedIn = $authController->isLoggedIn();
$isAdmin = $authController->isAdmin();

// Basic routing based on page parameter
$page = $_GET['page'] ?? ($isLoggedIn ? 'dashboard' : 'login');

// Check permissions
if ($page !== 'login' && !$isLoggedIn) {
    $_SESSION['error'] = "Please log in to access this page";
    header('Location: index.php');
    exit;
}

// Admin-only pages
if (in_array($page, ['create_customer', 'edit_customer', 'delete_customer']) && !$isAdmin) {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle special actions
switch ($page) {
    case 'export_csv':
        // Only proceed if logged in
        $authController->requireLogin();
        if ($customerController->exportCsv()) {
            // This action outputs CSV directly and exits
            exit;
        }
        // If we get here, something went wrong
        header('Location: index.php?page=dashboard');
        exit;
        break;
        
    case 'delete_customer':
        // Only proceed if admin
        $authController->requireAdmin();
        
        if (isset($_GET['id']) && isset($_GET['csrf_token'])) {
            $id = (int)$_GET['id'];
            $token = $_GET['csrf_token'];
            
            $customerController->deleteCustomer($id, $token);
        }
        
        // Always redirect back to dashboard after delete attempt
        header('Location: index.php?page=dashboard');
        exit;
        break;
}

// Include page template based on routing
include 'views/partials/header.php';

switch ($page) {
    case 'login':
        include 'views/auth/login.php';
        break;
        
    case 'dashboard':
        // Get search term if provided
        $searchTerm = $_GET['search'] ?? '';
        $currentPage = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        
        if (!empty($searchTerm)) {
            // Search mode
            $data = $customerController->searchCustomers($searchTerm, $currentPage);
        } else {
            // Normal listing mode
            $data = $customerController->listCustomers($currentPage);
        }
        
        $customers = $data['customers'];
        $pagination = $data['pagination'];
        
        // Get monthly stats for admin dashboard
        if ($isAdmin) {
            $monthlyStats = $customerController->getMonthlyStats();
        }
        
        include 'views/customers/list.php';
        break;
        
    case 'create_customer':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'create_customer') {
                $customerId = $customerController->createCustomer($_POST, $_FILES);
                if ($customerId) {
                    // Success, redirect to dashboard
                    header('Location: index.php?page=dashboard');
                    exit;
                }
                // On failure, show the form again with errors displayed by the form
            }
        }
        include 'views/customers/add.php';
        break;
        
    case 'edit_customer':
        if (!isset($_GET['id'])) {
            $_SESSION['error'] = "Customer ID is required";
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        $id = (int)$_GET['id'];
        $customer = $customerController->getCustomer($id);
        
        if (!$customer) {
            $_SESSION['error'] = "Customer not found";
            header('Location: index.php?page=dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'update_customer') {
                if ($customerController->updateCustomer($id, $_POST, $_FILES)) {
                    // Success, redirect to dashboard
                    header('Location: index.php?page=dashboard');
                    exit;
                }
                // On failure, show the form again with errors displayed by the form
            }
        }
        
        include 'views/customers/edit.php';
        break;
        
    default:
        // 404 Page
        echo "<div class='container mt-5'><div class='alert alert-danger'>Page not found</div></div>";
        break;
}

include 'views/partials/footer.php';