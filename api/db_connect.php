<?php
header("Content-Type: application/json");
$host     = "sql202.infinityfree.com";
$username = "if0_42896388";
$password = "Harry933verma";
$database = "if0_42896388_realestate_db";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}
?>
