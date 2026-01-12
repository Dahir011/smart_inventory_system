<?php
/**
 * Dashboard Controller
 * Provides dashboard statistics and analytics
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Alert.php';
require_once __DIR__ . '/../models/AIAnalytics.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_GET['action'] ?? 'stats';

switch ($action) {
    case 'stats':
        // Get basic dashboard statistics
        $product = new Product($db);
        $stats = $product->getDashboardStats();
        
        // Get unread alerts count
        $alert = new Alert($db);
        $stats['unread_alerts'] = $alert->getUnreadCount();
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;

    case 'categories':
        // Get products by category for chart
        $product = new Product($db);
        $categories = $product->getProductsByCategory();
        echo json_encode(['success' => true, 'data' => $categories]);
        break;

    case 'monthly-changes':
        // Get monthly inventory changes
        $ai = new AIAnalytics($db);
        $changes = $ai->getMonthlyInventoryChanges(6);
        echo json_encode(['success' => true, 'data' => $changes]);
        break;

    case 'fast-moving':
        // Get fast-moving products
        $ai = new AIAnalytics($db);
        $products = $ai->getFastMovingProducts(10);
        echo json_encode(['success' => true, 'data' => $products]);
        break;

    case 'slow-moving':
        // Get slow-moving products
        $ai = new AIAnalytics($db);
        $products = $ai->getSlowMovingProducts(10);
        echo json_encode(['success' => true, 'data' => $products]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
