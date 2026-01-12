<?php
/**
 * Main API Router
 * Routes requests to appropriate controllers
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

// Get request URI and method
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = parse_url($request_uri, PHP_URL_PATH);
$uri = str_replace('/inventory-backend/api', '', $uri);
$uri = trim($uri, '/');

// Split URI into parts
$uri_parts = explode('/', $uri);
$endpoint = $uri_parts[0] ?? '';

// Route to appropriate controller
switch ($endpoint) {
    case 'auth':
        require_once __DIR__ . '/../controllers/AuthController.php';
        break;

    case 'users':
        require_once __DIR__ . '/../controllers/UserController.php';
        break;

    case 'products':
        require_once __DIR__ . '/../controllers/ProductController.php';
        break;

    case 'categories':
        require_once __DIR__ . '/../controllers/CategoryController.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/../controllers/DashboardController.php';
        break;

    case 'alerts':
        require_once __DIR__ . '/../controllers/AlertController.php';
        break;

    case 'ai-analytics':
        require_once __DIR__ . '/../controllers/AIAnalyticsController.php';
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'available_endpoints' => [
                '/api/auth',
                '/api/users',
                '/api/products',
                '/api/categories',
                '/api/dashboard',
                '/api/alerts',
                '/api/ai-analytics'
            ]
        ]);
        break;
}
?>
