<?php
/**
 * Category Controller
 * Handles category CRUD operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Category.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $category = new Category($db);
        
        if (!empty($_GET['id'])) {
            $category_data = $category->getById($_GET['id']);
            if ($category_data) {
                echo json_encode(['success' => true, 'data' => $category_data]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Category not found']);
            }
        } else {
            $categories = $category->getAll();
            echo json_encode(['success' => true, 'data' => $categories]);
        }
        break;

    case 'POST':
        $category = new Category($db);
        $data = json_decode(file_get_contents("php://input"));

        $category->name = $data->name ?? '';
        $category->description = $data->description ?? '';

        if ($category->create()) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Category created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Category creation failed']);
        }
        break;

    case 'PUT':
        $category = new Category($db);
        $data = json_decode(file_get_contents("php://input"));

        $category->id = $_GET['id'] ?? $data->id ?? null;
        $category->name = $data->name ?? '';
        $category->description = $data->description ?? '';

        if ($category->update()) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Category update failed']);
        }
        break;

    case 'DELETE':
        $category = new Category($db);
        $category->id = $_GET['id'] ?? null;

        if ($category->delete()) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Category deletion failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
