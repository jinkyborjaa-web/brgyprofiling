<?php
require_once __DIR__ . '/public-common.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$action = $_POST['action'] ?? '';
$returnTo = $_POST['return_to'] ?? 'user-dashboard.php';
if (!preg_match('/^[a-z0-9-]+(?:\.php)?(?:#[a-z0-9_-]+)?$/i', $returnTo)) {
    $returnTo = 'user-dashboard.php';
}

$reference = strtoupper($action === 'rsvp' ? 'RSVP' : 'ALT') . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$email = $_SESSION['username'] ?? '';
$message = 'Your request was submitted successfully. Reference number: ' . $reference;
$redirect = $returnTo;
$hash = '';
if (str_contains($redirect, '#')) {
    [$redirect, $hash] = explode('#', $redirect, 2);
    $hash = '#' . $hash;
}

try {
    if (!$publicPdo) {
        throw new RuntimeException('Database unavailable');
    }

    if ($action === 'rsvp') {
        $publicPdo->exec("CREATE TABLE IF NOT EXISTS public_rsvps (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, contact VARCHAR(255) NOT NULL, attendees INT NOT NULL, reference_no VARCHAR(40) NOT NULL UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $statement = $publicPdo->prepare('INSERT INTO public_rsvps (user_id, name, contact, attendees, reference_no) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([$_SESSION['user_id'], trim($_POST['name'] ?? ''), trim($_POST['contact'] ?? ''), max(1, (int)($_POST['attendees'] ?? 1)), $reference]);
        $email = trim($_POST['contact'] ?? $email);
    } elseif ($action === 'subscribe') {
        $publicPdo->exec("CREATE TABLE IF NOT EXISTS public_alert_subscriptions (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL UNIQUE, email VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $statement = $publicPdo->prepare('INSERT INTO public_alert_subscriptions (user_id, email) VALUES (?, ?) ON DUPLICATE KEY UPDATE email = VALUES(email)');
        $statement->execute([$_SESSION['user_id'], trim($_POST['email'] ?? $email)]);
        $message = 'You are now subscribed to barangay alerts.';
    } else {
        throw new InvalidArgumentException('Unsupported action');
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        @mail($email, 'Barangay San Isidro confirmation', $message, "Content-Type: text/plain; charset=UTF-8");
    }
    header('Location: ' . $redirect . '?reference=' . rawurlencode($reference) . ($hash ?: '#confirmation'));
} catch (Throwable $exception) {
    error_log('Public action error: ' . $exception->getMessage());
    header('Location: ' . $redirect . '?error=1' . ($hash ?: '#confirmation'));
}
exit();