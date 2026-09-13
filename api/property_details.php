<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

// Safe integer parsing
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;

if ($id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Valid Property ID is required."
    ]);
    exit();
}

try {
    // Explicit column selection ensures properties.id is never overwritten by users.id
    $sql = "SELECT 
                p.id,
                p.title,
                p.type,
                p.price,
                p.location,
                p.description,
                p.bedrooms,
                p.bathrooms,
                p.area,
                p.image,
                p.user_id,
                u.name AS owner_name,
                u.email AS owner_email,
                u.phone AS owner_phone
            FROM properties p 
            LEFT JOIN users u ON p.user_id = u.id 
            WHERE p.id = ? 
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($property) {
        // Fallback checks in PHP if DB contains empty strings instead of NULLs
        $property['owner_name']  = !empty(trim($property['owner_name'] ?? '')) ? $property['owner_name'] : 'Harsh Verma';
        $property['owner_email'] = !empty(trim($property['owner_email'] ?? '')) ? $property['owner_email'] : 'contact@harshrealestate.com';
        $property['owner_phone'] = !empty(trim($property['owner_phone'] ?? '')) ? $property['owner_phone'] : '+91 9876543210';
        $property['image']       = !empty(trim($property['image'] ?? '')) ? $property['image'] : 'default.jpg';

        http_response_code(200);
        echo json_encode([
            "status" => "success", 
            "data" => $property
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error", 
            "message" => "Property not found for ID: " . $id
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Database query error: " . $e->getMessage()
    ]);
}
?>