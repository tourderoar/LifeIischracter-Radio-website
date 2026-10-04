<?php
/**
 * Life is Character Radio - Contact Submissions API
 * 
 * Location: /api/admin/contact.php
 * 
 * GET: List contact submissions
 * POST: Update submission status
 * DELETE: Delete submission
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
        // List all contact submissions
        $status = $_GET['status'] ?? 'all';
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $sql = "SELECT * FROM contact_submissions WHERE 1=1";
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
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(*) FROM contact_submissions WHERE 1=1" . ($status !== 'all' ? " AND status = ?" : "");
        $countParams = $status !== 'all' ? [$status] : [];
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetchColumn();
        
        jsonSuccess(['submissions' => $submissions, 'total' => $total]);
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (!$id) jsonError('Submission ID required', 400);
        if (!in_array($status, ['new', 'read', 'responded'])) jsonError('Invalid status', 400);
        
        $responded_at = ($status === 'responded') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("UPDATE contact_submissions SET status = ?, responded_at = ? WHERE id = ?");
        $stmt->execute([$status, $responded_at, $id]);
        
        jsonSuccess(['message' => 'Status updated successfully']);
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonError('Submission ID required', 400);

        $stmt = $pdo->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Submission deleted successfully']);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Contact API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
