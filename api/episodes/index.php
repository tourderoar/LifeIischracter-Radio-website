<?php
/**
 * Life is Character Radio - Episodes API
 * Location: /api/episodes/index.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

try {
    $pdo = getDB();
    $topicId = $_GET['topic_id'] ?? null;
    $status = $_GET['status'] ?? 'published';
    $limit = min((int)($_GET['limit'] ?? 20), 100);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $sql = "SELECT e.id, e.title, e.description, e.youtube_url, e.thumbnail_url, e.scripture_reference, 
            e.episode_number, e.duration, e.published_at, 
            t.id as topic_id, t.name as topic_name, t.slug as topic_slug 
            FROM episodes e 
            LEFT JOIN episode_topics t ON e.topic_id = t.id 
            WHERE e.status = ?";
    
    $params = [$status];
    if ($topicId) {
        $sql .= " AND e.topic_id = ?";
        $params[] = $topicId;
    }
    
    $sql .= " ORDER BY e.episode_number DESC, e.published_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($episodes as &$ep) {
        $ep['topic'] = $ep['topic_id'] ? [
            'id' => (int)$ep['topic_id'],
            'name' => $ep['topic_name'],
            'slug' => $ep['topic_slug']
        ] : null;
        unset($ep['topic_id'], $ep['topic_name'], $ep['topic_slug']);
        $ep['episode_number'] = $ep['episode_number'] ? (int)$ep['episode_number'] : null;
        $ep['duration'] = $ep['duration'] ? (int)$ep['duration'] : null;
    }
    
    jsonSuccess($episodes);
} catch (Exception $e) {
    error_log("Episodes API error: " . $e->getMessage());
    jsonError('Failed to fetch episodes', 500);
}
