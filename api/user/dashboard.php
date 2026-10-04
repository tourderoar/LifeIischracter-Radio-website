<?php
/**
 * Life is Character Radio - User Dashboard Data API
 * Location: /api/user/dashboard.php
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

try {
    $pdo = getDB();
    
    // Get statistics
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM event_registrations er 
        JOIN events e ON er.event_id = e.id 
        WHERE er.user_id = ? AND e.event_date > NOW() AND er.payment_status = 'completed'
    ");
    $stmt->execute([$userId]);
    $upcomingEvents = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM transactions 
        WHERE user_id = ? AND transaction_type = 'donation' AND payment_status = 'completed'
    ");
    $stmt->execute([$userId]);
    $totalDonations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM transactions 
        WHERE user_id = ? AND payment_status = 'completed'
    ");
    $stmt->execute([$userId]);
    $totalTransactions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT * FROM transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get upcoming events
    $stmt = $pdo->prepare("
        SELECT e.*, er.payment_status, er.registered_at as registration_date
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        WHERE er.user_id = ? AND e.event_date > NOW()
        ORDER BY e.event_date ASC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $upcomingEventsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonSuccess([
        'stats' => [
            'upcoming_events' => $upcomingEvents,
            'total_donations' => number_format($totalDonations, 2),
            'total_transactions' => $totalTransactions
        ],
        'recent_transactions' => $recentTransactions,
        'upcoming_events' => $upcomingEventsList
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    jsonError('Failed to load dashboard data', 500);
}
