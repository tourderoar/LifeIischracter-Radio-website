<?php
/**
 * Life is Character Radio - Authentication Helpers
 * 
 * Location: /includes/auth.php
 * 
 * Session management, authentication checks, and permission handling
 */

// Include guard
if (defined('LICR_AUTH_LOADED')) return;
define('LICR_AUTH_LOADED', true);

// Ensure dependencies are loaded
if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ==================== CSRF & Token Functions ====================

if (!function_exists('generateCsrfToken')) {
function generateCsrfToken(): string {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}
}

if (!function_exists('verifyCsrfToken')) {
function verifyCsrfToken(?string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && 
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token ?? '');
}
}

if (!function_exists('generateRandomToken')) {
function generateRandomToken(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}
}

// ==================== Password Functions ====================

if (!function_exists('hashPassword')) {
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}
}

if (!function_exists('verifyPassword')) {
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}
}

// ==================== Session Management ====================

if (!function_exists('setAdminSession')) {
function setAdminSession(array $admin): void {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_role_id'] = $admin['role_id'];
    $_SESSION['admin_role_name'] = $admin['role_name'] ?? null;
    $_SESSION['admin_permissions'] = $admin['permissions'] ?? [];
    $_SESSION['is_admin'] = true;
}
}

if (!function_exists('setUserSession')) {
function setUserSession(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['is_user'] = true;
}
}

// ==================== Authentication Checks ====================

if (!function_exists('isAdminLoggedIn')) {
function isAdminLoggedIn(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}
}

if (!function_exists('isUserLoggedIn')) {
function isUserLoggedIn(): bool {
    return isset($_SESSION['is_user']) && $_SESSION['is_user'] === true;
}
}

if (!function_exists('requireAdminAuth')) {
function requireAdminAuth(string $redirectUrl = '/admin/login'): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . ltrim($redirectUrl, '/'));
        exit;
    }
}
}

if (!function_exists('requireUserAuth')) {
function requireUserAuth(string $redirectUrl = '/account/login'): void {
    if (!isUserLoggedIn()) {
        header('Location: ' . BASE_URL . ltrim($redirectUrl, '/'));
        exit;
    }
}
}

// ==================== Permission Functions ====================

if (!function_exists('getAdminPermissions')) {
function getAdminPermissions(): array {
    return $_SESSION['admin_permissions'] ?? [];
}
}

if (!function_exists('hasPermission')) {
function hasPermission(string $permission): bool {
    if (!isAdminLoggedIn()) return false;
    
    $permissions = getAdminPermissions();
    return in_array($permission, $permissions);
}
}

if (!function_exists('isSuperAdmin')) {
function isSuperAdmin(): bool {
    return isAdminLoggedIn() && 
           ($_SESSION['admin_role_name'] ?? '') === 'Super Admin';
}
}

if (!function_exists('requirePermission')) {
function requirePermission(string $permission, string $redirectUrl = '/admin'): void {
    if (!hasPermission($permission) && !isSuperAdmin()) {
        header('Location: ' . BASE_URL . ltrim($redirectUrl, '/'));
        exit;
    }
}
}

// ==================== User Management ====================

if (!function_exists('getCurrentUser')) {
function getCurrentUser(): ?array {
    if (isAdminLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'],
            'email' => $_SESSION['admin_email'],
            'name' => $_SESSION['admin_name'],
            'type' => 'admin',
            'role' => $_SESSION['admin_role_name'] ?? null,
            'permissions' => $_SESSION['admin_permissions'] ?? []
        ];
    }
    
    if (isUserLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'type' => 'user'
        ];
    }
    
    return null;
}
}

if (!function_exists('logout')) {
function logout(): void {
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}
}

// ==================== Validation Functions ====================

if (!function_exists('isValidEmail')) {
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
}

if (!function_exists('validatePasswordStrength')) {
function validatePasswordStrength(string $password): array {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}
}

if (!function_exists('getClientIp')) {
function getClientIp(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            return trim($ip);
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
}
