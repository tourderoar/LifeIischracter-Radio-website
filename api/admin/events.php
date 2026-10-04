<?php
/**
 * Life is Character Radio - Events API
 * 
 * Location: /api/admin/events.php
 * 
 * GET: List all events with filters
 * POST: Create new event
 * PUT: Update event
 * DELETE: Delete event (soft delete - set status to cancelled)
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php'; // Includes auth.php and response.php
require_once __DIR__ . '/../../includes/upload.php';

header('Content-Type: application/json');

requireAdminAuthAPI();
requirePermissionAPI('manage_events');

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
        // Check for action parameter
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'registrations') {
            // Fetch all event registrations with user and event details
            $sql = "SELECT 
                        er.*, 
                        e.title as event_title,
                        e.event_date,
                        u.full_name as user_name,
                        u.email as user_email
                    FROM event_registrations er
                    JOIN events e ON er.event_id = e.id
                    JOIN users u ON er.user_id = u.id
                    ORDER BY er.registered_at DESC";
            
            $stmt = $pdo->query($sql);
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            jsonSuccess(['registrations' => $registrations]);
        }
        
        // List events with optional filters
        $status = $_GET['status'] ?? null;
        $limit = min((int)($_GET['limit'] ?? 50), 100);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $sql = "SELECT e.*, a.full_name as created_by_name,
                (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id AND er.payment_status = 'completed') as current_registrations
                FROM events e 
                JOIN admins a ON e.created_by = a.id WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND e.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.event_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM events WHERE 1=1" . ($status ? " AND status = ?" : "");
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($status ? [$status] : []);
        $total = $countStmt->fetchColumn();
        
        jsonSuccess(['events' => $events, 'total' => $total, 'limit' => $limit, 'offset' => $offset]);
        
    } elseif ($method === 'DELETE') {
        // Soft delete - set status to cancelled
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Event ID required', 400);
        
        $stmt = $pdo->prepare("UPDATE events SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonSuccess(['message' => 'Event cancelled successfully']);
        
    } elseif ($method === 'PUT') {
        // Update event
        validateCsrfTokenAPI();
        
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Event ID required', 400);
        
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $cost = (float)($_POST['cost'] ?? 0);
        $max_capacity = !empty($_POST['max_capacity']) ? (int)$_POST['max_capacity'] : null;
        $status = $_POST['status'] ?? 'upcoming';
        
        if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
            jsonError('Title, description, date and location are required', 400);
        }
        
        // Handle image upload
        $featured_image = null;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['featured_image'], 'events');
            if (!$upload['success']) {
                jsonError($upload['error'], 400);
            }
            $featured_image = $upload['path'];
            
            // Delete old image if exists
            $stmt = $pdo->prepare("SELECT featured_image FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $oldImage = $stmt->fetchColumn();
            if ($oldImage) {
                deleteImage($oldImage);
            }
        }
        
        if ($featured_image) {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, featured_image = ?, event_date = ?, location = ?, cost = ?, status = ?, max_capacity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $description, $featured_image, $event_date, $location, $cost, $status, $max_capacity, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, cost = ?, status = ?, max_capacity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $description, $event_date, $location, $cost, $status, $max_capacity, $id]);
        }
        
        jsonSuccess(['message' => 'Event updated successfully']);
        
    } elseif ($method === 'POST') {
        // Create new event
        validateCsrfTokenAPI();
        
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $cost = (float)($_POST['cost'] ?? 0);
        $max_capacity = !empty($_POST['max_capacity']) ? (int)$_POST['max_capacity'] : null;
        $status = $_POST['status'] ?? 'upcoming';
        
        if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
            jsonError('Title, description, date and location are required', 400);
        }
        
        // Handle image upload
        $featured_image = null;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['featured_image'], 'events');
            if (!$upload['success']) {
                jsonError($upload['error'], 400);
            }
            $featured_image = $upload['path'];
        }
        
        $stmt = $pdo->prepare("INSERT INTO events (title, description, featured_image, event_date, location, cost, status, max_capacity, created_by, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $description, $featured_image, $event_date, $location, $cost, $status, $max_capacity, $_SESSION['admin_id']]);
        
        jsonSuccess(['id' => $pdo->lastInsertId(), 'message' => 'Event created successfully']);
        
    } else {
        jsonError('Method not allowed', 405);
    }
} catch (PDOException $e) {
    error_log('Events API error: ' . $e->getMessage());
    jsonError('An error occurred. Please try again.', 500);
}
