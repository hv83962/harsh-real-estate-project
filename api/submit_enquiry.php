<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db_connect.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);
if (!$data) {
    $data = $_POST;
}

$user_id     = intval($data['user_id'] ?? 0);
$property_id = intval($data['property_id'] ?? 0);
$message     = trim($data['message'] ?? '');

if ($user_id <= 0 || $property_id <= 0 || empty($message)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "User, property ID, and enquiry message are required."]);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO enquiries (user_id, property_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $property_id, $message]);

    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Your enquiry has been sent to the property owner!"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>