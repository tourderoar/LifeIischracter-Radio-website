<?php
/**
 * Life is Character Radio - Newsletter Subscribers API
 * 
 * Location: /api/admin/newsletter.php
 * 
 * GET: List subscribers
 * POST: Toggle subscription status
 * DELETE: Delete subscriber
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
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'export') {
            // Export to CSV
            $stmt = $pdo->query("SELECT name, email, subscribed_at, is_active FROM newsletter_subscribers ORDER BY subscribed_at DESC");
            $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Name', 'Email', 'Subscribed At', 'Active']);
            
            foreach ($subscribers as $sub) {
                fputcsv($output, [
                    $sub['name'],
                    $sub['email'],
                    $sub['subscribed_at'],
                    $sub['is_active'] ? 'Yes' : 'No'
                ]);
            }
            
            fclose($output);
            exit;
        }
        
        // List all subscribers
        $status = $_GET['status'] ?? 'all';
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $sql .= " AND is_active = ?";
            $params[] = ($status === 'active') ? 1 : 0;
        }
        
        $sql .= " ORDER BY subscribed_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countSql = "SELECT COUNT(*) FROM newsletter_subscribers WHERE 1=1" . ($status !== 'all' ? " AND is_active = " . (($status === 'active') ? 1 : 0) : "");
        $total = $pdo->query($countSql)->fetchColumn();
        
        jsonSuccess(['subscribers' => $subscribers, 'total' => $total]);
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Subscriber ID required', 400);
        
        // Toggle active status
        $stmt = $pdo->prepare("SELECT is_active FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current) jsonError('Subscriber not found', 404);
        
        $newStatus = $current['is_active'] ? 0 : 1;
        $unsubscribed_at = $newStatus ? null : date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = ?, unsubscribed_at = ? WHERE id = ?");
        $stmt->execute([$newStatus, $unsubscribed_at, $id]);
        
        jsonSuccess(['message' => 'Subscription status updated successfully', 'is_active' => $newStatus]);
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonError('Subscriber ID required', 400);

        $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Subscriber deleted successfully']);
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Newsletter API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
