<?php
/**
 * User Model
 * 
 * Handles all database operations related to users
 */
require_once __DIR__ . '/../db/Database.php';

class User {
    private $db;
    
    /**
     * Constructor - initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find user by username
     * 
     * @param string $username Username to find
     * @return array|false User data or false if not found
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Authenticate user with username and password
     * 
     * @param string $username Username for authentication
     * @param string $password Plain text password to verify
     * @return array|false User data if authenticated, false otherwise
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create a new user
     * 
     * @param string $username Username for new user
     * @param string $password Plain text password (will be hashed)
     * @param string $role User role (admin or staff)
     * @return int|false New user ID or false on failure
     */
    public function create($username, $password, $role) {
        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, password, role, created_at) 
            VALUES (:username, :password, :role, NOW())
        ");
        
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Get all users from database
     * 
     * @return array List of users
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
        return $stmt->fetchAll();
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID to update
     * @param string $newPassword New plain text password
     * @return bool Success or failure
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
