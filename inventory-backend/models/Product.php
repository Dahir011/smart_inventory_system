<?php
/**
 * Product Model
 * Handles product CRUD operations
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'products';

    public $id;
    public $name;
    public $category_id;
    public $quantity;
    public $min_stock_level;
    public $expiry_date;
    public $supplier_name;
    public $product_image;
    public $description;
    public $unit_price;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all products with optional filters
     */
    public function getAll($filters = []) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE 1=1";

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = :category_id";
        }
        if (!empty($filters['low_stock'])) {
            $query .= " AND p.quantity <= p.min_stock_level";
        }
        if (!empty($filters['near_expiry'])) {
            $query .= " AND p.expiry_date IS NOT NULL AND p.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        }
        if (!empty($filters['expired'])) {
            $query .= " AND p.expiry_date IS NOT NULL AND p.expiry_date < CURDATE()";
        }
        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE :search OR p.supplier_name LIKE :search)";
        }

        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Bind filter parameters
        if (!empty($filters['category_id'])) {
            $stmt->bindParam(":category_id", $filters['category_id']);
        }
        if (!empty($filters['search'])) {
            $search_term = "%" . $filters['search'] . "%";
            $stmt->bindParam(":search", $search_term);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get single product by ID
     */
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Create new product
     */
    public function create($user_id) {
        $query = "INSERT INTO " . $this->table . " 
                  SET name=:name, category_id=:category_id, quantity=:quantity, 
                      min_stock_level=:min_stock_level, expiry_date=:expiry_date, 
                      supplier_name=:supplier_name, product_image=:product_image,
                      description=:description, unit_price=:unit_price";

        $stmt = $this->conn->prepare($query);

        // Handle empty expiry_date
        $expiry_date = !empty($this->expiry_date) ? $this->expiry_date : null;

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":min_stock_level", $this->min_stock_level);
        $stmt->bindParam(":expiry_date", $expiry_date);
        $stmt->bindParam(":supplier_name", $this->supplier_name);
        $stmt->bindParam(":product_image", $this->product_image);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":unit_price", $this->unit_price);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Log stock movement
            $this->logStockMovement($user_id, 'add', $this->quantity, 0, $this->quantity);
            
            return true;
        }
        return false;
    }

    /**
     * Update product
     */
    public function update($user_id) {
        // Get current quantity for logging
        $current_product = $this->getById($this->id);
        $old_quantity = $current_product['quantity'];

        $query = "UPDATE " . $this->table . " 
                  SET name=:name, category_id=:category_id, quantity=:quantity, 
                      min_stock_level=:min_stock_level, expiry_date=:expiry_date, 
                      supplier_name=:supplier_name, product_image=:product_image,
                      description=:description, unit_price=:unit_price
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $expiry_date = !empty($this->expiry_date) ? $this->expiry_date : null;

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":min_stock_level", $this->min_stock_level);
        $stmt->bindParam(":expiry_date", $expiry_date);
        $stmt->bindParam(":supplier_name", $this->supplier_name);
        $stmt->bindParam(":product_image", $this->product_image);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            // Log stock movement if quantity changed
            $quantity_change = $this->quantity - $old_quantity;
            if ($quantity_change != 0) {
                $this->logStockMovement($user_id, 'update', $quantity_change, $old_quantity, $this->quantity);
            }
            return true;
        }
        return false;
    }

    /**
     * Delete product
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    /**
     * Log stock movement for AI analysis
     */
    private function logStockMovement($user_id, $action_type, $quantity_change, $quantity_before, $quantity_after) {
        $query = "INSERT INTO stock_logs 
                  SET product_id=:product_id, user_id=:user_id, action_type=:action_type,
                      quantity_change=:quantity_change, quantity_before=:quantity_before,
                      quantity_after=:quantity_after";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $this->id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":action_type", $action_type);
        $stmt->bindParam(":quantity_change", $quantity_change);
        $stmt->bindParam(":quantity_before", $quantity_before);
        $stmt->bindParam(":quantity_after", $quantity_after);
        $stmt->execute();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];

        // Total products
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_products'] = $stmt->fetch()['total'];

        // Low stock count
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE quantity <= min_stock_level";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['low_stock'] = $stmt->fetch()['total'];

        // Near expiry count (within 7 days)
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE expiry_date IS NOT NULL 
                  AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['near_expiry'] = $stmt->fetch()['total'];

        // Expired products count
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['expired'] = $stmt->fetch()['total'];

        return $stats;
    }

    /**
     * Get products by category (for charts)
     */
    public function getProductsByCategory() {
        $query = "SELECT c.name as category, COUNT(p.id) as count 
                  FROM categories c
                  LEFT JOIN " . $this->table . " p ON c.id = p.category_id
                  GROUP BY c.id, c.name
                  ORDER BY count DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
