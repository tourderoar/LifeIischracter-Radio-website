<?php
/**
 * Life is Character Radio - Forgot Password API
 * Location: /api/user/forgot-password.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = trim($input['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Invalid email address', 400);
}

try {
    $pdo = getDB();
    
    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Always return success for privacy (don't reveal if email exists)
    if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = gmdate('Y-m-d H:i:s', time() + 3600); // 1 hour (UTC)
        
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        // Send reset email
        sendPasswordResetEmail($user['email'], $user['full_name'], $token);
    }
    
    jsonSuccess(null, 'If your email is registered, you will receive a password reset link.');
    
} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    jsonError('Failed to process request. Please try again.', 500);
}
