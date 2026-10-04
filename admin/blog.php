<?php
/**
 * Life is Character Radio - Blog Management
 * 
 * Location: /admin/blog.php
 * 
 * Manage blog posts with categories modal for managing blog_categories
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_blog');

$pageTitle = 'Blog Management';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Posts</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-posts">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-blog text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Published</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="published-count">-</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Categories</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="categories-count">-</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-folder text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Toolbar -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <button onclick="openCategoriesModal()" class="px-6 py-2 border border-blue-500 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition">
            <i class="fas fa-folder-tree mr-2"></i>Manage Categories
        </button>
        <button onclick="openPostModal()" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>New Post
        </button>
    </div>
</div>

<!-- Posts List -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Blog Posts</h3>
    <div id="posts-container">
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
            <p class="text-gray-600 mt-4">Loading posts...</p>
        </div>
    </div>
</div>

<!-- Post Modal -->
<div id="post-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 id="post-modal-title" class="text-2xl font-bold text-gray-800">New Blog Post</h3>
                    <button onclick="closePostModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="post-form" class="p-6 space-y-4">
                <input type="hidden" id="post-id" name="id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Title *</label>
                    <input type="text" id="post-title" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Slug (auto-generated if empty)</label>
                    <input type="text" id="post-slug" name="slug" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Excerpt</label>
                    <textarea id="post-excerpt" name="excerpt" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Featured Image</label>
                    <input type="hidden" id="post-current-image" name="current_image" value="">
                    <input type="file" id="post-image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    <p class="text-sm text-gray-500 mt-1">Supported: JPEG, PNG, GIF, WebP (max 5MB)</p>
                    <div id="post-current-image-display" class="mt-3 hidden">
                        <p class="text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                        <img id="post-current-img" src="" alt="Current" class="max-w-xs h-auto rounded border border-gray-300 mb-2">
                        <p class="text-xs text-gray-500">Upload a new image to replace it</p>
                    </div>
                    <div id="post-image-preview" class="mt-3 hidden">
                        <p class="text-sm font-medium text-gray-700 mb-2">New Image Preview:</p>
                        <img id="post-preview-img" src="" alt="Preview" class="max-w-xs h-auto rounded border border-gray-300">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Content *</label>
                    <div id="post-content-editor" class="bg-white border border-gray-300 rounded-lg" style="min-height: 300px;"></div>
                    <textarea id="post-content" name="content" class="hidden"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Scripture Reference</label>
                        <input type="text" id="post-scripture" name="scripture_reference" placeholder="e.g., John 3:16" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Tags (comma-separated)</label>
                        <input type="text" id="post-tags" name="tags" placeholder="faith, hope, love" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-3">Categories</label>
                    <div id="categories-checkboxes" class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 border border-gray-300 rounded-lg max-h-48 overflow-y-auto">
                        <p class="text-gray-500 text-sm">Loading categories...</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="post-published" name="is_published" value="1" class="w-5 h-5 text-cyan-600 border-gray-300 rounded">
                    <label for="post-published" class="ml-2 text-gray-700 font-medium">Publish immediately</label>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closePostModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" id="post-save-btn" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Categories Modal -->
<div id="categories-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-800">Manage Categories</h3>
                <button onclick="closeCategoriesModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Add Category Form -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Add New Category</h4>
                <form id="category-form" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="category">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="text" id="cat-name" name="name" placeholder="Category Name *" required class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    <input type="text" id="cat-slug" name="slug" placeholder="slug-auto-generated" readonly class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                    <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i>Add
                    </button>
                </form>
            </div>
            
            <!-- Categories List -->
            <div id="categories-list">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-cyan-500"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';
let posts = [], categories = [], quillEditor = null;

document.addEventListener('DOMContentLoaded', () => {
    loadPosts();
    loadCategories();
    
    // Initialize Quill Rich Text Editor
    quillEditor = new Quill('#post-content-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Write your blog post content here...'
    });
    
    // Auto-generate slug from category name
    document.getElementById('cat-name')?.addEventListener('input', function(e) {
        const name = e.target.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        document.getElementById('cat-slug').value = slug;
    });    
    // Auto-generate slug from post title
    document.getElementById('post-title')?.addEventListener('input', function(e) {
        // Only auto-generate if creating new post (no ID)
        if (!document.getElementById('post-id').value) {
            const title = e.target.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('post-slug').value = slug;
        }
    });});

async function loadPosts() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/blog?action=posts`);
        const data = await res.json();
        
        if (data.success) {
            posts = data.data.posts;
            renderPosts(posts);
            document.getElementById('total-posts').textContent = data.data.total;
            document.getElementById('published-count').textContent = posts.filter(p => p.status === 'published').length;
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Load posts error:', err);
        showAlert('Failed to load posts', 'error');
    }
}

function renderPosts(postsData) {
    const container = document.getElementById('posts-container');
    
    if (postsData.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-blog text-4xl mb-4"></i><p>No posts yet</p></div>';
        return;
    }
    
    container.innerHTML = `
        <div class="space-y-4">
            ${postsData.map(post => `
                <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h4 class="text-lg font-semibold text-gray-800">${escapeHtml(post.title)}</h4>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${post.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                                    ${post.status === 'published' ? 'Published' : 'Draft'}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-3">${escapeHtml(post.excerpt || post.content.substring(0, 150) + '...')}</p>
                            <div class="flex flex-wrap gap-2 mb-2">
                                ${post.categories.map(cat => `<span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">${escapeHtml(cat.name)}</span>`).join('')}
                            </div>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>${formatDate(post.created_at)}
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <button onclick="editPost(${post.id})" class="text-blue-600 hover:text-blue-800 px-3 py-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deletePost(${post.id})" class="text-red-600 hover:text-red-800 px-3 py-1" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

async function loadCategories() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/blog?action=categories`);
        const data = await res.json();
        
        if (data.success) {
            categories = data.data.categories;
            renderCategoriesCheckboxes();
            document.getElementById('categories-count').textContent = categories.length;
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Load categories error:', err);
    }
}

function renderCategoriesCheckboxes() {
    const container = document.getElementById('categories-checkboxes');
    if (categories.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm col-span-full">No categories. Click "Manage Categories" to add.</p>';
        return;
    }
    
    container.innerHTML = categories.filter(c => c.is_active == 1).map(cat => `
        <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
            <input type="checkbox" name="categories[]" value="${cat.id}" class="category-checkbox w-4 h-4 text-cyan-600 border-gray-300 rounded">
            <span class="text-sm text-gray-700">${escapeHtml(cat.name)}</span>
        </label>
    `).join('');
}

function renderCategoriesList() {
    const container = document.getElementById('categories-list');
    
    if (categories.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">No categories yet</p>';
        return;
    }
    
    container.innerHTML = `
        <div class="space-y-2">
            ${categories.map(cat => `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-sm transition">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h5 class="font-semibold text-gray-800">${escapeHtml(cat.name)}</h5>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${cat.is_active == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                                ${cat.is_active == 1 ? 'Active' : 'Inactive'}
                            </span>
                            <span class="px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded-full">${cat.post_count} posts</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">${escapeHtml(cat.slug)}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="toggleCategoryStatus(${cat.id}, ${cat.is_active})" class="px-3 py-1 text-sm ${cat.is_active == 1 ? 'text-gray-600 hover:text-gray-800' : 'text-green-600 hover:text-green-800'}" title="${cat.is_active == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${cat.is_active == 1 ? 'ban' : 'check'}"></i>
                        </button>
                        <button onclick="deleteCategory(${cat.id})" class="px-3 py-1 text-sm text-red-600 hover:text-red-800" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function openPostModal(post = null) {
    document.getElementById('post-modal-title').textContent = post ? 'Edit Blog Post' : 'New Blog Post';
    document.getElementById('post-form').reset();
    document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
    
    // Explicitly clear the ID field when creating new post
    document.getElementById('post-id').value = '';
    
    // Clear image displays
    document.getElementById('post-image-preview').classList.add('hidden');
    document.getElementById('post-current-image-display').classList.add('hidden');
    document.getElementById('post-current-image').value = '';
    
    // Clear Quill content
    if (quillEditor) {
        quillEditor.setContents([]);
    }
    
    if (post) {
        document.getElementById('post-id').value = post.id;
        document.getElementById('post-title').value = post.title;
        document.getElementById('post-slug').value = post.slug;
        document.getElementById('post-excerpt').value = post.excerpt || '';
        
        // Set Quill content
        if (quillEditor) {
            quillEditor.root.innerHTML = post.content || '';
        }
        
        document.getElementById('post-scripture').value = post.scripture_reference || '';
        document.getElementById('post-tags').value = Array.isArray(post.tags) ? post.tags.join(', ') : '';
        document.getElementById('post-published').checked = post.status === 'published';
        
        post.categories.forEach(cat => {
            const checkbox = document.querySelector(`input[name="categories[]"][value="${cat.id}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        // Display existing image if present
        if (post.featured_image) {
            document.getElementById('post-current-image').value = post.featured_image;
            const imgSrc = post.featured_image.startsWith('http') ? post.featured_image : BASE_URL + 'uploads/' + post.featured_image;
            document.getElementById('post-current-img').src = imgSrc;
            document.getElementById('post-current-img').onerror = function() { 
                console.error('Failed to load image:', this.src); 
                this.parentElement.innerHTML = '<p class="text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>Image not found</p>'; 
            };
            document.getElementById('post-current-image-display').classList.remove('hidden');
        }
    }
    
    document.getElementById('post-modal').classList.remove('hidden');
}

function closePostModal() {
    document.getElementById('post-modal').classList.add('hidden');
}

function editPost(postId) {
    const post = posts.find(p => p.id == postId);
    if (post) {
        openPostModal(post);
    } else {
        showAlert('Post not found', 'error');
    }
}

// Image preview
document.getElementById('post-image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('post-image-preview');
    const img = document.getElementById('post-preview-img');
    
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});

document.getElementById('post-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Sync Quill content to textarea before submission
    if (quillEditor) {
        document.getElementById('post-content').value = quillEditor.root.innerHTML;
    }
    
    // Validate content is not empty
    const content = quillEditor.getText().trim();
    if (content.length === 0) {
        showAlert('Blog content is required', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    // POST without _method = UPDATE (has id), POST with _method=PUT = CREATE (no id)
    if (!id) formData.append('_method', 'PUT');
    
    const saveBtn = document.getElementById('post-save-btn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/blog`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            closePostModal();
            loadPosts();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Save post error:', err);
        showAlert('Failed to save post', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Post';
    }
});

async function deletePost(id) {
    if (!confirm('Delete this blog post? This cannot be undone.')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('_method', 'DELETE');
        
        const res = await fetch(`${BASE_URL}api/admin/blog`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadPosts();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Delete post error:', err);
        showAlert('Failed to delete post', 'error');
    }
}

function openCategoriesModal() {
    renderCategoriesList();
    document.getElementById('categories-modal').classList.remove('hidden');
}

function closeCategoriesModal() {
    document.getElementById('categories-modal').classList.add('hidden');
    loadCategories(); // Refresh checkboxes in post form
}

document.getElementById('category-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/blog`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            e.target.reset();
            document.getElementById('cat-slug').value = '';
            await loadCategories();
            renderCategoriesList();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Create category error:', err);
        showAlert('Failed to create category', 'error');
    }
});

async function toggleCategoryStatus(id, currentStatus) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('action', 'category');
    
    const cat = categories.find(c => c.id === id);
    formData.append('name', cat.name);
    formData.append('slug', cat.slug);
    formData.append('description', cat.description || '');
    formData.append('sort_order', cat.sort_order);
    formData.append('is_active', currentStatus == 1 ? 0 : 1);
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/blog`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(currentStatus == 1 ? 'Category deactivated' : 'Category activated', 'success');
            await loadCategories();
            renderCategoriesList();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Toggle category error:', err);
        showAlert('Failed to update category', 'error');
    }
}

async function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('action', 'category');
        formData.append('_method', 'DELETE');
        
        const res = await fetch(`${BASE_URL}api/admin/blog`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            await loadCategories();
            renderCategoriesList();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Delete category error:', err);
        showAlert('Failed to delete category', 'error');
    }
}

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `${type === 'success' ? 'bg-green-500/10 border-green-500 text-green-400' : 'bg-red-500/10 border-red-500 text-red-400'} border px-6 py-4 rounded-lg`;
    alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${escapeHtml(message)}`;
    container.innerHTML = '';
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
