<?php
/**
 * Life is Character Radio - Admin Dashboard API
 * 
 * Location: /api/admin/dashboard.php
 * 
 * GET: Get dashboard statistics
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/middleware.php'; // Includes auth.php and response.php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

requireAdminAuthAPI();

try {
    $pdo = getDB();
    
    // Events stats
    $upcomingEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'upcoming' AND event_date > NOW()")->fetchColumn();
    $pastEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'past' OR (status = 'upcoming' AND event_date <= NOW())")->fetchColumn();
    
    // Users stats
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    
    // Donations stats
    $totalDonations = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = 'completed'")->fetchColumn();
    $totalDonationsCount = $pdo->query("SELECT COUNT(*) FROM donations WHERE payment_status = 'completed'")->fetchColumn();
    
    // Blog & Episodes stats
    $publishedPosts = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn();
    $publishedEpisodes = $pdo->query("SELECT COUNT(*) FROM episodes WHERE status = 'published'")->fetchColumn();
    
    // Recent contact submissions
    $recentContacts = $pdo->query("
        SELECT name, email, subject, submitted_at 
        FROM contact_submissions 
        ORDER BY submitted_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent transactions
    $recentTransactions = $pdo->query("
        SELECT t.*, u.full_name, u.email 
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.payment_status = 'completed'
        ORDER BY t.created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    jsonSuccess([
        'stats' => [
            'events' => [
                'upcoming' => (int)$upcomingEvents,
                'past' => (int)$pastEvents
            ],
            'users' => [
                'total' => (int)$totalUsers,
                'active' => (int)$activeUsers
            ],
            'donations' => [
                'total_amount' => (float)$totalDonations,
                'count' => (int)$totalDonationsCount
            ],
            'content' => [
                'episodes' => (int)$publishedEpisodes,
                'posts' => (int)$publishedPosts
            ]
        ],
        'recent' => [
            'contacts' => $recentContacts,
            'transactions' => $recentTransactions
        ]
    ]);
} catch (PDOException $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    jsonError('Failed to fetch dashboard data', 500);
}
