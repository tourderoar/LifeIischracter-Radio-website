<?php
/**
 * Life is Character Radio - Blog Post Detail Page
 * Location: /blog-post.php
 * 
 * Individual blog post view
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// Get slug from URL parameter
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: ' . BASE_URL . 'blog');
    exit;
}

// Fetch post data from API
$apiUrl = BASE_URL . 'api/blog/post/?slug=' . urlencode($slug);
$response = @file_get_contents($apiUrl);

if (!$response) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Post Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<div class="min-h-screen flex items-center justify-center bg-gray-900"><div class="text-center"><h1 class="text-4xl text-brand-cyan mb-4">Post Not Found</h1><p class="text-gray-300 mb-8">The blog post you\'re looking for doesn\'t exist.</p><a href="' . BASE_URL . 'blog" class="bg-brand-cyan text-gray-900 px-6 py-3 rounded-lg">Back to Blog</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$data = json_decode($response, true);

if (!$data['success']) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Post Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<div class="min-h-screen flex items-center justify-center bg-gray-900"><div class="text-center"><h1 class="text-4xl text-brand-cyan mb-4">Post Not Found</h1><p class="text-gray-300 mb-8">The blog post you\'re looking for doesn\'t exist.</p><a href="' . BASE_URL . 'blog" class="bg-brand-cyan text-gray-900 px-6 py-3 rounded-lg">Back to Blog</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$post = $data['data'];
$pageTitle = $post['title'] . ' - Character Blog';
$pageDescription = $post['excerpt'] ?? 'Read this inspiring article on Life is Character Radio';

include __DIR__ . '/includes/header.php';
?>

<style>
    .blog-content p {
        margin-bottom: 1.5rem;
        line-height: 1.8;
    }
    .blog-content p:last-child {
        margin-bottom: 0;
    }
    .blog-content h2 {
        color: #00E5FF;
        font-size: 1.75rem;
        font-weight: bold;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-family: 'Cinzel', Georgia, serif;
    }
    .blog-content h3 {
        color: #00E5FF;
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-family: 'Cinzel', Georgia, serif;
    }
    .blog-content ul, .blog-content ol {
        margin-left: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .blog-content li {
        margin-bottom: 0.5rem;
    }
    .blog-content strong {
        color: #00E5FF;
        font-weight: 600;
    }
    .blog-content em {
        font-style: italic;
        color: #FFD700;
    }
    .blog-content a {
        color: #00E5FF;
        text-decoration: underline;
    }
    .blog-content a:hover {
        color: #0099CC;
    }
</style>

<article class="min-h-screen bg-gray-900 pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Back to Blog -->
        <div class="mb-8">
            <a href="<?= BASE_URL ?>blog" class="text-brand-cyan hover:text-brand-blue transition-colors font-['Inter']">
                <i class="fas fa-arrow-left mr-2"></i>Back to Blog
            </a>
        </div>

        <!-- Featured Image -->
        <?php if ($post['featured_image']): ?>
        <div class="mb-8 rounded-2xl overflow-hidden shadow-2xl">
            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-auto object-cover">
        </div>
        <?php endif; ?>

        <!-- Post Header -->
        <header class="mb-8">
            <h1 class="font-['Cinzel'] text-4xl md:text-5xl text-brand-cyan mb-4"><?= htmlspecialchars($post['title']) ?></h1>
            
            <!-- Meta Info -->
            <div class="flex flex-wrap gap-4 items-center text-gray-400 font-['Inter'] text-sm mb-6">
                <span><i class="fas fa-user mr-2"></i><?= htmlspecialchars($post['author_name']) ?></span>
                <span><i class="far fa-calendar mr-2"></i><?= date('F j, Y', strtotime($post['published_at'])) ?></span>
            </div>

            <!-- Scripture Reference -->
            <?php if ($post['scripture_reference']): ?>
            <div class="bg-gradient-to-r from-[#001A2E] to-[#006B8F] rounded-xl p-6 mb-6">
                <p class="text-[#FFD700] font-['Cinzel'] text-lg italic">
                    <i class="fas fa-book-open mr-2"></i><?= htmlspecialchars($post['scripture_reference']) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Categories -->
            <?php if (!empty($post['categories'])): ?>
            <div class="flex flex-wrap gap-2 mb-6">
                <?php foreach ($post['categories'] as $category): ?>
                <a href="<?= BASE_URL ?>blog?category=<?= urlencode($category['slug']) ?>" 
                   class="bg-[#00E5FF]/10 text-[#00E5FF] px-4 py-2 rounded-full text-sm font-['Inter'] hover:bg-[#00E5FF] hover:text-[#001A2E] transition-colors">
                    <?= htmlspecialchars($category['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </header>

        <!-- Post Content -->
        <div class="prose prose-invert prose-lg max-w-none">
            <div class="blog-content text-gray-300 font-['Inter'] leading-relaxed text-lg">
                <?= $post['content'] ?>
            </div>
        </div>

        <!-- Tags -->
        <?php if (!empty($post['tags'])): ?>
        <div class="mt-12 pt-8 border-t border-gray-700">
            <h3 class="text-gray-400 font-['Inter'] text-sm uppercase tracking-wider mb-4">Tags</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($post['tags'] as $tag): ?>
                <span class="bg-gray-800 text-gray-300 px-3 py-1 rounded text-sm font-['Inter']">
                    #<?= htmlspecialchars($tag) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Share Section -->
        <div class="mt-12 pt-8 border-t border-gray-700">
            <h3 class="text-gray-300 font-['Inter'] text-lg mb-4">Share this article</h3>
            <div class="flex gap-4">
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . 'blog/' . $post['slug']) ?>&text=<?= urlencode($post['title']) ?>" 
                   target="_blank" 
                   class="bg-[#1DA1F2] text-white px-6 py-3 rounded-lg hover:opacity-80 transition-opacity">
                    <i class="fab fa-twitter mr-2"></i>Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . 'blog/' . $post['slug']) ?>" 
                   target="_blank" 
                   class="bg-[#1877F2] text-white px-6 py-3 rounded-lg hover:opacity-80 transition-opacity">
                    <i class="fab fa-facebook mr-2"></i>Facebook
                </a>
                <a href="https://www.linkedin.com/shareArticle?url=<?= urlencode(BASE_URL . 'blog/' . $post['slug']) ?>&title=<?= urlencode($post['title']) ?>" 
                   target="_blank" 
                   class="bg-[#0A66C2] text-white px-6 py-3 rounded-lg hover:opacity-80 transition-opacity">
                    <i class="fab fa-linkedin mr-2"></i>LinkedIn
                </a>
            </div>
        </div>

        <!-- Back to Blog (Bottom) -->
        <div class="mt-12 text-center">
            <a href="<?= BASE_URL ?>blog" class="bg-brand-cyan text-gray-900 px-8 py-3 rounded-lg font-['Inter'] font-bold hover:bg-brand-blue transition-colors inline-block">
                <i class="fas fa-arrow-left mr-2"></i>Back to All Posts
            </a>
        </div>
    </div>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>
