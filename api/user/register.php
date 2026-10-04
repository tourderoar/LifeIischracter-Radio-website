<?php
/**
 * Life is Character Radio - User Registration API
 * Location: /api/user/register.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    jsonError('Already logged in', 400);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';
$fullName = trim($input['full_name'] ?? '');
$phone = trim($input['phone'] ?? '');

// Validation
$errors = [];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address';
if (empty($fullName)) $errors['full_name'] = 'Full name is required';

// Strong password validation
if (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[A-Z]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match('/[a-z]/', $password)) {
    $errors['password'] = 'Password must contain at least one lowercase letter';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one number';
} elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors['password'] = 'Password must contain at least one special character';
}

if ($password !== $confirmPassword) $errors['confirm_password'] = 'Passwords do not match';

if (!empty($errors)) {
    jsonValidationError($errors);
}

try {
    $pdo = getDB();
    
    // Check email uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    if ($stmt->fetch()) {
        jsonError('Email already registered', 409);
    }
    
    // Generate activation token
    $token = bin2hex(random_bytes(32));
    $expires = gmdate('Y-m-d H:i:s', time() + 86400); // 24 hours (UTC)
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone, activation_token, activation_expires_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        strtolower($email),
        password_hash($password, PASSWORD_DEFAULT),
        $fullName,
        $phone ?: null,
        $token,
        $expires
    ]);
    
    // Send activation email
    sendActivationEmail($email, $fullName, $token);
    
    jsonSuccess(null, 'Registration successful! Please check your email to activate your account.', 201);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonError('Registration failed. Please try again.', 500);
}
