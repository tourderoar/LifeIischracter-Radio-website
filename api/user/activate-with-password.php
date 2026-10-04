<?php
/**
 * Life is Character Radio - Activate Account with Password
 * Location: /api/user/activate-with-password.php
 * Method: POST
 * For guest donors to set password and activate account in one step
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$password = $input['password'] ?? '';

if (empty($token)) {
    jsonError('Activation token is required', 400);
}

if (empty($password)) {
    jsonError('Password is required', 400);
}

if (strlen($password) < 8) {
    jsonError('Password must be at least 8 characters long', 400);
}

// Validate password strength
if (!preg_match('/[A-Z]/', $password)) {
    jsonError('Password must contain at least one uppercase letter', 400);
}

if (!preg_match('/[a-z]/', $password)) {
    jsonError('Password must contain at least one lowercase letter', 400);
}

if (!preg_match('/[0-9]/', $password)) {
    jsonError('Password must contain at least one number', 400);
}

if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
    jsonError('Password must contain at least one special character', 400);
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
    
    // Hash the new password
    $hashedPassword = hashPassword($password);
    
    // Activate account and set password
    $stmt = $pdo->prepare("UPDATE users SET 
        password = ?, 
        is_active = 1, 
        activation_token = NULL, 
        activation_expires_at = NULL 
        WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);
    
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
    ], 'Account activated successfully! Welcome aboard!');
    
} catch (Exception $e) {
    error_log("Activation with password error: " . $e->getMessage());
    jsonError('Activation failed. Please try again.', 500);
}
