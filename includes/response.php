<?php
/**
 * Life is Character Radio - Response Helpers
 * 
 * Location: /includes/response.php
 * 
 * Standardized JSON response functions for API endpoints
 */

// Include guard
if (defined('LICR_RESPONSE_LOADED')) return;
define('LICR_RESPONSE_LOADED', true);

// Ensure config is loaded
if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';

if (!function_exists('jsonResponse')) {
function jsonResponse(int $status, mixed $data, array $headers = []): never {
    http_response_code($status);
    header('Content-Type: application/json');
    
    foreach ($headers as $key => $value) {
        header("$key: $value");
    }
    
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
}

if (!function_exists('jsonSuccess')) {
function jsonSuccess(mixed $data = null, string $message = 'Success', int $status = 200): never {
    jsonResponse($status, [
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}
}

if (!function_exists('jsonError')) {
function jsonError(string $message, int $status = 400, ?array $errors = null): never {
    $response = [
        'success' => false,
        'message' => $message
    ];
    
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    
    jsonResponse($status, $response);
}
}

if (!function_exists('jsonValidationError')) {
function jsonValidationError(array $errors, string $message = 'Validation failed'): never {
    jsonError($message, 422, $errors);
}
}

if (!function_exists('jsonUnauthorized')) {
function jsonUnauthorized(string $message = 'Unauthorized'): never {
    jsonError($message, 401);
}
}

if (!function_exists('jsonForbidden')) {
function jsonForbidden(string $message = 'Forbidden'): never {
    jsonError($message, 403);
}
}

if (!function_exists('jsonNotFound')) {
function jsonNotFound(string $message = 'Not found'): never {
    jsonError($message, 404);
}
}

if (!function_exists('setCorsHeaders')) {
function setCorsHeaders(array $allowedOrigins = [], array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    
    if (empty($allowedOrigins) || in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
}

if (!function_exists('validateRequired')) {
function validateRequired(array $data, array $required): ?array {
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[$field] = ucfirst($field) . ' is required';
        }
    }
    
    return empty($errors) ? null : $errors;
}
}

if (!function_exists('sanitize')) {
function sanitize(mixed $value): mixed {
    if (is_string($value)) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    return $value;
}
}

if (!function_exists('getRequestBody')) {
function getRequestBody(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (stripos($contentType, 'application/json') !== false) {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }
    
    return $_POST;
}
}

if (!function_exists('getRequestMethod')) {
function getRequestMethod(): string {
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}
}

if (!function_exists('isAjax')) {
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
}
