<?php
/**
 * Life is Character Radio - Create Donation API
 * Location: /api/donations/create.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/stripe.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);

$amount = (float)($input['amount'] ?? 0);
$donationTypeId = (int)($input['donation_type_id'] ?? 0);
$frequency = $input['donation_frequency'] ?? 'one-time';
$isAnonymous = !empty($input['is_anonymous']);
$donorName = trim($input['donor_name'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$message = trim($input['message'] ?? '');

if ($amount <= 0) jsonError('Invalid amount', 400);
if (!in_array($frequency, ['one-time', 'monthly', 'quarterly', 'yearly'])) jsonError('Invalid frequency', 400);

// Email is always required (even for anonymous donations)
if (!filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) jsonError('Valid email is required', 400);

// Validation for non-anonymous donations
if (!$isAnonymous && empty($donorName)) {
    jsonError('Donor name is required for non-anonymous donations', 400);
}

try {
    $pdo = getDB();
    $userId = $_SESSION['user_id'] ?? null;
    $accountCreated = false;
    $activationToken = null;
    
    // Create guest account for non-anonymous donations from non-logged-in users
    if (!$userId && !$isAnonymous && $donorEmail && $donorName) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE email = ?");
        $stmt->execute([strtolower($donorEmail)]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // User exists - use their account
            $userId = $existingUser['id'];
        } else {
            // Create new account with activation token
            $activationToken = generateRandomToken(64);
            $activationExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, activation_token, activation_expires_at, is_active, created_at) 
                                   VALUES (?, ?, '', ?, ?, 0, NOW())");
            $stmt->execute([$donorName, strtolower($donorEmail), $activationToken, $activationExpiry]);
            
            $userId = $pdo->lastInsertId();
            $accountCreated = true;
        }
    }
    
    if ($frequency === 'one-time') {
        // Create payment intent
        $intent = createPaymentIntent(
            toStripeCents($amount),
            'usd',
            [
                'user_id' => (string)($userId ?: ''),
                'donation_type_id' => (string)$donationTypeId,
                'frequency' => $frequency,
                'is_anonymous' => $isAnonymous ? '1' : '0',
                'account_created' => $accountCreated ? '1' : '0'
            ]
        );
        
        // Create donation record
        $stmt = $pdo->prepare("INSERT INTO donations (user_id, donation_type_id, amount, donation_frequency, is_anonymous, donor_name, donor_email, message, payment_intent_id, payment_status, donated_at, account_created, activation_token) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?, ?)");
        $stmt->execute([
            $userId,
            $donationTypeId ?: null,
            $amount,
            $frequency,
            $isAnonymous ? 1 : 0,
            $isAnonymous ? null : $donorName,
            $donorEmail,
            $message ?: null,
            $intent->id,
            $accountCreated ? 1 : 0,
            $accountCreated ? $activationToken : null
        ]);
        $donationId = $pdo->lastInsertId();
        
        // Create transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, reference_id, amount, payment_intent_id, payment_status, created_at) 
                               VALUES (?, 'donation', ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $donationId, $amount, $intent->id]);
        
        jsonSuccess([
            'client_secret' => $intent->client_secret,
            'donation_id' => $donationId,
            'account_created' => $accountCreated
        ], 'Payment required to complete donation');
    } else {
        // Recurring donation - create customer and subscription
        // Note: This requires Stripe Price IDs to be set up for each frequency
        jsonError('Recurring donations not yet implemented. Please use one-time donation.', 501);
    }
} catch (Exception $e) {
    error_log("Donation creation error: " . $e->getMessage());
    jsonError('Failed to process donation', 500);
}
