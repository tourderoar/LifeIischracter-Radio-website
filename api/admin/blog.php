<?php
/**
 * Life is Character Radio - Blog API
 * 
 * Location: /api/admin/blog.php
 * 
 * GET: List posts or categories
 * POST: Create post or category
 * PUT: Update post or category
 * DELETE: Delete post or category
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/upload.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_blog');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    // Check for method override first
    if ($method === 'POST' && isset($_POST['_method'])) {
        if ($_POST['_method'] === 'PUT') {
            $method = 'PUT';
        } elseif ($_POST['_method'] === 'DELETE') {
            $method = 'DELETE';
        }
    }
    
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'posts';
        
        if ($action === 'categories') {
            // List all categories
            $stmt = $pdo->query("SELECT c.*, COUNT(DISTINCT bpc.blog_post_id) as post_count FROM blog_categories c LEFT JOIN blog_post_categories bpc ON c.id = bpc.category_id GROUP BY c.id ORDER BY c.sort_order ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonSuccess(['categories' => $categories]);
            
        } elseif ($action === 'posts') {
            // List all posts
            $category_id = $_GET['category_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $limit = min((int)($_GET['limit'] ?? 50), 100);
            $offset = max((int)($_GET['offset'] ?? 0), 0);
            
            $sql = "SELECT bp.* FROM blog_posts bp WHERE 1=1";
            $params = [];
            
            if ($category_id) {
                $sql = "SELECT bp.* FROM blog_posts bp JOIN blog_post_categories bpc ON bp.id = bpc.blog_post_id WHERE bpc.category_id = ?";
                $params[] = $category_id;
            }
            
            if ($status) {
                $sql .= " AND bp.status = ?";
                $params[] = $status; // 'published' or 'draft'
            }
            
            $sql .= " ORDER BY bp.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get categories for each post
            foreach ($posts as &$post) {
                $stmt = $pdo->prepare("SELECT c.id, c.name, c.slug FROM blog_categories c JOIN blog_post_categories bpc ON c.id = bpc.category_id WHERE bpc.blog_post_id = ?");
                $stmt->execute([$post['id']]);
                $post['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $post['tags'] = json_decode($post['tags'] ?: '[]');
            }
            
            $countSql = "SELECT COUNT(*) FROM blog_posts WHERE 1=1" . ($status ? " AND status = '" . ($status === 'published' ? 'published' : 'draft') . "'" : "");
            $total = $pdo->query($countSql)->fetchColumn();
            
            jsonSuccess(['posts' => $posts, 'total' => $total]);
        } else {
            jsonError('Invalid action', 400);
        }
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $action = $_POST['action'] ?? 'post';
        
        if ($action === 'category') {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Category ID required', 400);
            
            // Check if posts using this category
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_post_categories WHERE category_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                jsonError('Cannot delete category with posts. Deactivate instead.', 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM blog_categories WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonSuccess(['message' => 'Category deleted successfully']);
            
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Post ID required', 400);
            
            // Delete post and associated categories
            $pdo->prepare("DELETE FROM blog_post_categories WHERE blog_post_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
            
            jsonSuccess(['message' => 'Blog post deleted successfully']);
        }
        
    } elseif ($method === 'PUT') {
        validateCsrfTokenAPI();
        
        $action = $_POST['action'] ?? 'post';
        
        if ($action === 'category') {
            // Create category
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            if (empty($name)) jsonError('Category name is required', 400);
            
            // Check slug uniqueness
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_categories WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) jsonError('Category slug already exists', 400);
            
            $stmt = $pdo->prepare("INSERT INTO blog_categories (name, slug, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $sort_order, $is_active]);
            
            jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Category created successfully']);
            
        } else {
            // Create post
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: preg_replace('/-+/', '-', trim(preg_replace('/[^a-z0-9-]/', '-', strtolower($title)), '-'));
            $scripture_reference = trim($_POST['scripture_reference'] ?? '');
            $categories = $_POST['categories'] ?? [];
            $tags = isset($_POST['tags']) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['tags'])))) : '[]';
            $status = isset($_POST['is_published']) && $_POST['is_published'] ? 'published' : 'draft';
            
            if (empty($title) || empty($content)) jsonError('Title and content are required', 400);
            
            // Check slug uniqueness
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) jsonError('Slug already exists', 400);
            
            // Handle image upload
            $featured_image = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadImage($_FILES['featured_image'], 'blog');
                if (!$upload['success']) {
                    jsonError($upload['error'], 400);
                }
                $featured_image = $upload['path'];
            }
            
            $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
            
            // Credit the admin who created the post. Without this the column
            // default ('Paul Kumeni Agbo') would be applied to every post.
            $author_name = $_SESSION['admin_name'] ?? '';
            if ($author_name === '') {
                $stmt = $pdo->prepare("SELECT full_name FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $author_name = $stmt->fetchColumn() ?: 'Life is Character Radio';
            }

            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, scripture_reference, tags, author_name, status, created_by, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $scripture_reference, $tags, $author_name, $status, $_SESSION['admin_id'], $published_at]);
            
            $post_id = $pdo->lastInsertId();
            
            // Associate categories
            if (!empty($categories)) {
                $stmt = $pdo->prepare("INSERT INTO blog_post_categories (blog_post_id, category_id) VALUES (?, ?)");
                foreach ($categories as $cat_id) {
                    $stmt->execute([$post_id, (int)$cat_id]);
                }
            }
            
            jsonSuccess(['id' => $post_id, 'message' => 'Blog post created successfully']);
        }
        
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        $action = $_POST['action'] ?? 'post';
        
        if ($action === 'category') {
            // Update category
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Category ID required', 400);
            
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            if (empty($name)) jsonError('Category name is required', 400);
            if (empty($slug)) jsonError('Category slug is required', 400);
            
            // Check slug uniqueness (excluding current)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_categories WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetchColumn() > 0) jsonError('Category slug already exists', 400);
            
            $stmt = $pdo->prepare("UPDATE blog_categories SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $sort_order, $is_active, $id]);
            
            jsonSuccess(['message' => 'Category updated successfully']);
            
        } else {
            // Update post
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Post ID required', 400);
            
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: preg_replace('/-+/', '-', trim(preg_replace('/[^a-z0-9-]/', '-', strtolower($title)), '-'));
            $scripture_reference = trim($_POST['scripture_reference'] ?? '');
            $categories = $_POST['categories'] ?? [];
            $tags = isset($_POST['tags']) ? json_encode(array_filter(array_map('trim', explode(',', $_POST['tags'])))) : '[]';
            $status = isset($_POST['is_published']) && $_POST['is_published'] ? 'published' : 'draft';
            
            if (empty($title) || empty($content)) jsonError('Title and content are required', 400);
            if (empty($slug)) jsonError('Slug is required', 400);
            
            // Check slug uniqueness (excluding current)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetchColumn() > 0) jsonError('Slug already exists', 400);
            
            // Get current published status and image
            $stmt = $pdo->prepare("SELECT status, published_at, featured_image FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Handle image upload
            $featured_image = $current['featured_image'];
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadImage($_FILES['featured_image'], 'blog');
                if (!$upload['success']) {
                    jsonError($upload['error'], 400);
                }
                $featured_image = $upload['path'];
                
                // Delete old image if exists
                if ($current['featured_image']) {
                    deleteImage($current['featured_image']);
                }
            }
            
            $published_at = $current['published_at'];
            if ($status === 'published' && $current['status'] !== 'published') {
                $published_at = date('Y-m-d H:i:s');
            } elseif ($status !== 'published') {
                $published_at = null;
            }
            
            $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, scripture_reference = ?, tags = ?, status = ?, published_at = ? WHERE id = ?");
            $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $scripture_reference, $tags, $status, $published_at, $id]);
            
            // Update categories
            $pdo->prepare("DELETE FROM blog_post_categories WHERE blog_post_id = ?")->execute([$id]);
            if (!empty($categories)) {
                $stmt = $pdo->prepare("INSERT INTO blog_post_categories (blog_post_id, category_id) VALUES (?, ?)");
                foreach ($categories as $cat_id) {
                    $stmt->execute([$id, (int)$cat_id]);
                }
            }
            
            jsonSuccess(['message' => 'Blog post updated successfully']);
        }
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Blog API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
