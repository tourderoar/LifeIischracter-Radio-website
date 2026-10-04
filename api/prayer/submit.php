<?php
/**
 * Life is Character Radio - Prayer Request Submission API
 * Location: /api/prayer/submit.php
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
$request = trim($input['prayer_request'] ?? $input['request'] ?? '');
$isAnonymous = empty($name) && empty($email);

$errors = [];
if (empty($request)) $errors['prayer_request'] = 'Prayer request is required';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Valid email is required when email is provided';
}

if (!empty($errors)) jsonValidationError($errors);

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO prayer_requests (name, email, request, is_anonymous, ip_address, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $name ?: null,
        $email ?: null,
        $request,
        $isAnonymous ? 1 : 0,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
    
    sendPrayerNotification([
        'name' => $name,
        'email' => $email,
        'prayer_request' => $request,
        'is_anonymous' => $isAnonymous
    ]);
    
    jsonSuccess(null, 'Thank you for sharing your prayer request. We are praying for you!');
} catch (Exception $e) {
    error_log("Prayer submission error: " . $e->getMessage());
    jsonError('Failed to submit. Please try again.', 500);
}
