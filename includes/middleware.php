<?php
/**
 * Life is Character Radio - API Middleware
 * 
 * Location: /includes/middleware.php
 * 
 * Authentication and authorization middleware for API endpoints
 */

// Include guard
if (defined('LICR_MIDDLEWARE_LOADED')) return;
define('LICR_MIDDLEWARE_LOADED', true);

if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/response.php';

/**
 * Require admin authentication for API endpoint
 * Sends JSON error if not authenticated
 */
function requireAdminAuthAPI(): void {
    if (!isAdminLoggedIn()) {
        jsonError('Unauthorized. Admin authentication required.', 401);
    }
}

/**
 * Require user authentication for API endpoint
 * Sends JSON error if not authenticated
 */
function requireUserAuthAPI(): void {
    if (!isUserLoggedIn()) {
        jsonError('Unauthorized. User authentication required.', 401);
    }
}

/**
 * Require specific permission for API endpoint
 * Sends JSON error if not authorized
 * 
 * @param string $permission Required permission
 */
function requirePermissionAPI(string $permission): void {
    requireAdminAuthAPI();
    
    if (!hasPermission($permission) && !isSuperAdmin()) {
        jsonError('Forbidden. Insufficient permissions.', 403);
    }
}

/**
 * Validate CSRF token for API endpoint
 * Sends JSON error if invalid
 */
function validateCsrfTokenAPI(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    
    if (!verifyCsrfToken($token)) {
        jsonError('Invalid CSRF token.', 403);
    }
}

/**
 * Get all admin emails from database
 * 
 * @return array Array of admin email addresses
 */
function getAdminEmails(): array {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT email FROM admins WHERE is_active = 1");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log('Error fetching admin emails: ' . $e->getMessage());
        return [];
    }
}

/**
 * Send email to all active admins
 * 
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @return bool True if sent successfully to at least one admin
 */
function notifyAdmins(string $subject, string $htmlBody): bool {
    $adminEmails = getAdminEmails();
    $success = false;
    
    foreach ($adminEmails as $email) {
        if (sendEmail($email, $subject, $htmlBody)) {
            $success = true;
        }
    }
    
    return $success;
}
