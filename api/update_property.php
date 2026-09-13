<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

$property_id = intval($_POST['property_id'] ?? 0);
$user_id     = intval($_POST['user_id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$type        = trim($_POST['type'] ?? '');
$price       = trim($_POST['price'] ?? '');
$location    = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');
$bedrooms    = intval($_POST['bedrooms'] ?? 0);
$bathrooms   = intval($_POST['bathrooms'] ?? 0);
$area        = intval($_POST['area'] ?? 0);

if ($property_id <= 0 || $user_id <= 0 || empty($title) || empty($price)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Property ID, user ID, title, and price are required."]);
    exit();
}

try {
    // Verify ownership or admin
    $check = $pdo->prepare("SELECT id, user_id FROM properties WHERE id = ?");
    $check->execute([$property_id]);
    $prop = $check->fetch();

    if (!$prop || intval($prop['user_id']) !== $user_id) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Unauthorized to modify this property."]);
        exit();
    }

    $sql = "UPDATE properties SET title = ?, type = ?, price = ?, location = ?, description = ?, bedrooms = ?, bathrooms = ?, area = ?";
    $params = [$title, $type, $price, $location, $description, $bedrooms, $bathrooms, $area];

    // Optional image update
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_img = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_img);
        $sql .= ", image = ?";
        $params[] = $new_img;
    }

    $sql .= " WHERE id = ?";
    $params[] = $property_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["status" => "success", "message" => "Property updated successfully!"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>