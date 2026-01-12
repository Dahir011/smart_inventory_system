<?php
/**
 * AI Analytics Controller
 * Handles AI-powered analytics and intelligent insights endpoints
 * Provides stock predictions, usage analysis, and recommendations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
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

$action = $_GET['action'] ?? '';
$product_id = $_GET['product_id'] ?? null;

$aiAnalytics = new AIAnalytics($db);

switch ($action) {
    case 'daily-usage':
        // Get average daily usage for a product
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $days = $_GET['days'] ?? 30;
        $daily_usage = $aiAnalytics->calculateAverageDailyUsage($product_id, $days);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'product_id' => $product_id,
                'average_daily_usage' => $daily_usage,
                'period_days' => $days
            ]
        ]);
        break;

    case 'stockout-prediction':
        // Predict when stock will run out
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $prediction = $aiAnalytics->predictStockOutDate($product_id);
        
        if ($prediction === null) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'product_id' => $product_id,
                    'prediction' => null,
                    'message' => 'Insufficient data for prediction (no usage pattern found)'
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => [
                    'product_id' => $product_id,
                    'prediction' => $prediction
                ]
            ]);
        }
        break;

    case 'restock-recommendation':
        // Get intelligent restock recommendation
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $lead_time = $_GET['lead_time'] ?? 7;
        $safety_stock_percent = $_GET['safety_stock_percent'] ?? 20;
        
        $recommendation = $aiAnalytics->recommendRestockQuantity($product_id, $lead_time, $safety_stock_percent);
        
        if ($recommendation === null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'product_id' => $product_id,
                'recommendation' => $recommendation,
                'parameters' => [
                    'lead_time_days' => $lead_time,
                    'safety_stock_percentage' => $safety_stock_percent
                ]
            ]
        ]);
        break;

    case 'insights':
        // Get comprehensive AI insights for a product
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $insights = $aiAnalytics->getProductInsights($product_id);
        
        if (empty($insights['product'])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $insights
        ]);
        break;

    case 'fast-moving':
        // Get list of fast-moving products
        $limit = $_GET['limit'] ?? 10;
        $products = $aiAnalytics->getFastMovingProducts($limit);
        
        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
        break;

    case 'slow-moving':
        // Get list of slow-moving products
        $limit = $_GET['limit'] ?? 10;
        $products = $aiAnalytics->getSlowMovingProducts($limit);
        
        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
        break;

    case 'usage-trend':
        // Get usage trend data for a product (for charts)
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $days = $_GET['days'] ?? 30;
        $trend = $aiAnalytics->getProductUsageTrend($product_id, $days);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'product_id' => $product_id,
                'trend' => $trend,
                'period_days' => $days
            ]
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action. Available actions: daily-usage, stockout-prediction, restock-recommendation, insights, fast-moving, slow-moving, usage-trend',
            'available_actions' => [
                'daily-usage' => 'GET /api/ai-analytics?action=daily-usage&product_id={id}',
                'stockout-prediction' => 'GET /api/ai-analytics?action=stockout-prediction&product_id={id}',
                'restock-recommendation' => 'GET /api/ai-analytics?action=restock-recommendation&product_id={id}',
                'insights' => 'GET /api/ai-analytics?action=insights&product_id={id}',
                'fast-moving' => 'GET /api/ai-analytics?action=fast-moving&limit={limit}',
                'slow-moving' => 'GET /api/ai-analytics?action=slow-moving&limit={limit}',
                'usage-trend' => 'GET /api/ai-analytics?action=usage-trend&product_id={id}&days={days}'
            ]
        ]);
        break;
}
?>
