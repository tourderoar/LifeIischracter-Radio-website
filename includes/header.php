<?php
/**
 * Life is Character Radio - Header Component
 * 
 * Location: /includes/header.php
 * 
 * Common header and navigation for all public pages
 */

// Ensure config is loaded
if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/auth.php';

// Get current page for active nav link
$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF'], '.php');
$isActive = fn($page) => $currentPage === $page ? '!text-brand-cyan' : '';
$isLoggedIn = isUserLoggedIn();
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Home' ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= $pageDescription ?? 'Faith-based radio and TV broadcast program. Life is a script, win with character through God\'s Word.' ?>">
    <?php if (!empty($pageRobots)): ?>
    <meta name="robots" content="<?= htmlspecialchars($pageRobots) ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= $pageTitle ?? 'Home' ?> - <?= SITE_NAME ?>">
    <meta property="og:description" content="<?= $pageDescription ?? 'Faith-based radio and TV broadcast program.' ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>logo.png">
    <link rel="shortcut icon" href="<?= BASE_URL ?>logo.png">
    
    <!-- Stylesheets -->
    <script src="https://cdn.tailwindcss.com/"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>css/styles.css" rel="stylesheet">
    
    <!-- Tailwind Config -->
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'brand': {
                'cyan': '#00E5FF',
                'blue': '#0099CC',
                'dark': '#006B8F',
                'navy': '#001A2E',
                'gold': '#FFD700',
                'silver': '#C0C0C0'
              },
              'spotify': '#1DB954',
              'youtube': '#FF0000'
            },
            fontFamily: {
              'cinematic': ['Georgia', 'Times New Roman', 'serif'],
              'modern': ['Inter', 'system-ui', 'sans-serif']
            }
          }
        }
      }
    </script>
    
    <script>
        // Make Stripe publishable key available globally (Stripe.js itself is
        // only loaded on pages that process payments, e.g. donate/events)
        const STRIPE_PUBLISHABLE_KEY = '<?= STRIPE_PUBLISHABLE_KEY ?>';
        // User authentication status
        const USER_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
    </script>
</head>
<body class="bg-gray-900 text-white font-modern">
    <!-- Navigation -->
    <nav class="bg-gray-900/95 backdrop-blur-sm border-b border-brand-cyan/20 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>">
                        <img src="<?= BASE_URL ?>logo.png" alt="<?= SITE_NAME ?>" class="h-10 w-10 mr-3 logo-glow">
                    </a>
                    <a href="<?= BASE_URL ?>" class="font-bold text-xl text-brand-cyan"><?= SITE_NAME ?></a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?= BASE_URL ?>" class="nav-link text-gray-300 hover:text-brand-cyan transition-colors <?= $isActive('index') ?>">Home</a>
                    <a href="<?= BASE_URL ?>about" class="nav-link text-gray-300 hover:text-brand-cyan transition-colors <?= $isActive('about') ?>">About</a>
                    <a href="<?= BASE_URL ?>episodes" class="nav-link text-gray-300 hover:text-brand-cyan transition-colors <?= $isActive('episodes') ?>">Episodes</a>
                    <a href="<?= BASE_URL ?>blog" class="nav-link text-gray-300 hover:text-brand-cyan transition-colors <?= $isActive('blog') ?>">Blog</a>
                    <a href="<?= BASE_URL ?>events" class="nav-link text-gray-300 hover:text-brand-cyan transition-colors <?= $isActive('events') ?>">Events</a>
                    <a href="<?= BASE_URL ?>contact" class="nav-link text-gray-300 hover:text-brand-cyan transition-colors <?= $isActive('contact') ?>">Contact</a>
                    <a href="<?= BASE_URL ?>donate" class="bg-brand-cyan text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-brand-blue transition-colors">Donate</a>
                    
                    <?php if ($isLoggedIn): ?>
                        <div class="relative group">
                            <button class="text-gray-300 hover:text-brand-cyan transition-colors flex items-center gap-2">
                                <i class="fas fa-user-circle"></i>
                                <span><?= htmlspecialchars(explode(' ', $userName)[0]) ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <a href="<?= BASE_URL ?>account/dashboard" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-brand-cyan rounded-t-lg">
                                    <i class="fas fa-tachometer-alt w-5"></i> Dashboard
                                </a>
                                <a href="<?= BASE_URL ?>account/profile" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-brand-cyan">
                                    <i class="fas fa-user w-5"></i> Profile
                                </a>
                                <a href="<?= BASE_URL ?>account/transactions" class="block px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-brand-cyan">
                                    <i class="fas fa-receipt w-5"></i> Transactions
                                </a>
                                <div class="border-t border-gray-700"></div>
                                <a href="<?= BASE_URL ?>account/logout" class="block px-4 py-2 text-red-400 hover:bg-gray-700 rounded-b-lg">
                                    <i class="fas fa-sign-out-alt w-5"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>account/login" class="text-gray-300 hover:text-brand-cyan transition-colors">Login</a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-300 hover:text-brand-cyan">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-gray-900/95 backdrop-blur-sm fixed w-full z-40 mt-16 border-b border-brand-cyan/20">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="<?= BASE_URL ?>" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan <?= $isActive('index') ?>">Home</a>
            <a href="<?= BASE_URL ?>about" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan <?= $isActive('about') ?>">About</a>
            <a href="<?= BASE_URL ?>episodes" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan <?= $isActive('episodes') ?>">Episodes</a>
            <a href="<?= BASE_URL ?>blog" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan <?= $isActive('blog') ?>">Blog</a>
            <a href="<?= BASE_URL ?>events" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan <?= $isActive('events') ?>">Events</a>
            <a href="<?= BASE_URL ?>contact" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan <?= $isActive('contact') ?>">Contact</a>
            <a href="<?= BASE_URL ?>donate" class="block px-3 py-2 bg-brand-cyan text-gray-900 rounded-lg font-semibold">Donate</a>
            
            <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>account/dashboard" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="<?= BASE_URL ?>account/profile" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan">
                    <i class="fas fa-user mr-2"></i>Profile
                </a>
                <a href="<?= BASE_URL ?>account/logout" class="block px-3 py-2 text-red-400 hover:text-red-300">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>account/login" class="block px-3 py-2 text-gray-300 hover:text-brand-cyan">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Page Content -->
