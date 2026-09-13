<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

$input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

$email        = trim($input['email'] ?? '');
$new_password = trim($input['new_password'] ?? '');

if (empty($email) || empty($new_password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email and new password are required."]);
    exit();
}

if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long."]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "No account found with this email address."]);
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->execute([$hashed_password, $user['id']]);

    echo json_encode(["status" => "success", "message" => "Password reset successfully! You can now login."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>