<?php
/**
 * Life is Character Radio - Stripe Webhook Handler
 * Location: /api/webhook/stripe.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/stripe.php';
require_once __DIR__ . '/../../includes/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Stripe webhook error: Invalid request method - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    exit;
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (empty($payload)) {
    error_log("Stripe webhook error: Empty payload received");
    http_response_code(400);
    exit;
}

try {
    $event = verifyStripeWebhook($payload, $signature);
    if (!$event) {
        http_response_code(400);
        exit;
    }
    $pdo = getDB();
    
    switch ($event->type) {
        case 'payment_intent.succeeded':
            $paymentIntent = $event->data->object;
            handlePaymentSuccess($pdo, $paymentIntent);
            break;
            
        case 'payment_intent.payment_failed':
            $paymentIntent = $event->data->object;
            handlePaymentFailed($pdo, $paymentIntent);
            break;
            
        default:
            error_log("Unhandled webhook event: " . $event->type);
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    exit;
}

/**
 * Handle successful payment
 */
function handlePaymentSuccess($pdo, $paymentIntent) {
    $paymentIntentId = $paymentIntent->id;
    
    // Update transaction
    $stmt = $pdo->prepare("UPDATE transactions SET payment_status = 'completed', stripe_webhook_verified = 1, completed_at = NOW() WHERE payment_intent_id = ?");
    $stmt->execute([$paymentIntentId]);
    
    // Get transaction details
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE payment_intent_id = ?");
    $stmt->execute([$paymentIntentId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) return;
    
    if ($transaction['transaction_type'] === 'event_registration') {
        // Update event registration
        $stmt = $pdo->prepare("UPDATE event_registrations SET payment_status = 'completed', payment_completed_at = NOW() WHERE payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        
        // Increment registration count
        $stmt = $pdo->prepare("SELECT event_id, user_id FROM event_registrations WHERE payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        $reg = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reg) {
            $pdo->prepare("UPDATE events SET current_registrations = current_registrations + 1 WHERE id = ?")->execute([$reg['event_id']]);
            
            // Get event and user details for email
            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$reg['event_id']]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$reg['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($event && $user) {
                sendEventRegistrationEmail($user['email'], $user['full_name'], $event, (float)$transaction['amount']);
            }
        }
    } elseif ($transaction['transaction_type'] === 'donation') {
        // Update donation
        $stmt = $pdo->prepare("UPDATE donations SET payment_status = 'completed', payment_completed_at = NOW() WHERE payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        
        // Get donation details for email
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        $donation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($donation && $donation['donor_email']) {
            $email = $donation['donor_email'];
            $name = $donation['donor_name'] ?: 'Friend'; // Use 'Friend' for anonymous donations
            
            // If user donation, get user details
            if ($donation['user_id']) {
                $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ?");
                $stmt->execute([$donation['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $email = $user['email'];
                    $name = $user['full_name'];
                }
            }
            
            // Check if account was created with this donation
            if (!empty($donation['account_created']) && !empty($donation['activation_token'])) {
                // Send donation confirmation with account activation
                sendDonationWithAccountEmail($email, $name, (float)$donation['amount'], $donation['donation_frequency'], $donation['activation_token']);
            } else {
                // Send regular donation confirmation (includes anonymous donations)
                sendDonationConfirmationEmail($email, $name, (float)$donation['amount'], $donation['donation_frequency']);
            }
        }
    }
}

/**
 * Handle failed payment
 */
function handlePaymentFailed($pdo, $paymentIntent) {
    $paymentIntentId = $paymentIntent->id;
    
    $stmt = $pdo->prepare("UPDATE transactions SET payment_status = 'failed', stripe_webhook_verified = 1 WHERE payment_intent_id = ?");
    $stmt->execute([$paymentIntentId]);
    
    $stmt = $pdo->prepare("UPDATE event_registrations SET payment_status = 'failed' WHERE payment_intent_id = ?");
    $stmt->execute([$paymentIntentId]);
    
    $stmt = $pdo->prepare("UPDATE donations SET payment_status = 'failed' WHERE payment_intent_id = ?");
    $stmt->execute([$paymentIntentId]);
}
