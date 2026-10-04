<?php
/**
 * Life is Character Radio - Prayer Requests API
 * 
 * Location: /api/admin/prayers.php
 * 
 * GET: List prayer requests
 * POST: Update request status
 * DELETE: Delete request
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

requireAdminAuthAPI();

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    if ($method === 'GET') {
        // List all prayer requests
        $status = $_GET['status'] ?? 'all';
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $sql = "SELECT * FROM prayer_requests WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(*) FROM prayer_requests WHERE 1=1" . ($status !== 'all' ? " AND status = ?" : "");
        $countParams = $status !== 'all' ? [$status] : [];
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetchColumn();
        
        jsonSuccess(['requests' => $requests, 'total' => $total]);
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (!$id) jsonError('Request ID required', 400);
        if (!in_array($status, ['new', 'praying', 'answered'])) jsonError('Invalid status', 400);
        
        $stmt = $pdo->prepare("UPDATE prayer_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        jsonSuccess(['message' => 'Status updated successfully']);
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonError('Request ID required', 400);

        $stmt = $pdo->prepare("DELETE FROM prayer_requests WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Request deleted successfully']);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Prayers API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
