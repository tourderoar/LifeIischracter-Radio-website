<?php
/**
 * Life is Character Radio - Donations API
 * Location: /api/donations/index.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, name, description, suggested_amount FROM donation_types WHERE is_active = 1 ORDER BY sort_order ASC");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($types as &$type) {
        $type['suggested_amount'] = $type['suggested_amount'] ? (float)$type['suggested_amount'] : null;
    }
    
    jsonSuccess($types);
} catch (Exception $e) {
    error_log("Donations API error: " . $e->getMessage());
    jsonError('Failed to fetch donation types', 500);
}
