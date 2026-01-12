<?php
/**
 * Authentication Controller
 * Handles user login and registration
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Get JSON input
$data = json_decode(file_get_contents("php://input"));

// Route handling
switch ($method) {
    case 'POST':
        $action = $_GET['action'] ?? '';

        if ($action === 'login') {
            // Login
            $user = new User($db);
            $user->username = $data->username ?? '';
            $user->password = $data->password ?? '';

            if ($user->login()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'full_name' => $user->full_name
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password'
                ]);
            }
        } elseif ($action === 'register') {
            // Register
            $user = new User($db);
            $user->username = $data->username ?? '';
            $user->email = $data->email ?? '';
            $user->password = $data->password ?? '';
            $user->role = $data->role ?? 'staff';
            $user->full_name = $data->full_name ?? '';

            // Validate input
            if (empty($user->username) || empty($user->email) || empty($user->password)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Username, email, and password are required'
                ]);
                exit;
            }

            // Check if username exists
            if ($user->usernameExists()) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Username already exists'
                ]);
                exit;
            }

            // Check if email exists
            if ($user->emailExists()) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Email already exists'
                ]);
                exit;
            }

            if ($user->register()) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'data' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Registration failed'
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
