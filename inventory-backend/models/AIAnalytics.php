<?php
/**
 * AI Analytics Model
 * Implements AI-inspired intelligent features
 * Uses statistical analysis and historical data patterns
 */

require_once __DIR__ . '/../config/database.php';

class AIAnalytics {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Calculate average daily usage for a product
     * AI Logic: Analyzes stock_logs to determine usage patterns
     */
    public function calculateAverageDailyUsage($product_id, $days = 30) {
        $query = "SELECT 
                    SUM(CASE WHEN action_type = 'remove' OR action_type = 'sold' THEN ABS(quantity_change) ELSE 0 END) as total_removed,
                    COUNT(DISTINCT DATE(created_at)) as active_days
                  FROM stock_logs 
                  WHERE product_id = :product_id 
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        $result = $stmt->fetch();

        $total_removed = $result['total_removed'] ?? 0;
        $active_days = $result['active_days'] ?? 1;

        // Calculate average daily usage
        // If no active days, return 0
        if ($active_days == 0) {
            return 0;
        }

        return round($total_removed / $active_days, 2);
    }

    /**
     * Predict when stock will run out
     * AI Logic: Uses current quantity and average daily usage
     */
    public function predictStockOutDate($product_id) {
        // Get current product quantity
        $query = "SELECT quantity FROM products WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $current_quantity = $product['quantity'];
        $avg_daily_usage = $this->calculateAverageDailyUsage($product_id);

        // If no usage pattern, return null
        if ($avg_daily_usage <= 0) {
            return null;
        }

        // Calculate days until stockout
        $days_until_stockout = floor($current_quantity / $avg_daily_usage);
        $predicted_date = date('Y-m-d', strtotime("+{$days_until_stockout} days"));

        return [
            'days_remaining' => $days_until_stockout,
            'predicted_date' => $predicted_date,
            'average_daily_usage' => $avg_daily_usage
        ];
    }

    /**
     * Recommend restock quantity
     * AI Logic: Considers average usage, lead time, and safety stock
     */
    public function recommendRestockQuantity($product_id, $lead_time_days = 7, $safety_stock_percentage = 20) {
        $avg_daily_usage = $this->calculateAverageDailyUsage($product_id);
        
        // Get current quantity and min stock level
        $query = "SELECT quantity, min_stock_level FROM products WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        $current_quantity = $product['quantity'];
        $min_stock_level = $product['min_stock_level'];

        // Calculate recommended quantity
        // Formula: (Average Daily Usage × Lead Time) + Safety Stock - Current Quantity
        $usage_during_lead_time = $avg_daily_usage * $lead_time_days;
        $safety_stock = max($usage_during_lead_time * ($safety_stock_percentage / 100), $min_stock_level);
        $recommended_quantity = ceil($usage_during_lead_time + $safety_stock - $current_quantity);

        // Ensure minimum restock quantity
        $recommended_quantity = max($recommended_quantity, $min_stock_level - $current_quantity);

        return [
            'recommended_quantity' => max(0, $recommended_quantity),
            'reasoning' => "Based on average daily usage of {$avg_daily_usage} units, {$lead_time_days}-day lead time, and {$safety_stock_percentage}% safety stock buffer."
        ];
    }

    /**
     * Identify fast-moving products
     * AI Logic: Products with high usage rate relative to their stock
     */
    public function getFastMovingProducts($limit = 10) {
        $query = "SELECT 
                    p.id,
                    p.name,
                    p.quantity,
                    SUM(CASE WHEN sl.action_type = 'remove' OR sl.action_type = 'sold' THEN ABS(sl.quantity_change) ELSE 0 END) as total_usage,
                    COUNT(DISTINCT DATE(sl.created_at)) as active_days,
                    COUNT(sl.id) as movement_count
                  FROM products p
                  LEFT JOIN stock_logs sl ON p.id = sl.product_id 
                    AND sl.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  GROUP BY p.id, p.name, p.quantity
                  HAVING total_usage > 0
                  ORDER BY total_usage DESC, movement_count DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Identify slow-moving products
     * AI Logic: Products with low or no usage despite having stock
     */
    public function getSlowMovingProducts($limit = 10) {
        $query = "SELECT 
                    p.id,
                    p.name,
                    p.quantity,
                    COALESCE(SUM(CASE WHEN sl.action_type = 'remove' OR sl.action_type = 'sold' THEN ABS(sl.quantity_change) ELSE 0 END), 0) as total_usage,
                    COUNT(sl.id) as movement_count,
                    DATEDIFF(CURDATE(), MAX(COALESCE(sl.created_at, p.created_at))) as days_since_last_movement
                  FROM products p
                  LEFT JOIN stock_logs sl ON p.id = sl.product_id 
                    AND sl.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                  WHERE p.quantity > 0
                  GROUP BY p.id, p.name, p.quantity
                  HAVING total_usage = 0 OR days_since_last_movement > 30
                  ORDER BY days_since_last_movement DESC, total_usage ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get product usage trend (for charts)
     */
    public function getProductUsageTrend($product_id, $days = 30) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    SUM(CASE WHEN action_type = 'add' THEN quantity_change ELSE 0 END) as added,
                    SUM(CASE WHEN action_type = 'remove' OR action_type = 'sold' THEN ABS(quantity_change) ELSE 0 END) as removed
                  FROM stock_logs
                  WHERE product_id = :product_id
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get monthly inventory changes (for dashboard chart)
     */
    public function getMonthlyInventoryChanges($months = 6) {
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(CASE WHEN action_type = 'add' THEN quantity_change ELSE 0 END) as added,
                    SUM(CASE WHEN action_type = 'remove' OR action_type = 'sold' THEN ABS(quantity_change) ELSE 0 END) as removed,
                    COUNT(DISTINCT product_id) as products_affected
                  FROM stock_logs
                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":months", $months);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get comprehensive AI insights for a product
     */
    public function getProductInsights($product_id) {
        $insights = [];

        // Basic product info
        $query = "SELECT * FROM products WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();
        $insights['product'] = $stmt->fetch();

        // Average daily usage
        $insights['average_daily_usage'] = $this->calculateAverageDailyUsage($product_id);

        // Stockout prediction
        $insights['stockout_prediction'] = $this->predictStockOutDate($product_id);

        // Restock recommendation
        $insights['restock_recommendation'] = $this->recommendRestockQuantity($product_id);

        // Usage trend
        $insights['usage_trend'] = $this->getProductUsageTrend($product_id);

        // Movement classification
        $avg_usage = $insights['average_daily_usage'];
        if ($avg_usage > 5) {
            $insights['movement_classification'] = 'fast_moving';
        } elseif ($avg_usage > 0) {
            $insights['movement_classification'] = 'normal';
        } else {
            $insights['movement_classification'] = 'slow_moving';
        }

        return $insights;
    }
}
?>
