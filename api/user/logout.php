<?php
/**
 * Life is Character Radio - User Logout API
 * Location: /api/user/logout.php
 * Method: POST
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

session_unset();
session_destroy();

jsonSuccess(['redirect' => BASE_URL], 'Logged out successfully');
