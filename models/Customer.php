<?php
/**
 * Customer Model
 * 
 * Handles all database operations related to customers
 */
require_once __DIR__ . '/../db/Database.php';

class Customer {
    private $db;
    
    /**
     * Constructor - initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new customer
     * 
     * @param array $data Customer data
     * @return int|false New customer ID or false on failure
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO customers (name, email, phone, address, avatar, created_at) 
            VALUES (:name, :email, :phone, :address, :avatar, NOW())
        ");
        
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $data['avatar'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Get customer by ID
     * 
     * @param int $id Customer ID
     * @return array|false Customer data or false if not found
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Update an existing customer
     * 
     * @param int $id Customer ID
     * @param array $data Customer data to update
     * @return bool Success or failure
     */
    public function update($id, $data) {
        $sql = "UPDATE customers SET 
                name = :name, 
                email = :email, 
                phone = :phone, 
                address = :address";
        
        // Only update avatar if provided
        if (!empty($data['avatar'])) {
            $sql .= ", avatar = :avatar";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if (!empty($data['avatar'])) {
            $stmt->bindParam(':avatar', $data['avatar'], PDO::PARAM_STR);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Delete a customer
     * 
     * @param int $id Customer ID
     * @return bool Success or failure
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get all customers with pagination
     * 
     * @param int $page Current page number
     * @param int $limit Items per page
     * @return array List of customers
     */
    public function getAllPaginated($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT * FROM customers
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count total customers
     * 
     * @return int Total customer count
     */
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM customers");
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Search customers by term
     * 
     * @param string $term Search term
     * @param int $page Current page number
     * @param int $limit Items per page
     * @return array Search results
     */
    public function search($term, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $searchTerm = '%' . $term . '%';
        
        $stmt = $this->db->prepare("
            SELECT * FROM customers
            WHERE name LIKE :term 
               OR email LIKE :term 
               OR phone LIKE :term
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindParam(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count search results
     * 
     * @param string $term Search term
     * @return int Total search results count
     */
    public function countSearchResults($term) {
        $searchTerm = '%' . $term . '%';
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM customers
            WHERE name LIKE :term 
               OR email LIKE :term 
               OR phone LIKE :term
        ");
        
        $stmt->bindParam(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Get all customers for CSV export
     * 
     * @return array All customers
     */
    public function getAllForExport() {
        $stmt = $this->db->query("
            SELECT id, name, email, phone, address, created_at 
            FROM customers 
            ORDER BY id ASC
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get monthly customer count for statistics
     * 
     * @param int $year Year to get statistics for
     * @return array Monthly statistics
     */
    public function getMonthlyStats($year = null) {
        $year = $year ?? date('Y');
        
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(created_at) as month,
                COUNT(*) as count
            FROM customers
            WHERE YEAR(created_at) = :year
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ");
        
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
