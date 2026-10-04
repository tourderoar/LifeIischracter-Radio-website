<?php
/**
 * Life is Character Radio - Create Payment Intent API
 * Location: /api/payments/create-intent.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/stripe.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

requireUserAuth();

$input = json_decode(file_get_contents('php://input'), true);
$amount = (float)($input['amount'] ?? 0);
$type = $input['type'] ?? ''; // 'event' or 'donation'
$referenceId = (int)($input['reference_id'] ?? 0);

if ($amount <= 0) jsonError('Invalid amount', 400);
if (!in_array($type, ['event', 'donation'])) jsonError('Invalid type', 400);

try {
    $pdo = getDB();
    $userId = $_SESSION['user_id'];
    
    // Create payment intent
    $intent = createPaymentIntent(toStripeCents($amount), 'usd', [
        'user_id' => (string)$userId,
        'type' => $type,
        'reference_id' => (string)$referenceId
    ]);
    
    // Save transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, reference_id, amount, payment_intent_id, payment_status, created_at) 
                           VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([
        $userId,
        $type === 'event' ? 'event_registration' : 'donation',
        $referenceId,
        $amount,
        $intent->id
    ]);
    
    jsonSuccess(['client_secret' => $intent->client_secret, 'payment_intent_id' => $intent->id]);
} catch (Exception $e) {
    error_log("Payment intent error: " . $e->getMessage());
    jsonError('Failed to create payment intent', 500);
}
