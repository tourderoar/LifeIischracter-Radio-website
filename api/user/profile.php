<?php
/**
 * Life is Character Radio - User Profile API
 * Location: /api/user/profile.php
 * Methods: GET, PUT
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

// Check authentication
if (!isUserLoggedIn()) {
    jsonUnauthorized('Please login to access this page');
}

$userId = $_SESSION['user_id'];
$pdo = getDB();

// GET - Retrieve profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT id, email, full_name, phone, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            jsonError('User not found', 404);
        }
        
        jsonSuccess($user);
        
    } catch (Exception $e) {
        error_log("Get profile error: " . $e->getMessage());
        jsonError('Failed to load profile', 500);
    }
}

// PUT - Update profile
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $fullName = trim($input['full_name'] ?? '');
        $phone = trim($input['phone'] ?? '');
        
        // Validation
        if (empty($fullName)) {
            jsonError('Full name is required', 400);
        }
        
        // Update profile
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullName, $phone ?: null, $userId]);
        
        // Update session
        $_SESSION['user_name'] = $fullName;
        
        jsonSuccess([
            'full_name' => $fullName,
            'phone' => $phone
        ], 'Profile updated successfully');
        
    } catch (Exception $e) {
        error_log("Update profile error: " . $e->getMessage());
        jsonError('Failed to update profile', 500);
    }
}

else {
    jsonError('Method not allowed', 405);
}
