<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once 'db_connect.php';

$action = $_GET['action'] ?? 'get_all';

// 1. Delete user or property action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

    if ($action === 'delete_property') {
        $id = intval($input['property_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "Property listing removed by admin."]);
        exit();
    }

    if ($action === 'delete_user') {
        $id = intval($input['user_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "User account removed."]);
        exit();
    }
}

// 2. Fetch all system statistics and records
try {
    // Counts
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $propCount = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $enqCount  = $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();

    // Data lists
    $users = $pdo->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    
    $properties = $pdo->query("SELECT p.*, u.name AS owner_name 
                               FROM properties p 
                               JOIN users u ON p.user_id = u.id 
                               ORDER BY p.created_at DESC")->fetchAll();

    $enquiries = $pdo->query("SELECT e.id, e.message, e.created_at, p.title AS property_title, u.name AS sender_name, u.email AS sender_email 
                              FROM enquiries e 
                              JOIN properties p ON e.property_id = p.id 
                              JOIN users u ON e.user_id = u.id 
                              ORDER BY e.created_at DESC")->fetchAll();

    echo json_encode([
        "status" => "success",
        "stats" => [
            "total_users" => $userCount,
            "total_properties" => $propCount,
            "total_enquiries" => $enqCount
        ],
        "users" => $users,
        "properties" => $properties,
        "enquiries" => $enquiries
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>