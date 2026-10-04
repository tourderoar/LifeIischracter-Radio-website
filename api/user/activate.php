<?php
/**
 * Life is Character Radio - Account Activation API
 * Location: /api/user/activate.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$token = $input['token'] ?? '';

if (empty($token)) {
    jsonError('Activation token is required', 400);
}

try {
    $pdo = getDB();
    
    // Find user with valid token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE activation_token = ? AND activation_expires_at > UTC_TIMESTAMP()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        jsonError('Invalid or expired activation link', 400);
    }
    
    // Activate account
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1, activation_token = NULL, activation_expires_at = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Auto-login - Set all required session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['is_user'] = true;
    
    jsonSuccess([
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name']
        ],
        'redirect' => BASE_URL . 'account/dashboard'
    ], 'Account activated successfully!');
    
} catch (Exception $e) {
    error_log("Activation error: " . $e->getMessage());
    jsonError('Activation failed. Please try again.', 500);
}
