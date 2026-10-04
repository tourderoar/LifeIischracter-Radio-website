<?php
/**
 * Life is Character Radio - Users API
 * 
 * Location: /api/admin/users.php
 * 
 * GET: List users
 * POST: Update user (activate/deactivate)
 * DELETE: Delete user (soft delete)
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_users');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    if ($method === 'GET' && isset($_GET['id'])) {
        // Single user detail with transaction history
        $id = (int)$_GET['id'];
        if (!$id) jsonError('User ID required', 400);

        $stmt = $pdo->prepare("SELECT id, email, full_name, phone, is_active, created_at, updated_at, last_login FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) jsonError('User not found', 404);

        $stmt = $pdo->prepare("SELECT id, transaction_type, amount, payment_method, payment_status, created_at, completed_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$id]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND payment_status = 'completed'");
        $stmt->execute([$id]);
        $totalSpent = (float)$stmt->fetchColumn();

        $user['transaction_count'] = count($transactions);
        $user['total_spent'] = $totalSpent;

        jsonSuccess(['user' => $user, 'transactions' => $transactions]);

    } elseif ($method === 'GET') {
        // List all users
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'all';
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $sql = "SELECT id, email, full_name, phone, is_active, created_at, last_login FROM users WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (email LIKE ? OR full_name LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($status !== 'all') {
            $sql .= " AND is_active = ?";
            $params[] = ($status === 'active') ? 1 : 0;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get transaction count for each user
        foreach ($users as &$user) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $user['transaction_count'] = (int)$stmt->fetchColumn();
        }
        
        $countSql = "SELECT COUNT(*) FROM users WHERE 1=1" . ($search ? " AND (email LIKE ? OR full_name LIKE ? OR phone LIKE ?)" : "") . ($status !== 'all' ? " AND is_active = " . (($status === 'active') ? 1 : 0) : "");
        $countParams = $search ? [$searchTerm, $searchTerm, $searchTerm] : [];
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetchColumn();
        
        jsonSuccess(['users' => $users, 'total' => $total]);
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        
        if (!$id) jsonError('User ID required', 400);
        
        if ($action === 'toggle_status') {
            $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current) jsonError('User not found', 404);
            
            $newStatus = $current['is_active'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);
            
            jsonSuccess(['message' => 'User status updated successfully', 'is_active' => $newStatus]);
        } else {
            jsonError('Invalid action', 400);
        }
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('User ID required', 400);
        
        // Soft delete by deactivating
        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'User deactivated successfully']);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Users API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
