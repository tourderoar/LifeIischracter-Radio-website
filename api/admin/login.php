<?php
/**
 * Life is Character Radio - Admin Login API
 * 
 * Location: /api/admin/login.php
 * 
 * POST: Authenticate admin user
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!verifyCsrfToken($csrf_token)) {
        jsonError('Invalid security token. Please refresh and try again.', 403);
    }
    
    // Validate input
    if (empty($email) || empty($password)) {
        jsonError('Please enter both email and password.', 400);
    }
    
    if (!isValidEmail($email)) {
        jsonError('Invalid email format.', 400);
    }
    
    // Query database for admin
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT a.*, r.name as role_name, r.permissions 
        FROM admins a 
        JOIN roles r ON a.role_id = r.id 
        WHERE a.email = ? AND a.is_active = 1
    ");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify password
    if (!$admin || !verifyPassword($password, $admin['password'])) {
        jsonError('Invalid email or password.', 401);
    }
    
    // Set session
    $admin['permissions'] = json_decode($admin['permissions'], true) ?? [];
    setAdminSession($admin);
    
    // Update last login time
    $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW(), updated_at = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);
    
    jsonSuccess([
        'redirect' => BASE_URL . 'admin',
        'admin' => [
            'id' => $admin['id'],
            'full_name' => $admin['full_name'],
            'email' => $admin['email'],
            'role_name' => $admin['role_name']
        ]
    ], 'Login successful');
    
} catch (PDOException $e) {
    error_log('Admin login API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
