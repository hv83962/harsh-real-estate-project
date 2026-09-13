<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

$user_id     = intval($_POST['user_id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$type        = trim($_POST['type'] ?? 'Apartment');
$purpose     = trim($_POST['purpose'] ?? 'Buy');
$price       = trim($_POST['price'] ?? '');
$location    = trim($_POST['location'] ?? '');
$bedrooms    = intval($_POST['bedrooms'] ?? 0);
$bathrooms   = intval($_POST['bathrooms'] ?? 0);
$area        = intval($_POST['area'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($user_id <= 0 || empty($title) || empty($price) || empty($location)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Title, Price, and Location are mandatory."]);
    exit();
}

$imageName = 'default.jpg';

// Handle uploaded image file
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath   = $_FILES['image']['tmp_name'];
    $originalName  = $_FILES['image']['name'];
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($fileExtension, $allowedExtensions)) {
        $uploadFileDir = '../assets/images/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
        $destPath    = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $imageName = $newFileName;
        }
    }
}

try {
    $sql = "INSERT INTO properties (title, type, purpose, price, location, description, bedrooms, bathrooms, area, image, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $type, $purpose, $price, $location, $description, $bedrooms, $bathrooms, $area, $imageName, $user_id]);

    echo json_encode(["status" => "success", "message" => "Property listing published successfully!"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>