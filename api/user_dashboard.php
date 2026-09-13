<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once 'db_connect.php';

$action  = $_GET['action'] ?? 'get_data';
$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid user ID is required."]);
    exit();
}

// 1. Delete property action
if ($action === 'delete_property') {
    $property_id = intval($_POST['property_id'] ?? 0);
    if ($property_id <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Property ID is required."]);
        exit();
    }

    try {
        $delStmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND user_id = ?");
        $delStmt->execute([$property_id, $user_id]);
        echo json_encode(["status" => "success", "message" => "Property removed successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

// 2. Fetch user's uploaded properties and received inquiries
try {
    // Get user listings
    $propStmt = $pdo->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC");
    $propStmt->execute([$user_id]);
    $myProperties = $propStmt->fetchAll();

    // Get inquiries for this user's properties
    $enqStmt = $pdo->prepare("SELECT e.id, e.message, e.created_at, p.title AS property_title, u.name AS sender_name, u.email AS sender_email, u.phone AS sender_phone 
                              FROM enquiries e
                              JOIN properties p ON e.property_id = p.id
                              JOIN users u ON e.user_id = u.id
                              WHERE p.user_id = ?
                              ORDER BY e.created_at DESC");
    $enqStmt->execute([$user_id]);
    $inquiries = $enqStmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "properties" => $myProperties,
        "inquiries" => $inquiries
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>