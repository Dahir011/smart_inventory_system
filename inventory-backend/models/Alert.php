<?php
/**
 * Alert Model
 * Handles alerts and notifications
 */

require_once __DIR__ . '/../config/database.php';

class Alert {
    private $conn;
    private $table = 'alerts';

    public $id;
    public $product_id;
    public $alert_type;
    public $message;
    public $is_read;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create alert
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET product_id=:product_id, alert_type=:alert_type, message=:message";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":alert_type", $this->alert_type);
        $stmt->bindParam(":message", $this->message);
        return $stmt->execute();
    }

    /**
     * Get all alerts
     */
    public function getAll($unread_only = false) {
        $query = "SELECT a.*, p.name as product_name 
                  FROM " . $this->table . " a
                  LEFT JOIN products p ON a.product_id = p.id
                  WHERE 1=1";

        if ($unread_only) {
            $query .= " AND a.is_read = 0";
        }

        $query .= " ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark alert as read
     */
    public function markAsRead($id) {
        $query = "UPDATE " . $this->table . " SET is_read = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Mark all alerts as read
     */
    public function markAllAsRead() {
        $query = "UPDATE " . $this->table . " SET is_read = 1 WHERE is_read = 0";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    /**
     * Check and generate alerts for products
     */
    public function checkAndGenerateAlerts() {
        // Check for low stock
        $query = "SELECT id, name, quantity, min_stock_level 
                  FROM products 
                  WHERE quantity <= min_stock_level";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $low_stock_products = $stmt->fetchAll();

        foreach ($low_stock_products as $product) {
            // Check if alert already exists
            $check_query = "SELECT id FROM " . $this->table . " 
                           WHERE product_id = :product_id 
                           AND alert_type = 'low_stock' 
                           AND is_read = 0 
                           AND DATE(created_at) = CURDATE()";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":product_id", $product['id']);
            $check_stmt->execute();

            if ($check_stmt->rowCount() == 0) {
                $this->product_id = $product['id'];
                $this->alert_type = 'low_stock';
                $this->message = $product['name'] . " is running low. Current stock: " . $product['quantity'] . " (Minimum: " . $product['min_stock_level'] . ")";
                $this->create();
            }
        }

        // Check for near expiry (within 7 days)
        $query = "SELECT id, name, expiry_date 
                  FROM products 
                  WHERE expiry_date IS NOT NULL 
                  AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                  AND expiry_date >= CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $near_expiry_products = $stmt->fetchAll();

        foreach ($near_expiry_products as $product) {
            $check_query = "SELECT id FROM " . $this->table . " 
                           WHERE product_id = :product_id 
                           AND alert_type = 'near_expiry' 
                           AND is_read = 0 
                           AND DATE(created_at) = CURDATE()";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":product_id", $product['id']);
            $check_stmt->execute();

            if ($check_stmt->rowCount() == 0) {
                $days_remaining = ceil((strtotime($product['expiry_date']) - time()) / (60 * 60 * 24));
                $this->product_id = $product['id'];
                $this->alert_type = 'near_expiry';
                $this->message = $product['name'] . " will expire in " . $days_remaining . " day(s). Expiry date: " . $product['expiry_date'];
                $this->create();
            }
        }

        // Check for expired products
        $query = "SELECT id, name, expiry_date 
                  FROM products 
                  WHERE expiry_date IS NOT NULL 
                  AND expiry_date < CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $expired_products = $stmt->fetchAll();

        foreach ($expired_products as $product) {
            $check_query = "SELECT id FROM " . $this->table . " 
                           WHERE product_id = :product_id 
                           AND alert_type = 'expired' 
                           AND is_read = 0 
                           AND DATE(created_at) = CURDATE()";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":product_id", $product['id']);
            $check_stmt->execute();

            if ($check_stmt->rowCount() == 0) {
                $this->product_id = $product['id'];
                $this->alert_type = 'expired';
                $this->message = $product['name'] . " has expired on " . $product['expiry_date'];
                $this->create();
            }
        }

        return true;
    }

    /**
     * Get unread alerts count
     */
    public function getUnreadCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch()['count'];
    }
}
?>
