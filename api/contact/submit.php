<?php
/**
 * Life is Character Radio - Contact Form Submission API
 * Location: /api/contact/submit.php
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
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

$errors = [];
if (empty($name)) $errors['name'] = 'Name is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
if (empty($subject)) $errors['subject'] = 'Subject is required';
if (empty($message)) $errors['message'] = 'Message is required';

if (!empty($errors)) jsonValidationError($errors);

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, subject, message, ip_address, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $subject, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
    
    sendContactNotification(['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message]);
    
    jsonSuccess(null, 'Thank you for your message! We will respond shortly.');
} catch (Exception $e) {
    error_log("Contact submission error: " . $e->getMessage());
    jsonError('Failed to submit. Please try again.', 500);
}
