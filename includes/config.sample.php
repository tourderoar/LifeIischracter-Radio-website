<?php
/**
 * Life is Character Radio - Configuration File (TEMPLATE)
 *
 * Location: /includes/config.sample.php
 *
 * Copy this to includes/config.php and fill in your own values.
 * includes/config.php is gitignored because it holds credentials.
 *
 * Central configuration file for the application.
 * All pages should include this file first: require_once __DIR__ . '/includes/config.php';
 */

// Include guard
if (defined('LICR_CONFIG_LOADED')) return;
define('LICR_CONFIG_LOADED', true);

// Prevent direct access
defined('ABSPATH') || define('ABSPATH', dirname(__DIR__) . '/');

// Environment Configuration
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', getenv('APP_ENV') ?: 'local'); // 'local' or 'production'
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', ENVIRONMENT === 'local');

// Logging Configuration
if (!defined('LOG_PATH')) define('LOG_PATH', ABSPATH . 'logs/');
if (!defined('ERROR_LOG_FILE')) define('ERROR_LOG_FILE', LOG_PATH . 'error.log');
if (!defined('MAIL_LOG_FILE')) define('MAIL_LOG_FILE', LOG_PATH . 'mail.log');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// Timezone
date_default_timezone_set('America/Chicago'); // Central Time (Texas)

// Base URL Configuration (auto-detect with manual override support)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Calculate base path: everything up to (and including) the 'life' directory,
// derived from the filesystem path so it doesn't depend on which script ran
// or whether the request URL was rewritten (SCRIPT_NAME is unreliable for this).
$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$appRoot = str_replace('\\', '/', rtrim(__DIR__ . '/..', '/'));
$appRootReal = realpath($appRoot);
$appRoot = $appRootReal ? str_replace('\\', '/', $appRootReal) : $appRoot;

if ($docRoot && str_starts_with($appRoot, $docRoot)) {
    $basePath = substr($appRoot, strlen($docRoot)) . '/';
    $basePath = '/' . ltrim($basePath, '/');
} else {
    // App root is the document root itself (e.g. deployed at the domain root)
    $basePath = '/';
}

$baseUrl = $protocol . '://' . $host . $basePath;

if (!defined('BASE_URL')) define('BASE_URL', getenv('APP_URL') ?: $baseUrl);
if (!defined('SITE_NAME')) define('SITE_NAME', 'Life is Character Radio');
if (!defined('SITE_TAGLINE')) define('SITE_TAGLINE', 'Faith. Character. Victory.');

// Database Configuration
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'life_character_radio');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Stripe Configuration (environment-based)
if (!defined('STRIPE_SECRET_KEY')) {
    if (ENVIRONMENT === 'production') {
        define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_live_YOUR_LIVE_KEY');
        define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_live_YOUR_LIVE_KEY');
        define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: 'whsec_YOUR_WEBHOOK_SECRET');
    } else {
        define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_test_YOUR_TEST_SECRET_KEY');
        define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_YOUR_TEST_PUBLISHABLE_KEY');
        define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: 'whsec_YOUR_WEBHOOK_SECRET');
    }
}

// Zeptomail Configuration (only sends in production)
if (!defined('ZEPTOMAIL_API_URL')) define('ZEPTOMAIL_API_URL', 'https://api.zeptomail.com/v1.1/email');
if (!defined('ZEPTOMAIL_API_KEY')) define('ZEPTOMAIL_API_KEY', getenv('ZEPTOMAIL_API_KEY') ?: 'YOUR_ZEPTOMAIL_API_KEY');
if (!defined('ZEPTOMAIL_FROM_EMAIL')) define('ZEPTOMAIL_FROM_EMAIL', 'noreply@lifeischaracterradio.com');
if (!defined('ZEPTOMAIL_FROM_NAME')) define('ZEPTOMAIL_FROM_NAME', SITE_NAME);
if (!defined('SEND_EMAILS')) define('SEND_EMAILS', ENVIRONMENT === 'production');

// Session Configuration
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 86400); // 24 hours
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'LICR_SESSION');
// Only set session ini settings if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $protocol === 'https' ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
}

// File Upload Configuration
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', ABSPATH . 'uploads/');
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', BASE_URL . 'uploads/');
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
if (!defined('ALLOWED_IMAGE_TYPES')) define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
if (!defined('ALLOWED_IMAGE_EXTENSIONS')) define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security Configuration
if (!defined('CSRF_TOKEN_NAME')) define('CSRF_TOKEN_NAME', 'csrf_token');
if (!defined('PASSWORD_MIN_LENGTH')) define('PASSWORD_MIN_LENGTH', 8);
if (!defined('ACTIVATION_TOKEN_EXPIRY')) define('ACTIVATION_TOKEN_EXPIRY', 86400); // 24 hours
if (!defined('RESET_TOKEN_EXPIRY')) define('RESET_TOKEN_EXPIRY', 3600); // 1 hour

// Pagination
if (!defined('DEFAULT_PAGE_SIZE')) define('DEFAULT_PAGE_SIZE', 20);
if (!defined('MAX_PAGE_SIZE')) define('MAX_PAGE_SIZE', 100);

// Brand Colors (for email templates and dynamic styling)
if (!defined('BRAND_COLORS')) define('BRAND_COLORS', [
    'cyan' => '#00E5FF',
    'blue' => '#0099CC',
    'dark' => '#006B8F',
    'navy' => '#001A2E',
    'gold' => '#FFD700',
    'silver' => '#C0C0C0'
]);

// Permissions List (for RBAC)
if (!defined('AVAILABLE_PERMISSIONS')) define('AVAILABLE_PERMISSIONS', [
    'manage_events' => 'Manage Events',
    'manage_donations' => 'Manage Donations',
    'manage_blog' => 'Manage Blog Posts',
    'manage_episodes' => 'Manage Episodes',
    'manage_users' => 'Manage Users',
    'manage_admins' => 'Manage Admins & Roles',
    'view_reports' => 'View Reports',
    'manage_settings' => 'Manage Settings'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
