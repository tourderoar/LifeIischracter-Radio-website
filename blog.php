<?php
/**
 * Life is Character Radio - Blog Page
 * 
 * Location: /blog.php
 * 
 * Description:
 * Blog listing page with category filtering. Posts loaded dynamically via API.
 * Features category filters and responsive grid layout.
 */

require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Character Blog - Inspirational Articles';
$currentPage = 'blog';
include __DIR__ . '/includes/header.php';
?>

<section class="pt-24 pb-20 bg-gray-800">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold font-cinematic mb-6 text-brand-cyan">Character Blog</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Inspirational articles and devotionals for character-driven living
            </p>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap gap-4 mb-8 justify-center items-center">
            <select id="blog-filter" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                <option value="">All Categories</option>
                <!-- Categories loaded dynamically via JavaScript -->
            </select>
            <button id="clear-blog-filters" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition-colors">
                <i class="fas fa-redo mr-2"></i>Reset
            </button>
            <div class="text-gray-300 px-4 py-2 bg-gray-700/50 rounded-lg">
                <i class="fas fa-newspaper mr-2"></i>
                <span id="post-count">Loading articles...</span>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <i class="fas fa-spinner animate-spin text-4xl text-brand-cyan mb-4"></i>
            <p class="text-gray-400">Loading blog posts...</p>
        </div>

        <!-- Blog Posts Grid -->
        <div id="blog-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 hidden">
            <!-- Blog posts will be loaded here via JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <i class="fas fa-file-alt text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">No blog posts found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back soon for new content</p>
        </div>
    </div>
</section>

<script>
// Blog page functionality
document.addEventListener('DOMContentLoaded', () => {
    const blogGrid = document.getElementById('blog-grid');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const blogFilter = document.getElementById('blog-filter');
    const clearFilters = document.getElementById('clear-blog-filters');
    const postCount = document.getElementById('post-count');

    // Load blog categories into the filter dropdown
    async function loadBlogCategories() {
        try {
            const response = await fetch('<?php echo BASE_URL; ?>api/blog/categories');
            const data = await response.json();

            if (data.success) {
                const categoriesHTML = data.data.map(c =>
                    `<option value="${c.id}">${c.name} (${c.post_count})</option>`
                ).join('');
                blogFilter.innerHTML = `<option value="">All Categories</option>${categoriesHTML}`;
            }
        } catch (error) {
            console.error('Failed to load blog categories:', error);
        }
    }

    // Load blog posts function
    async function loadBlogPosts() {
        loadingState.classList.remove('hidden');
        blogGrid.classList.add('hidden');
        emptyState.classList.add('hidden');

        try {
            const categoryId = blogFilter.value;
            const params = new URLSearchParams();
            if (categoryId) params.append('category_id', categoryId);

            const response = await fetch(`<?php echo BASE_URL; ?>api/blog/?${params}`);
            const data = await response.json();

            loadingState.classList.add('hidden');

            if (data.success && data.data.length > 0) {
                renderBlogPosts(data.data);
                blogGrid.classList.remove('hidden');
                postCount.textContent = `${data.data.length} article${data.data.length !== 1 ? 's' : ''}`;
            } else {
                emptyState.classList.remove('hidden');
                postCount.textContent = '0 articles';
            }
        } catch (error) {
            console.error('Error loading blog posts:', error);
            loadingState.classList.add('hidden');
            emptyState.classList.remove('hidden');
            postCount.textContent = 'Error loading';
        }
    }

    // Render blog posts
    function renderBlogPosts(posts) {
        blogGrid.innerHTML = posts.map(post => `
            <article class="bg-gray-700/50 rounded-lg border border-brand-cyan/20 hover:border-brand-cyan transition-all overflow-hidden group">
                ${post.featured_image ? 
                    `<div class="relative aspect-video bg-gray-800 overflow-hidden">
                        <img src="${post.featured_image}" alt="${post.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 to-transparent"></div>
                    </div>` :
                    `<div class="aspect-video bg-gradient-to-br from-brand-cyan/20 to-brand-blue/20 flex items-center justify-center">
                        <i class="fas fa-book-open text-6xl text-brand-cyan/30"></i>
                    </div>`
                }
                <div class="p-6">
                    ${post.tags && post.tags.length ? 
                        `<div class="flex flex-wrap gap-2 mb-3">
                            ${post.tags.slice(0, 3).map(tag => 
                                `<span class="text-xs bg-brand-cyan/20 text-brand-cyan px-2 py-1 rounded">${tag}</span>`
                            ).join('')}
                        </div>` : ''
                    }
                    <h3 class="text-xl font-bold text-white mb-2 line-clamp-2 group-hover:text-brand-cyan transition-colors">${post.title}</h3>
                    ${post.scripture_reference ? 
                        `<p class="text-brand-cyan text-sm mb-3">
                            <i class="fas fa-book mr-1"></i>${post.scripture_reference}
                        </p>` : ''
                    }
                    ${post.excerpt ? 
                        `<p class="text-gray-400 text-sm mb-4 line-clamp-3">${post.excerpt}</p>` : ''
                    }
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                        <span>
                            <i class="fas fa-user mr-1"></i>${post.author_name || 'Paul Kumeni Agbo'}
                        </span>
                        ${post.published_at ? 
                            `<span><i class="far fa-calendar mr-1"></i>${new Date(post.published_at).toLocaleDateString()}</span>` : ''
                        }
                    </div>
                    <a href="<?php echo BASE_URL; ?>blog/${post.slug}" class="inline-flex items-center text-brand-cyan hover:text-brand-blue transition-colors">
                        Read More <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </article>
        `).join('');
    }

    // Filter events
    blogFilter.addEventListener('change', loadBlogPosts);
    clearFilters.addEventListener('click', () => {
        blogFilter.value = '';
        loadBlogPosts();
    });

    // Initial load
    loadBlogCategories();
    loadBlogPosts();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
