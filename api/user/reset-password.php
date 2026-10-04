<?php
/**
 * Life is Character Radio - Reset Password API
 * Location: /api/user/reset-password.php
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
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

// Validation
$errors = [];
if (empty($token)) $errors['token'] = 'Reset token is required';

// Strong password validation
if (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors['password'] = 'Password must contain at least one lowercase letter';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one number';
} elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one special character';
}

if ($password !== $confirmPassword) $errors['confirm_password'] = 'Passwords do not match';

if (!empty($errors)) {
    jsonValidationError($errors);
}

try {
    $pdo = getDB();
    
    // Find user with valid token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires_at > UTC_TIMESTAMP()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        jsonError('Invalid or expired reset link', 400);
    }
    
    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
    $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    
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
    ], 'Password reset successfully!');
    
} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    jsonError('Password reset failed. Please try again.', 500);
}
