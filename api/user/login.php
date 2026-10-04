<?php
/**
 * Life is Character Radio - User Login API
 * Location: /api/user/login.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

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
$remember = $input['remember'] ?? false;
$redirect = $input['redirect'] ?? null;

// Validation
if (empty($email) || empty($password)) {
    jsonError('Email and password are required', 400);
}

// Validate and sanitize redirect URL if provided
$redirectUrl = BASE_URL . 'account/dashboard'; // Default redirect
if ($redirect) {
    // Decode the redirect URL
    $redirect = urldecode($redirect);
    
    // Only allow redirects to the same domain
    $parsedUrl = parse_url($redirect);
    $parsedBaseUrl = parse_url(BASE_URL);
    
    // Check if it's a relative URL or same domain
    if (!isset($parsedUrl['host']) || 
        (isset($parsedUrl['host']) && $parsedUrl['host'] === $parsedBaseUrl['host'])) {
        $redirectUrl = $redirect;
    }
}

try {
    $pdo = getDB();
    
    // Get user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        jsonError('Invalid email or password', 401);
    }
    
    if (!$user['is_active']) {
        jsonError('Please activate your account. Check your email for the activation link.', 403);
    }
    
    // Set session - Set all required session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['is_user'] = true;
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    jsonSuccess([
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'phone' => $user['phone']
        ],
        'redirect' => $redirectUrl
    ], 'Login successful');
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    jsonError('Login failed. Please try again.', 500);
}
