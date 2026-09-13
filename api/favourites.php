<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connect.php';

$action  = $_GET['action'] ?? 'get';
$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Valid User ID is required."]);
    exit();
}

// 1. Toggle favourite (Add or Remove)
if ($action === 'toggle') {
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
    $property_id = intval($input['property_id'] ?? 0);

    if ($property_id <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Property ID is required."]);
        exit();
    }

    try {
        // Check if already in favourites
        $check = $pdo->prepare("SELECT id FROM favourites WHERE user_id = ? AND property_id = ?");
        $check->execute([$user_id, $property_id]);
        $exists = $check->fetch();

        if ($exists) {
            $del = $pdo->prepare("DELETE FROM favourites WHERE id = ?");
            $del->execute([$exists['id']]);
            echo json_encode(["status" => "removed", "message" => "Removed from favourites."]);
        } else {
            $ins = $pdo->prepare("INSERT INTO favourites (user_id, property_id) VALUES (?, ?)");
            $ins->execute([$user_id, $property_id]);
            echo json_encode(["status" => "added", "message" => "Added to favourites!"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit();
}

// 2. Fetch all saved favourite properties for this user
try {
    $stmt = $pdo->prepare("SELECT p.*, f.id AS fav_id 
                           FROM favourites f 
                           JOIN properties p ON f.property_id = p.id 
                           WHERE f.user_id = ? 
                           ORDER BY f.created_at DESC");
    $stmt->execute([$user_id]);
    $favourites = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $favourites]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>