<?php
/**
 * Life is Character Radio - Admin Header
 * 
 * Location: /admin/includes/admin_header.php
 * 
 * Common admin navigation with permission-based menu visibility
 */

if (!defined('BASE_URL')) die('Direct access not permitted');

$current_user = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - Admin - Life is Character Radio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Cinzel', serif; }
        .gradient-bg { background: linear-gradient(135deg, #001A2E 0%, #006B8F 100%); }
        .sidebar { background: linear-gradient(180deg, #1F2937 0%, #111827 100%); }
        .nav-link { transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(0, 229, 255, 0.1); border-left: 3px solid #00E5FF; }
        .glow { box-shadow: 0 0 20px rgba(0, 229, 255, 0.2); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="sidebar w-64 flex-shrink-0 overflow-y-auto">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">
                    Life is Character
                </h1>
                <p class="text-gray-400 text-sm mt-1">Admin Portal</p>
            </div>

            <nav class="px-3 pb-6">
                <a href="<?= BASE_URL ?>admin" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-dashboard w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                <?php if (hasPermission('manage_events') || isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/events" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'events' ? 'active' : '' ?>">
                    <i class="fas fa-calendar w-5"></i>
                    <span class="ml-3">Events</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('manage_donations') || isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/donations" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'donations' ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-heart w-5"></i>
                    <span class="ml-3">Donations</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('manage_blog') || isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/blog" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'blog' ? 'active' : '' ?>">
                    <i class="fas fa-blog w-5"></i>
                    <span class="ml-3">Blog</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('manage_episodes') || isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/episodes" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'episodes' ? 'active' : '' ?>">
                    <i class="fas fa-video w-5"></i>
                    <span class="ml-3">Episodes</span>
                </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>admin/contact" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'contact' ? 'active' : '' ?>">
                    <i class="fas fa-envelope w-5"></i>
                    <span class="ml-3">Contact</span>
                </a>

                <a href="<?= BASE_URL ?>admin/prayers" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'prayers' ? 'active' : '' ?>">
                    <i class="fas fa-praying-hands w-5"></i>
                    <span class="ml-3">Prayer Requests</span>
                </a>

                <a href="<?= BASE_URL ?>admin/newsletter" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'newsletter' ? 'active' : '' ?>">
                    <i class="fas fa-mail-bulk w-5"></i>
                    <span class="ml-3">Newsletter</span>
                </a>

                <?php if (hasPermission('manage_users') || isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/users" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">Users</span>
                </a>
                <?php endif; ?>

                <?php if (isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>admin/admins" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'admins' ? 'active' : '' ?>">
                    <i class="fas fa-user-shield w-5"></i>
                    <span class="ml-3">Admins</span>
                </a>

                <a href="<?= BASE_URL ?>admin/roles" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'roles' ? 'active' : '' ?>">
                    <i class="fas fa-key w-5"></i>
                    <span class="ml-3">Roles</span>
                </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>admin/transactions" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'transactions' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar w-5"></i>
                    <span class="ml-3">Transactions</span>
                </a>

                <div class="border-t border-gray-700 my-4"></div>

                <a href="<?= BASE_URL ?>admin/profile" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg mb-1 <?= $currentPage === 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user-circle w-5"></i>
                    <span class="ml-3">Profile</span>
                </a>

                <a href="<?= BASE_URL ?>admin/logout" class="nav-link flex items-center px-4 py-3 text-gray-300 rounded-lg hover:bg-red-500/10 hover:border-red-500">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </nav>

            <div class="px-6 pb-6">
                <div class="bg-gradient-to-r from-cyan-500/10 to-blue-500/10 border border-cyan-500/20 rounded-lg p-4">
                    <p class="text-sm text-gray-300 font-medium"><?= htmlspecialchars($current_user['name'] ?? '') ?></p>
                    <p class="text-xs text-cyan-400"><?= htmlspecialchars($current_user['role'] ?? '') ?></p>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-2xl font-semibold text-gray-800"><?= $pageTitle ?? 'Dashboard' ?></h2>
                    <a href="<?= BASE_URL ?>" target="_blank" class="text-cyan-600 hover:text-cyan-700 flex items-center">
                        <i class="fas fa-external-link-alt mr-2"></i>View Site
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
