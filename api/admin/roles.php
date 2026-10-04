<?php
/**
 * Life is Character Radio - Roles API
 * 
 * Location: /api/admin/roles.php
 * 
 * GET: List roles
 * PUT: Create role
 * POST: Update role
 * DELETE: Delete role
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_roles');

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
        // List all roles with admin count
        $stmt = $pdo->query("SELECT r.*, COUNT(a.id) as admin_count 
                             FROM roles r 
                             LEFT JOIN admins a ON r.id = a.role_id 
                             GROUP BY r.id 
                             ORDER BY r.created_at ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse JSON permissions
        foreach ($roles as &$role) {
            $role['permissions'] = json_decode($role['permissions'], true) ?: [];
        }
        
        jsonSuccess(['roles' => $roles]);
        
    } elseif ($method === 'PUT') {
        validateCsrfTokenAPI();
        
        // Create new role
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name)) jsonError('Role name is required', 400);
        if (empty($permissions)) jsonError('At least one permission is required', 400);
        
        // Check name uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) jsonError('Role name already exists', 400);
        
        $permissionsJson = json_encode($permissions);
        
        $stmt = $pdo->prepare("INSERT INTO roles (name, description, permissions, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$name, $description, $permissionsJson]);
        
        jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Role created successfully']);
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        // Update role
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Role ID required', 400);
        
        // Cannot edit Super Admin role
        $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role && $role['name'] === 'Super Admin') {
            jsonError('Cannot edit Super Admin role', 400);
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name)) jsonError('Role name is required', 400);
        if (empty($permissions)) jsonError('At least one permission is required', 400);
        
        // Check name uniqueness (excluding current)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetchColumn() > 0) jsonError('Role name already exists', 400);
        
        $permissionsJson = json_encode($permissions);
        
        $stmt = $pdo->prepare("UPDATE roles SET name = ?, description = ?, permissions = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $description, $permissionsJson, $is_active, $id]);
        
        jsonSuccess(['message' => 'Role updated successfully']);
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Role ID required', 400);
        
        // Cannot delete Super Admin role
        $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role && $role['name'] === 'Super Admin') {
            jsonError('Cannot delete Super Admin role', 400);
        }
        
        // Check if admins using this role
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE role_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            jsonError('Cannot delete role with admins assigned. Reassign admins first.', 400);
        }
        
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Role deleted successfully']);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Roles API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
