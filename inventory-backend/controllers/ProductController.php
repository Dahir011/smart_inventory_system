<?php
/**
 * Product Controller
 * Handles product CRUD operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Product.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Get authenticated user info (simplified)
$auth_user_id = 1; // Default for demo
$auth_user_role = 'staff';

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    if (strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7);
        $parts = explode(':', $token);
        if (count($parts) === 2) {
            $auth_user_id = $parts[0];
            $auth_user_role = $parts[1];
        }
    }
}

switch ($method) {
    case 'GET':
        $product = new Product($db);
        
        if (!empty($_GET['id'])) {
            // Get single product
            $product_data = $product->getById($_GET['id']);
            if ($product_data) {
                echo json_encode(['success' => true, 'data' => $product_data]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
        } else {
            // Get all products with filters
            $filters = [];
            if (!empty($_GET['category_id'])) $filters['category_id'] = $_GET['category_id'];
            if (!empty($_GET['low_stock']) && $_GET['low_stock'] == '1') $filters['low_stock'] = true;
            if (!empty($_GET['near_expiry']) && $_GET['near_expiry'] == '1') $filters['near_expiry'] = true;
            if (!empty($_GET['expired']) && $_GET['expired'] == '1') $filters['expired'] = true;
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

            $products = $product->getAll($filters);
            echo json_encode(['success' => true, 'data' => $products]);
        }
        break;

    case 'POST':
        $product = new Product($db);
        $data = json_decode(file_get_contents("php://input"));

        $product->name = $data->name ?? '';
        $product->category_id = $data->category_id ?? 1;
        $product->quantity = $data->quantity ?? 0;
        $product->min_stock_level = $data->min_stock_level ?? 10;
        $product->expiry_date = $data->expiry_date ?? null;
        $product->supplier_name = $data->supplier_name ?? '';
        $product->product_image = $data->product_image ?? null;
        $product->description = $data->description ?? '';
        $product->unit_price = $data->unit_price ?? 0.00;

        if ($product->create($auth_user_id)) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => ['id' => $product->id]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Product creation failed']);
        }
        break;

    case 'PUT':
        $product = new Product($db);
        $data = json_decode(file_get_contents("php://input"));

        $product->id = $_GET['id'] ?? $data->id ?? null;
        $product->name = $data->name ?? '';
        $product->category_id = $data->category_id ?? 1;
        $product->quantity = $data->quantity ?? 0;
        $product->min_stock_level = $data->min_stock_level ?? 10;
        $product->expiry_date = $data->expiry_date ?? null;
        $product->supplier_name = $data->supplier_name ?? '';
        $product->product_image = $data->product_image ?? null;
        $product->description = $data->description ?? '';
        $product->unit_price = $data->unit_price ?? 0.00;

        if ($product->update($auth_user_id)) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Product update failed']);
        }
        break;

    case 'DELETE':
        $product = new Product($db);
        $product->id = $_GET['id'] ?? null;

        if ($product->delete()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Product deletion failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
