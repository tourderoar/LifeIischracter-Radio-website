<?php
/**
 * Life is Character Radio - Single Blog Post API
 * Location: /api/blog/post.php
 * Method: GET
 * 
 * Get single blog post by slug
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $slug = $_GET['slug'] ?? null;
    
    if (!$slug) {
        jsonError('Slug parameter is required', 400);
    }
    
    $pdo = getDB();
    
    $sql = "SELECT p.id, p.title, p.slug, p.content, p.excerpt, p.featured_image, 
                   p.scripture_reference, p.tags, p.author_name, p.status,
                   p.published_at, p.created_at, p.updated_at
            FROM blog_posts p
            WHERE p.slug = ? AND p.status = 'published' AND p.published_at <= NOW()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        jsonError('Blog post not found', 404);
    }
    
    // Get categories
    $stmt = $pdo->prepare("SELECT c.id, c.name, c.slug FROM blog_categories c 
                           INNER JOIN blog_post_categories bpc ON bpc.category_id = c.id 
                           WHERE bpc.blog_post_id = ? ORDER BY c.name");
    $stmt->execute([$post['id']]);
    $post['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse tags
    $post['tags'] = $post['tags'] ? json_decode($post['tags'], true) : [];
    
    // Construct image URL
    $post['featured_image'] = $post['featured_image'] ? BASE_URL . 'uploads/' . $post['featured_image'] : null;
    
    jsonSuccess($post);
} catch (Exception $e) {
    error_log("Blog post API error: " . $e->getMessage());
    jsonError('Failed to fetch blog post', 500);
}
