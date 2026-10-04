<?php
/**
 * Life is Character Radio - Donations API
 * 
 * Location: /api/admin/donations.php
 * 
 * GET: List donation types and donations
 * POST: Create donation type
 * PUT: Update donation type
 * DELETE: Delete donation type (soft delete)
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_donations');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    // Check for method override first
    if ($method === 'POST' && isset($_POST['_method'])) {
        if ($_POST['_method'] === 'PUT') {
            $method = 'PUT';
        } elseif ($_POST['_method'] === 'DELETE') {
            $method = 'DELETE';
        }
    }
    
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'types';
        
        if ($action === 'types') {
            // List donation types
            $stmt = $pdo->query("SELECT * FROM donation_types ORDER BY sort_order ASC");
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonSuccess(['types' => $types]);
            
        } elseif ($action === 'donations') {
            // List all donations with filters
            $type_id = $_GET['type_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $limit = min((int)($_GET['limit'] ?? 50), 100);
            $offset = max((int)($_GET['offset'] ?? 0), 0);
            
            $sql = "SELECT d.*, dt.name as donation_type_name, u.full_name as donor_name
                    FROM donations d
                    LEFT JOIN donation_types dt ON d.donation_type_id = dt.id
                    LEFT JOIN users u ON d.user_id = u.id
                    WHERE 1=1";
            $params = [];
            
            if ($type_id) {
                $sql .= " AND d.donation_type_id = ?";
                $params[] = $type_id;
            }
            
            if ($status) {
                $sql .= " AND d.payment_status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY d.donated_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM donations WHERE 1=1";
            $countParams = [];
            if ($type_id) {
                $countSql .= " AND donation_type_id = ?";
                $countParams[] = $type_id;
            }
            if ($status) {
                $countSql .= " AND payment_status = ?";
                $countParams[] = $status;
            }
            
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetchColumn();
            
            // Get total revenue
            $revenueSql = "SELECT SUM(amount) FROM donations WHERE payment_status = 'completed'";
            $revenueStmt = $pdo->query($revenueSql);
            $totalRevenue = $revenueStmt->fetchColumn() ?: 0;
            
            jsonSuccess(['donations' => $donations, 'total' => $total, 'totalRevenue' => $totalRevenue]);
        } else {
            jsonError('Invalid action', 400);
        }
        
    } elseif ($method === 'DELETE') {
        // Delete donation type (soft delete - set inactive)
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Donation type ID required', 400);
        
        // Check if donations exist for this type
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE donation_type_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            jsonError('Cannot delete donation type with existing donations. Deactivate instead.', 400);
        }
        
        $stmt = $pdo->prepare("UPDATE donation_types SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Donation type deactivated successfully']);
        
    } elseif ($method === 'PUT') {
        // Update donation type
        validateCsrfTokenAPI();
        $data = $_POST;
        
        $id = (int)($data['id'] ?? 0);
        if (!$id) jsonError('Donation type ID required', 400);
        
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $suggested_amount = !empty($data['suggested_amount']) ? (float)$data['suggested_amount'] : null;
        $sort_order = (int)($data['sort_order'] ?? 0);
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        
        if (empty($name)) jsonError('Name is required', 400);
        
        $stmt = $pdo->prepare("UPDATE donation_types SET name = ?, description = ?, suggested_amount = ?, sort_order = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $description, $suggested_amount, $sort_order, $is_active, $id]);
        
        jsonSuccess(['message' => 'Donation type updated successfully']);
        
    } elseif ($method === 'POST') {
        // Create donation type
        validateCsrfTokenAPI();
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $suggested_amount = !empty($_POST['suggested_amount']) ? (float)$_POST['suggested_amount'] : null;
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        
        if (empty($name)) jsonError('Name is required', 400);
        
        $stmt = $pdo->prepare("INSERT INTO donation_types (name, description, suggested_amount, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $suggested_amount, $sort_order, $is_active]);
        
        jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Donation type created successfully']);
        
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Donations API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
