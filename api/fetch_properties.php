<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once 'db_connect.php';

// 1. Read query parameters from URL GET request
$location  = trim($_GET['location'] ?? '');
$type      = trim($_GET['type'] ?? '');
$max_price = trim($_GET['max_price'] ?? '');
$bedrooms  = trim($_GET['bedrooms'] ?? '');

// 2. Base SQL Query joining properties with the users table
$sql = "SELECT p.*, u.name AS owner_name, u.phone AS owner_phone 
        FROM properties p 
        JOIN users u ON p.user_id = u.id 
        WHERE 1=1";
$params = [];

// 3. Dynamically append filters if provided
if (!empty($location)) {
    $sql .= " AND p.location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($type)) {
    $sql .= " AND p.type = ?";
    $params[] = $type;
}

if (!empty($max_price) && is_numeric($max_price)) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

if (!empty($bedrooms) && is_numeric($bedrooms)) {
    $sql .= " AND p.bedrooms >= ?";
    $params[] = $bedrooms;
}

// 4. Order latest properties first
$sql .= " ORDER BY p.created_at DESC";

// 5. Execute prepared statement and return JSON
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "count"  => count($properties),
        "data"   => $properties
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>