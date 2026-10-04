<?php
/**
 * Life is Character Radio - Admins API
 * 
 * Location: /api/admin/admins.php
 * 
 * GET: List admins
 * PUT: Create admin
 * POST: Update admin
 * DELETE: Delete admin
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_admins');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    // Check for method override
    if ($method === 'POST' && isset($_POST['_method'])) {
        if ($_POST['_method'] === 'PUT') {
            $method = 'PUT';
        } elseif ($_POST['_method'] === 'DELETE') {
            $method = 'DELETE';
        }
    }
    
    if ($method === 'GET') {
        // List all admins with roles
        $stmt = $pdo->query("SELECT a.id, a.username, a.email, a.full_name, a.is_active, a.created_at, r.id as role_id, r.name as role_name 
                             FROM admins a 
                             LEFT JOIN roles r ON a.role_id = r.id 
                             ORDER BY a.created_at DESC");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonSuccess(['admins' => $admins]);
        
    } elseif ($method === 'PUT') {
        validateCsrfTokenAPI();
        
        // Create new admin
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_id = (int)($_POST['role_id'] ?? 0);
        
        if (empty($username) || empty($email) || empty($full_name) || empty($password) || !$role_id) {
            jsonError('All fields are required', 400);
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Invalid email address', 400);
        }
        
        // Check username uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) jsonError('Username already exists', 400);
        
        // Check email uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) jsonError('Email already exists', 400);
        
        // Verify role exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE id = ?");
        $stmt->execute([$role_id]);
        if ($stmt->fetchColumn() == 0) jsonError('Invalid role', 400);
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, full_name, password, role_id, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$username, $email, $full_name, $hashedPassword, $role_id]);
        
        jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Admin created successfully']);
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        // Update admin
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Admin ID required', 400);
        
        // Cannot edit self
        if ($id == $_SESSION['admin_id']) {
            jsonError('Cannot edit your own account here. Use profile page.', 400);
        }
        
        $role_id = (int)($_POST['role_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (!$role_id) jsonError('Role is required', 400);
        
        // Verify role exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE id = ?");
        $stmt->execute([$role_id]);
        if ($stmt->fetchColumn() == 0) jsonError('Invalid role', 400);
        
        $stmt = $pdo->prepare("UPDATE admins SET role_id = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$role_id, $is_active, $id]);
        
        jsonSuccess(['message' => 'Admin updated successfully']);
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Admin ID required', 400);
        
        // Cannot delete self
        if ($id == $_SESSION['admin_id']) {
            jsonError('Cannot delete your own account', 400);
        }
        
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Admin deleted successfully']);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Admins API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
