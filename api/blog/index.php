<?php
/**
 * Life is Character Radio - Blog API
 * Location: /api/blog/index.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

try {
    $pdo = getDB();
    $categoryId = $_GET['category_id'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 10), 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.scripture_reference, 
            p.tags, p.author_name, p.published_at 
            FROM blog_posts p 
            WHERE p.status = 'published' AND p.published_at IS NOT NULL";
    
    $params = [];
    if ($categoryId) {
        $sql .= " AND EXISTS (SELECT 1 FROM blog_post_categories bpc WHERE bpc.blog_post_id = p.id AND bpc.category_id = ?)";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY p.published_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for each post
    foreach ($posts as &$post) {
        $stmt = $pdo->prepare("SELECT c.id, c.name, c.slug FROM blog_categories c 
                               INNER JOIN blog_post_categories bpc ON bpc.category_id = c.id 
                               WHERE bpc.blog_post_id = ? ORDER BY c.name");
        $stmt->execute([$post['id']]);
        $post['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $post['tags'] = $post['tags'] ? json_decode($post['tags'], true) : [];
        $post['featured_image'] = $post['featured_image'] ? BASE_URL . 'uploads/' . $post['featured_image'] : null;
    }
    
    jsonSuccess($posts);
} catch (Exception $e) {
    error_log("Blog API error: " . $e->getMessage());
    jsonError('Failed to fetch blog posts', 500);
}
