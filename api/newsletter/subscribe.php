<?php
/**
 * Life is Character Radio - Newsletter Subscription API
 * Location: /api/newsletter/subscribe.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonError('Valid email is required', 400);

try {
    $pdo = getDB();
    
    // Check if already subscribed
    $stmt = $pdo->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['is_active']) {
            jsonError('This email is already subscribed', 409);
        } else {
            // Reactivate
            $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 1, name = ?, subscribed_at = NOW(), unsubscribed_at = NULL WHERE id = ?");
            $stmt->execute([$name ?: null, $existing['id']]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (name, email, subscribed_at) VALUES (?, ?, NOW())");
        $stmt->execute([$name ?: null, strtolower($email)]);
    }
    
    sendNewsletterWelcomeEmail($email, $name);
    
    jsonSuccess(null, 'Thank you for subscribing! Check your email for confirmation.');
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    jsonError('Failed to subscribe. Please try again.', 500);
}
