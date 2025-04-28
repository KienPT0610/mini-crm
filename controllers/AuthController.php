<?php
/**
 * Authentication Controller
 * 
 * Handles user authentication and session management
 */
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    /**
     * Constructor - initialize User model
     */
    public function __construct() {
        $this->userModel = new User();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Process login form
     * 
     * @param string $username Username from login form
     * @param string $password Password from login form
     * @return bool Success or failure
     */
    public function login($username, $password) {
        // Trim and sanitize input
        $username = trim($username);
        
        // Basic validation
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Username and password are required";
            return false;
        }
        
        // Attempt authentication
        $user = $this->userModel->authenticate($username, $password);
        
        if (!$user) {
            $_SESSION['error'] = "Invalid username or password";
            return false;
        }
        
        // Set user session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Generate CSRF token for forms
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return true;
    }
    
    /**
     * Log out user
     */
    public function logout() {
        // Unset all session variables
        session_unset();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: index.php');
        exit;
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if user has admin role
     * 
     * @return bool True if admin, false otherwise
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token CSRF token from form
     * @return bool True if valid, false otherwise
     */
    public function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Require authentication
     * 
     * Redirects to login page if user is not logged in
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = "Please log in to access this page";
            header('Location: index.php');
            exit;
        }
    }
    
    /**
     * Require admin role
     * 
     * Redirects with error if user is not admin
     */
    public function requireAdmin() {
        $this->requireLogin();
        
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "You don't have permission to access this page";
            header('Location: dashboard.php');
            exit;
        }
    }
}
