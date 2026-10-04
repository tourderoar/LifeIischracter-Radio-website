<?php
/**
 * Life is Character Radio - Blog Categories API
 * Location: /api/blog/categories.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT c.id, c.name, c.slug, c.description, 
                         (SELECT COUNT(*) FROM blog_post_categories bpc WHERE bpc.category_id = c.id) as post_count 
                         FROM blog_categories c 
                         WHERE c.is_active = 1 
                         ORDER BY c.sort_order ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as &$cat) {
        $cat['post_count'] = (int)$cat['post_count'];
    }
    
    jsonSuccess($categories);
} catch (Exception $e) {
    error_log("Blog Categories API error: " . $e->getMessage());
    jsonError('Failed to fetch categories', 500);
}
