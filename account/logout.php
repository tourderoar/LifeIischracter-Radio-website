<?php
/**
 * Life is Character Radio - User Logout
 * Location: /account/logout.php
 */

require_once __DIR__ . '/../includes/config.php';

session_unset();
session_destroy();

header('Location: ' . ($_GET['redirect'] ?? BASE_URL));
exit;
