<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

session_start(); // start session for dashboard login

// Include your config (adjust path)
include __DIR__ . '/../server/config.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "Username and password are required."
    ]);
    exit();
}

try {
    // Prepare and execute query safely
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Store session variables for server-side login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';

        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "redirect" => $_SESSION['role'] === 'admin' ? 'dashboard.php' : 'user-dashboard.php'
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid username or password"
        ]);
    }
} catch (Exception $e) {
    // Catch database errors
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>
