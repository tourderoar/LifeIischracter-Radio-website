<?php
/**
 * Life is Character Radio - Episodes Page
 * 
 * Location: /episodes.php
 * 
 * Description:
 * Episodes listing page with filtering capabilities. Episodes loaded dynamically via API.
 * Features topic filters, status filters, and an embedded audio player.
 */

require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Episodes - Faith & Character Building';
$currentPage = 'episodes';
include __DIR__ . '/includes/header.php';
?>

<section class="pt-24 pb-20 bg-gray-800">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold font-cinematic mb-6 text-brand-cyan">Episodes</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto mb-8">
                Discover wisdom for character-driven living through our faith-based episodes
            </p>
            <a href="https://www.youtube.com/@lifeischaracterradio959" target="_blank" class="inline-block border-2 border-youtube text-youtube px-6 py-3 rounded-lg font-semibold hover:bg-youtube hover:text-white transition-all">
                <i class="fab fa-youtube mr-2"></i>Watch on YouTube
            </a>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap gap-4 mb-8 justify-center">
            <select id="topic-filter" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                <option value="">All Topics</option>
                <!-- Topics loaded dynamically via JavaScript -->
            </select>
            <select id="status-filter" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                <option value="published">Published</option>
                <option value="upcoming">Coming Soon</option>
            </select>
            <button id="clear-filters" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition-colors">
                <i class="fas fa-redo mr-2"></i>Reset Filters
            </button>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <i class="fas fa-spinner animate-spin text-4xl text-brand-cyan mb-4"></i>
            <p class="text-gray-400">Loading episodes...</p>
        </div>

        <!-- Episodes Grid -->
        <div id="episodes-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 hidden">
            <!-- Episodes will be loaded here via JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <i class="fas fa-podcast text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">No episodes found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back soon for new content</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/devotional-signup.php'; ?>

<!-- Audio Player (Hidden by default) -->
<div id="audio-player" class="hidden fixed bottom-0 left-0 right-0 bg-gray-800 border-t border-gray-700 z-40 shadow-2xl">
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4 flex-1">
                <button id="play-pause-btn" class="bg-brand-cyan text-gray-900 p-3 rounded-full hover:bg-brand-blue transition-colors flex-shrink-0">
                    <i class="fas fa-play text-lg"></i>
                </button>
                <div class="flex-1 min-w-0">
                    <h4 class="text-white font-semibold truncate" id="player-title">Episode Title</h4>
                    <p class="text-gray-400 text-sm truncate" id="player-subtitle">Scripture Reference</p>
                </div>
            </div>
            <button id="close-player" class="text-gray-400 hover:text-white ml-4">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
