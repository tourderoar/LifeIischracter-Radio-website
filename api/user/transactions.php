<?php
/**
 * Life is Character Radio - User Transactions API
 * Location: /api/user/transactions.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

// Check authentication
if (!isUserLoggedIn()) {
    jsonUnauthorized('Please login to access this page');
}

$userId = $_SESSION['user_id'];
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';

try {
    $pdo = getDB();
    
    // Build query
    $sql = "SELECT * FROM transactions WHERE user_id = ?";
    $params = [$userId];
    
    if ($filterType && $filterType !== 'all') {
        $sql .= " AND transaction_type = ?";
        $params[] = $filterType;
    }
    
    if ($filterStatus && $filterStatus !== 'all') {
        $sql .= " AND payment_status = ?";
        $params[] = $filterStatus;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalCount = count($transactions);
    $totalAmount = array_reduce($transactions, function($sum, $t) {
        return $sum + ($t['payment_status'] === 'completed' ? $t['amount'] : 0);
    }, 0);
    
    jsonSuccess([
        'transactions' => $transactions,
        'summary' => [
            'total_count' => $totalCount,
            'total_amount' => number_format($totalAmount, 2)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Transactions error: " . $e->getMessage());
    jsonError('Failed to load transactions', 500);
}
