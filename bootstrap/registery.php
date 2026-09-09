<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

include __DIR__ . '/../server/config.php';

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'];
$password = $data['password'];

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "All fields required"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "Username already exists"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
$success = $stmt->execute([$username, $hashedPassword]);

if ($success) {
    echo json_encode(["status" => "success", "message" => "Account created successfully "]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed"]);
}
?>
