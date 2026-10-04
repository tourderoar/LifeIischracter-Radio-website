<?php
/**
 * Life is Character Radio - Episode Topics API
 * Location: /api/episodes/topics.php
 * Method: GET
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT t.id, t.name, t.slug, t.description, 
                         (SELECT COUNT(*) FROM episodes e WHERE e.topic_id = t.id) as episode_count 
                         FROM episode_topics t 
                         WHERE t.is_active = 1 
                         ORDER BY t.sort_order ASC");
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($topics as &$topic) {
        $topic['episode_count'] = (int)$topic['episode_count'];
    }
    
    jsonSuccess($topics);
} catch (Exception $e) {
    error_log("Episode Topics API error: " . $e->getMessage());
    jsonError('Failed to fetch topics', 500);
}
