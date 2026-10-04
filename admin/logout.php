<?php
/**
 * Life is Character Radio - Admin Logout
 * 
 * Location: /admin/logout.php
 * 
 * Destroy admin session and redirect to login
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

logout();

header('Location: ' . BASE_URL . 'admin/login');
exit;
