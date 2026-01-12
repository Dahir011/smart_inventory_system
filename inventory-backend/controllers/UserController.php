<?php
/**
 * User Management Controller
 * Handles user CRUD operations (Admin only)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Simple authentication check (in production, use JWT or session)
// For demo, we'll check for authorization header
$auth_user_id = null;
$auth_user_role = null;

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    // In a real app, decode JWT token here
    // For now, we'll use a simple header format: "Bearer user_id:role"
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

// Route handling
switch ($method) {
    case 'GET':
        if (empty($_GET['id'])) {
            // Get all users (Admin only)
            if ($auth_user_role !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }

            $user = new User($db);
            $users = $user->getAllUsers();
            echo json_encode(['success' => true, 'data' => $users]);
        } else {
            // Get single user
            $user = new User($db);
            $user_data = $user->getUserById($_GET['id']);
            if ($user_data) {
                echo json_encode(['success' => true, 'data' => $user_data]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
        }
        break;

    case 'PUT':
        if ($auth_user_role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        $user = new User($db);
        $user->id = $_GET['id'] ?? $data->id ?? null;
        $user->username = $data->username ?? '';
        $user->email = $data->email ?? '';
        $user->role = $data->role ?? 'staff';
        $user->full_name = $data->full_name ?? '';

        if ($user->update()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
        break;

    case 'DELETE':
        if ($auth_user_role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            exit;
        }

        $user = new User($db);
        $user->id = $_GET['id'] ?? null;

        if ($user->delete()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
