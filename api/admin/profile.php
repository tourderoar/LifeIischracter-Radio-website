<?php
/**
 * Life is Character Radio - Admin Profile API
 * 
 * Location: /api/admin/profile.php
 * 
 * GET: Get admin profile data
 * PUT: Update admin profile
 * POST: Change password
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php'; // Includes auth.php and response.php

header('Content-Type: application/json');

requireAdminAuthAPI();

$method = $_SERVER['REQUEST_METHOD'];
$admin_id = $_SESSION['admin_id'];
$pdo = getDB();

try {
    if ($method === 'GET') {
        // Get admin profile
        $stmt = $pdo->prepare("
            SELECT a.id, a.username, a.email, a.full_name, a.is_active, 
                   a.created_at, a.updated_at, r.name as role_name 
            FROM admins a 
            JOIN roles r ON a.role_id = r.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            jsonError('Admin not found', 404);
        }
        
        jsonSuccess(['admin' => $admin]);
        
    } elseif ($method === 'PUT' || ($method === 'POST' && !isset($_POST['current_password']))) {
        // Update profile
        parse_str(file_get_contents('php://input'), $_PUT);
        $data = $method === 'PUT' ? $_PUT : $_POST;
        
        validateCsrfTokenAPI();
        
        $full_name = trim($data['full_name'] ?? '');
        $email = trim($data['email'] ?? '');
        
        if (empty($full_name) || empty($email)) {
            jsonError('Name and email are required', 400);
        }
        
        if (!isValidEmail($email)) {
            jsonError('Invalid email format', 400);
        }
        
        // Check if email exists for another admin
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->fetch()) {
            jsonError('Email already in use by another admin', 400);
        }
        
        $stmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $email, $admin_id]);
        
        $_SESSION['admin_name'] = $full_name;
        $_SESSION['admin_email'] = $email;
        
        jsonSuccess(['message' => 'Profile updated successfully']);
        
    } elseif ($method === 'POST' && isset($_POST['current_password'])) {
        // Change password
        validateCsrfTokenAPI();
        
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            jsonError('All password fields are required', 400);
        }
        
        if ($new_password !== $confirm_password) {
            jsonError('New passwords do not match', 400);
        }
        
        $passwordErrors = validatePasswordStrength($new_password);
        if (!empty($passwordErrors)) {
            jsonError(implode(' ', $passwordErrors), 400);
        }
        
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!verifyPassword($current_password, $admin['password'])) {
            jsonError('Current password is incorrect', 400);
        }
        
        $stmt = $pdo->prepare("UPDATE admins SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([hashPassword($new_password), $admin_id]);
        
        jsonSuccess(['message' => 'Password changed successfully']);
        
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Profile API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
