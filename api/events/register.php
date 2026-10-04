<?php
/**
 * Life is Character Radio - Event Registration API
 * Location: /api/events/register.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/stripe.php';
require_once __DIR__ . '/../../includes/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

requireUserAuth();

$input = json_decode(file_get_contents('php://input'), true);
$eventId = (int)($input['event_id'] ?? 0);

if (!$eventId) jsonError('Event ID is required', 400);

try {
    $pdo = getDB();
    $userId = $_SESSION['user_id'];
    
    // Get event details
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status = 'upcoming'");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) jsonError('Event not found or not available', 404);
    
    // Check if already registered
    $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eventId, $userId]);
    if ($stmt->fetch()) jsonError('Already registered for this event', 409);
    
    // Check capacity
    if ($event['max_capacity'] && $event['current_registrations'] >= $event['max_capacity']) {
        jsonError('Event is full', 400);
    }
    
    $cost = (float)$event['cost'];
    
    if ($cost == 0) {
        // Free event - auto-complete registration
        $stmt = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, payment_status, amount_paid, registered_at, payment_completed_at) 
                               VALUES (?, ?, 'completed', 0, NOW(), NOW())");
        $stmt->execute([$eventId, $userId]);
        
        // Update registration count
        $pdo->prepare("UPDATE events SET current_registrations = current_registrations + 1 WHERE id = ?")->execute([$eventId]);
        
        // Get user details and send confirmation emails
        $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            sendEventRegistrationEmail($user['email'], $user['full_name'], $event, 0);
        }
        
        jsonSuccess(['status' => 'completed'], 'Successfully registered for event!');
    } else {
        // Paid event - create payment intent
        $intent = createPaymentIntent(toStripeCents($cost), 'usd', [
            'user_id' => (string)$userId,
            'event_id' => (string)$eventId,
            'event_title' => $event['title']
        ]);
        
        // Create pending registration
        $stmt = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, payment_intent_id, payment_status, amount_paid, registered_at) 
                               VALUES (?, ?, ?, 'pending', ?, NOW())");
        $stmt->execute([$eventId, $userId, $intent->id, $cost]);
        $registrationId = $pdo->lastInsertId();
        
        // Create transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, reference_id, amount, payment_intent_id, payment_status, created_at) 
                               VALUES (?, 'event_registration', ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $registrationId, $cost, $intent->id]);
        
        jsonSuccess([
            'status' => 'pending',
            'client_secret' => $intent->client_secret,
            'amount' => $cost
        ], 'Payment required to complete registration');
    }
} catch (Exception $e) {
    error_log("Event registration error: " . $e->getMessage());
    jsonError('Failed to register for event', 500);
}
