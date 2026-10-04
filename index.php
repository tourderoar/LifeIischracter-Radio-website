<?php
/**
 * Life is Character Radio - Homepage
 * Location: /index.php
 */
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Home - Faith. Character. Victory.';
$pageDescription = 'Faith-based radio and TV broadcast program. Life is a script, win with character through God\'s Word.';
$currentPage = 'index';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="min-h-screen flex items-center justify-center relative overflow-hidden pt-32 md:pt-40">
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-brand-navy to-gray-800"></div>
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-brand-cyan/10 rounded-full blur-3xl animate-pulse"></div>
    
    <div class="relative z-10 text-center px-4">
        <div class="mb-8">
            <img src="<?= BASE_URL ?>logo.jpeg" alt="Life is Character Radio" class="h-56 md:h-72 w-auto mx-auto mb-6 drop-shadow-2xl">
        </div>
        <h1 class="font-cinematic text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-brand-cyan to-brand-blue bg-clip-text text-transparent">
            Life is Character Radio
        </h1>
        <p class="text-xl md:text-2xl text-gray-300 mb-4 max-w-3xl mx-auto">
            Faith-based radio and television broadcast program
        </p>
        <p class="text-2xl md:text-3xl font-bold text-brand-cyan mb-8">
            Life is a Script. Win with Character.
        </p>
        <p class="text-lg text-gray-400 mb-12 max-w-2xl mx-auto">
            Every human being plays roles on the stage of life, but true victory comes from studying and living out the script of God's Word.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="https://life-is-character.mixlr.com/" class="bg-brand-cyan text-gray-900 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-brand-blue transition-all transform hover:scale-105 shadow-lg hover:shadow-brand-cyan/25">
                <i class="fas fa-play mr-2"></i>Listen Now
            </a>
            <a href="<?= BASE_URL ?>episodes" class="border-2 border-brand-cyan text-brand-cyan px-8 py-4 rounded-lg font-semibold text-lg hover:bg-brand-cyan hover:text-gray-900 transition-all">
                <i class="fas fa-video mr-2"></i>Watch Now
            </a>
            <a href="https://open.spotify.com/show/4TGAAkFbSA8U0fCOz3L0Fj" target="_blank" class="border-2 border-spotify text-spotify px-8 py-4 rounded-lg font-semibold text-lg hover:bg-spotify hover:text-gray-900 transition-all">
                <i class="fab fa-spotify mr-2"></i>Listen on Spotify
            </a>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-brand-cyan animate-bounce">
        <i class="fas fa-chevron-down text-2xl"></i>
    </div>
</section>

<!-- Mission Statement -->
<section class="py-20 bg-gray-800">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold font-cinematic mb-8 text-brand-cyan">Our Mission</h2>
        <p class="text-xl text-gray-300 leading-relaxed mb-8">
            We empower listeners to live victorious, character-driven lives through the transformative power of God's Word. 
            Using the powerful metaphor of "life as a script," we help people understand their roles and find victory 
            through faith, character, and spirit-filled living.
        </p>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-brand-cyan text-gray-900 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-book-open text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Scripture-Rooted</h3>
                <p class="text-gray-400">Wisdom and strategies anchored in God's Word</p>
            </div>
            <div class="text-center">
                <div class="bg-brand-cyan text-gray-900 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-trophy text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Character-Driven</h3>
                <p class="text-gray-400">Empowering victorious living through character</p>
            </div>
            <div class="text-center">
                <div class="bg-brand-cyan text-gray-900 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-heart text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Hope & Guidance</h3>
                <p class="text-gray-400">Bringing hope for life's journey</p>
            </div>
        </div>
    </div>
</section>

<!-- Recent Episodes Preview -->
<section class="py-20 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold font-cinematic mb-6 text-brand-cyan">Latest Episodes</h2>
            <p class="text-xl text-gray-300">Discover wisdom for character-driven living</p>
        </div>

        <div id="featured-episodes" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
            <!-- Episodes loaded via JavaScript -->
        </div>

        <div class="text-center">
            <a href="<?= BASE_URL ?>episodes" class="bg-brand-cyan text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                View All Episodes
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/devotional-signup.php'; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
