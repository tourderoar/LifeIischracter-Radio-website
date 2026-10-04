<?php
/**
 * Life is Character Radio - Episodes API
 * 
 * Location: /api/admin/episodes.php
 * 
 * GET: List episodes or topics
 * POST: Create episode or topic
 * PUT: Update episode or topic
 * DELETE: Delete episode or topic
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_episodes');

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
        $action = $_GET['action'] ?? 'episodes';
        
        if ($action === 'topics') {
            // List all topics
            $stmt = $pdo->query("SELECT t.*, COUNT(e.id) as episode_count FROM episode_topics t LEFT JOIN episodes e ON t.id = e.topic_id GROUP BY t.id ORDER BY t.sort_order ASC");
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonSuccess(['topics' => $topics]);
            
        } elseif ($action === 'episodes') {
            // List all episodes
            $topic_id = $_GET['topic_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $limit = min((int)($_GET['limit'] ?? 50), 100);
            $offset = max((int)($_GET['offset'] ?? 0), 0);
            
            $sql = "SELECT e.*, t.name as topic_name FROM episodes e LEFT JOIN episode_topics t ON e.topic_id = t.id WHERE 1=1";
            $params = [];
            
            if ($topic_id) {
                $sql .= " AND e.topic_id = ?";
                $params[] = $topic_id;
            }
            
            if ($status) {
                $sql .= " AND e.status = ?";
                $params[] = $status; // 'published', 'draft', or 'upcoming'
            }
            
            $sql .= " ORDER BY e.episode_number DESC, e.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $countSql = "SELECT COUNT(*) FROM episodes WHERE 1=1" . ($status ? " AND status = '" . ($status === 'published' ? 'published' : ($status === 'upcoming' ? 'upcoming' : 'draft')) . "'" : "");
            $total = $pdo->query($countSql)->fetchColumn();
            
            jsonSuccess(['episodes' => $episodes, 'total' => $total]);
        } else {
            jsonError('Invalid action', 400);
        }
        
    } elseif ($method === 'DELETE') {
        validateCsrfTokenAPI();
        
        $action = $_POST['action'] ?? 'episode';
        
        if ($action === 'topic') {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Topic ID required', 400);
            
            // Check if episodes using this topic
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM episodes WHERE topic_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                jsonError('Cannot delete topic with episodes. Deactivate instead.', 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM episode_topics WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonSuccess(['message' => 'Topic deleted successfully']);
            
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Episode ID required', 400);
            
            $stmt = $pdo->prepare("DELETE FROM episodes WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonSuccess(['message' => 'Episode deleted successfully']);
        }
        
    } elseif ($method === 'PUT') {
        validateCsrfTokenAPI();
        
        $action = $_POST['action'] ?? 'episode';
        
        if ($action === 'topic') {
            // Create topic
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            if (empty($name)) jsonError('Topic name is required', 400);
            
            // Check slug uniqueness
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM episode_topics WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) jsonError('Topic slug already exists', 400);
            
            $stmt = $pdo->prepare("INSERT INTO episode_topics (name, slug, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $sort_order, $is_active]);
            
            jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Topic created successfully']);
            
        } else {
            // Create episode
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $youtube_url = trim($_POST['youtube_url'] ?? '');
            $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');
            $scripture_reference = trim($_POST['scripture_reference'] ?? '');
            $episode_number = !empty($_POST['episode_number']) ? (int)$_POST['episode_number'] : null;
            $duration = !empty($_POST['duration']) ? (int)$_POST['duration'] : null;
            $topic_id = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
            $status = isset($_POST['is_published']) && $_POST['is_published'] ? 'published' : 'draft';
            
            if (empty($title) || empty($youtube_url)) jsonError('Title and YouTube URL are required', 400);
            
            // Validate YouTube URL
            if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $youtube_url)) {
                jsonError('Invalid YouTube URL', 400);
            }
            
            // Auto-generate thumbnail from YouTube if not provided
            if (empty($thumbnail_url)) {
                $video_id = extractYouTubeId($youtube_url);
                if ($video_id) {
                    $thumbnail_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
                }
            }
            
            $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
            
            $stmt = $pdo->prepare("INSERT INTO episodes (title, description, youtube_url, thumbnail_url, scripture_reference, episode_number, duration, topic_id, status, created_by, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $youtube_url, $thumbnail_url, $scripture_reference, $episode_number, $duration, $topic_id, $status, $_SESSION['admin_id'], $published_at]);
            
            jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Episode created successfully']);
        }
    } elseif ($method === 'POST') {
        validateCsrfTokenAPI();
        
        $action = $_POST['action'] ?? 'episode';
        
        if ($action === 'topic') {
            // Update topic
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Topic ID required', 400);
            
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            if (empty($name)) jsonError('Topic name is required', 400);
            if (empty($slug)) jsonError('Topic slug is required', 400);
            
            // Check slug uniqueness (excluding current)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM episode_topics WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetchColumn() > 0) jsonError('Topic slug already exists', 400);
            
            $stmt = $pdo->prepare("UPDATE episode_topics SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $sort_order, $is_active, $id]);
            
            jsonSuccess(['message' => 'Topic updated successfully']);
            
        } else {
            // Update episode
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonError('Episode ID required', 400);
            
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $youtube_url = trim($_POST['youtube_url'] ?? '');
            $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');
            $scripture_reference = trim($_POST['scripture_reference'] ?? '');
            $episode_number = !empty($_POST['episode_number']) ? (int)$_POST['episode_number'] : null;
            $duration = !empty($_POST['duration']) ? (int)$_POST['duration'] : null;
            $topic_id = !empty($_POST['topic_id']) ? (int)$_POST['topic_id'] : null;
            $status = isset($_POST['is_published']) && $_POST['is_published'] ? 'published' : 'draft';
            
            if (empty($title) || empty($youtube_url)) jsonError('Title and YouTube URL are required', 400);
            
            if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $youtube_url)) {
                jsonError('Invalid YouTube URL', 400);
            }
            
            // Auto-generate thumbnail from YouTube if not provided
            if (empty($thumbnail_url)) {
                $video_id = extractYouTubeId($youtube_url);
                if ($video_id) {
                    $thumbnail_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
                }
            }
            
            // Get current published status
            $stmt = $pdo->prepare("SELECT status, published_at FROM episodes WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $published_at = $current['published_at'];
            if ($status === 'published' && $current['status'] !== 'published') {
                $published_at = date('Y-m-d H:i:s');
            } elseif ($status !== 'published') {
                $published_at = null;
            }
            
            $stmt = $pdo->prepare("UPDATE episodes SET title = ?, description = ?, youtube_url = ?, thumbnail_url = ?, scripture_reference = ?, episode_number = ?, duration = ?, topic_id = ?, status = ?, published_at = ? WHERE id = ?");
            $stmt->execute([$title, $description, $youtube_url, $thumbnail_url, $scripture_reference, $episode_number, $duration, $topic_id, $status, $published_at, $id]);
            
            jsonSuccess(['message' => 'Episode updated successfully']);
        }
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Episodes API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}

/**
 * Extract YouTube Video ID from URL
 * @param string $url YouTube URL
 * @return string|null Video ID or null if not found
 */
function extractYouTubeId($url) {
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}
