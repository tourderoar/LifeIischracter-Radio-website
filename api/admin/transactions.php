<?php
/**
 * Life is Character Radio - Transactions API
 * 
 * Location: /api/admin/transactions.php
 * 
 * GET: List all transactions
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
            $sql = "SELECT t.*, u.email as user_email, u.full_name as user_name 
                    FROM transactions t 
                    LEFT JOIN users u ON t.user_id = u.id 
                    ORDER BY t.created_at DESC";
            $stmt = $pdo->query($sql);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Type', 'User', 'Amount', 'Status', 'Payment Method', 'Created At', 'Completed At']);
            
            foreach ($transactions as $txn) {
                fputcsv($output, [
                    $txn['id'],
                    $txn['transaction_type'],
                    $txn['user_name'] ?: 'Anonymous',
                    '$' . number_format($txn['amount'], 2),
                    $txn['payment_status'],
                    $txn['payment_method'],
                    $txn['created_at'],
                    $txn['completed_at']
                ]);
            }
            
            fclose($output);
            exit;
        }
        
        // List all transactions
        $type = $_GET['type'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $sql = "SELECT t.*, u.email as user_email, u.full_name as user_name 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if ($type !== 'all') {
            $sql .= " AND t.transaction_type = ?";
            $params[] = $type;
        }
        
        if ($status !== 'all') {
            $sql .= " AND t.payment_status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $sql .= " AND DATE(t.created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(t.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM transactions t WHERE 1=1";
        $countParams = [];
        if ($type !== 'all') {
            $countSql .= " AND t.transaction_type = ?";
            $countParams[] = $type;
        }
        if ($status !== 'all') {
            $countSql .= " AND t.payment_status = ?";
            $countParams[] = $status;
        }
        if ($dateFrom) {
            $countSql .= " AND DATE(t.created_at) >= ?";
            $countParams[] = $dateFrom;
        }
        if ($dateTo) {
            $countSql .= " AND DATE(t.created_at) <= ?";
            $countParams[] = $dateTo;
        }
        
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($countParams);
        $total = $stmt->fetchColumn();
        
        // Get summary stats
        $stmt = $pdo->query("SELECT 
                                COUNT(*) as total_count,
                                SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                                SUM(CASE WHEN transaction_type = 'donation' AND payment_status = 'completed' THEN amount ELSE 0 END) as total_donations,
                                SUM(CASE WHEN transaction_type = 'event_registration' AND payment_status = 'completed' THEN amount ELSE 0 END) as total_events
                            FROM transactions");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        jsonSuccess(['transactions' => $transactions, 'total' => $total, 'stats' => $stats]);
        
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Transactions API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
