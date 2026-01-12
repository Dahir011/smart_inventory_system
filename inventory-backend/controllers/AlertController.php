<?php
/**
 * Alert Controller
 * Handles alerts and notifications
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Alert.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $alert = new Alert($db);
        $action = $_GET['action'] ?? '';

        if ($action === 'check') {
            // Check and generate new alerts
            $alert->checkAndGenerateAlerts();
            echo json_encode(['success' => true, 'message' => 'Alerts checked and generated']);
        } else {
            // Get all alerts
            $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';
            $alerts = $alert->getAll($unread_only);
            echo json_encode(['success' => true, 'data' => $alerts]);
        }
        break;

    case 'PUT':
        $alert = new Alert($db);
        $data = json_decode(file_get_contents("php://input"));
        $action = $_GET['action'] ?? '';

        if ($action === 'read') {
            // Mark single alert as read
            $alert_id = $_GET['id'] ?? $data->id ?? null;
            if ($alert->markAsRead($alert_id)) {
                echo json_encode(['success' => true, 'message' => 'Alert marked as read']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update alert']);
            }
        } elseif ($action === 'read-all') {
            // Mark all alerts as read
            if ($alert->markAllAsRead()) {
                echo json_encode(['success' => true, 'message' => 'All alerts marked as read']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update alerts']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
