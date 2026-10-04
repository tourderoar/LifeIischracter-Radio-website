<?php
/**
 * Life is Character Radio - Events API
 * Location: /api/events/index.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

try {
    $pdo = getDB();
    $status = $_GET['status'] ?? 'upcoming';
    $limit = min((int)($_GET['limit'] ?? 100), 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $userId = $_SESSION['user_id'] ?? null;
    
    $sql = "SELECT id, title, description, featured_image, event_date, location, cost, status, max_capacity, current_registrations 
            FROM events 
            WHERE status = ? AND published_at IS NOT NULL 
            ORDER BY event_date ASC 
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $limit, $offset]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data and check registration status
    foreach ($events as &$event) {
        $event['cost'] = (float)$event['cost'];
        $event['current_registrations'] = (int)$event['current_registrations'];
        $event['max_capacity'] = $event['max_capacity'] ? (int)$event['max_capacity'] : null;
        $event['featured_image'] = $event['featured_image'] ? BASE_URL . 'uploads/' . $event['featured_image'] : null;
        $event['is_full'] = $event['max_capacity'] && $event['current_registrations'] >= $event['max_capacity'];
        $event['spots_left'] = $event['max_capacity'] ? $event['max_capacity'] - $event['current_registrations'] : null;
        
        // Check if user is already registered
        $event['user_registered'] = false;
        if ($userId) {
            $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND payment_status IN ('completed', 'free')");
            $stmt->execute([$event['id'], $userId]);
            $event['user_registered'] = $stmt->fetch() !== false;
        }
    }
    
    jsonSuccess($events);
} catch (Exception $e) {
    error_log("Events API error: " . $e->getMessage());
    jsonError('Failed to fetch events', 500);
}
