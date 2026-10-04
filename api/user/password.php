<?php
/**
 * Life is Character Radio - Update Password API
 * Location: /api/user/password.php
 * Method: PUT
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonError('Method not allowed', 405);
}

// Check authentication
if (!isUserLoggedIn()) {
    jsonUnauthorized('Please login to access this page');
}

$userId = $_SESSION['user_id'];

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    if (empty($currentPassword)) $errors['current_password'] = 'Current password is required';
    
    // Strong password validation
    if (strlen($newPassword) < 8) {
        $errors['new_password'] = 'New password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $errors['new_password'] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $errors['new_password'] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $errors['new_password'] = 'Password must contain at least one number';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        $errors['new_password'] = 'Password must contain at least one special character';
    }
    
    if ($newPassword !== $confirmPassword) $errors['confirm_password'] = 'Passwords do not match';
    
    if (!empty($errors)) {
        jsonValidationError($errors);
    }
    
    $pdo = getDB();
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($currentPassword, $user['password'])) {
        jsonError('Current password is incorrect', 400);
    }
    
    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    
    jsonSuccess(null, 'Password updated successfully');
    
} catch (Exception $e) {
    error_log("Update password error: " . $e->getMessage());
    jsonError('Failed to update password', 500);
}
