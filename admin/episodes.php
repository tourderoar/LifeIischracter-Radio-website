<?php
/**
 * Life is Character Radio - Episodes Management
 * 
 * Location: /admin/episodes.php
 * 
 * Manage episodes with topics modal for managing episode_topics
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_episodes');

$pageTitle = 'Episodes Management';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Episodes</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-episodes">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-video text-cyan-600 text-xl"></i>
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
                <p class="text-gray-500 text-sm font-medium">Topics</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="topics-count">-</p>
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
        <button onclick="openTopicsModal()" class="px-6 py-2 border border-blue-500 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition">
            <i class="fas fa-folder-tree mr-2"></i>Manage Topics
        </button>
        <button onclick="openEpisodeModal()" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>New Episode
        </button>
    </div>
</div>

<!-- Episodes List -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Episodes</h3>
    <div id="episodes-container">
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
            <p class="text-gray-600 mt-4">Loading episodes...</p>
        </div>
    </div>
</div>

<!-- Episode Modal -->
<div id="episode-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 id="episode-modal-title" class="text-2xl font-bold text-gray-800">New Episode</h3>
                    <button onclick="closeEpisodeModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="episode-form" class="p-6 space-y-4">
                <input type="hidden" id="episode-id" name="id">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Title *</label>
                    <input type="text" id="episode-title" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea id="episode-description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">YouTube URL *</label>
                    <input type="url" id="episode-youtube" name="youtube_url" required placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Episode Number</label>
                        <input type="number" id="episode-number" name="episode_number" min="1" placeholder="Optional" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Duration (seconds)</label>
                        <input type="number" id="episode-duration" name="duration" min="0" placeholder="Optional" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Topic</label>
                        <select id="episode-topic" name="topic_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                            <option value="">No Topic</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Scripture Reference</label>
                        <input type="text" id="episode-scripture" name="scripture_reference" placeholder="e.g., John 3:16" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Thumbnail URL (optional)</label>
                    <input type="url" id="episode-thumbnail" name="thumbnail_url" placeholder="https://..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate from YouTube video</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="episode-published" name="is_published" value="1" class="w-5 h-5 text-cyan-600 border-gray-300 rounded">
                    <label for="episode-published" class="ml-2 text-gray-700 font-medium">Publish immediately</label>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEpisodeModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" id="episode-save-btn" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-lg font-semibold hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Episode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Topics Modal -->
<div id="topics-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-800">Manage Topics</h3>
                <button onclick="closeTopicsModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Add Topic Form -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-gray-800 mb-3">Add New Topic</h4>
                <form id="topic-form" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="topic">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="text" id="topic-name" name="name" placeholder="Topic Name *" required class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500">
                    <input type="text" id="topic-slug" name="slug" placeholder="slug-auto-generated" readonly class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                    <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i>Add
                    </button>
                </form>
            </div>
            
            <!-- Topics List -->
            <div id="topics-list">
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
let episodes = [], topics = [];

document.addEventListener('DOMContentLoaded', () => {
    loadEpisodes();
    loadTopics();
    
    // Auto-generate slug from topic name
    document.getElementById('topic-name')?.addEventListener('input', function(e) {
        const name = e.target.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        document.getElementById('topic-slug').value = slug;
    });
});

async function loadEpisodes() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/episodes?action=episodes`);
        const data = await res.json();
        
        if (data.success) {
            episodes = data.data.episodes;
            renderEpisodes(episodes);
            document.getElementById('total-episodes').textContent = data.data.total;
            document.getElementById('published-count').textContent = episodes.filter(e => e.status === 'published').length;
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Load episodes error:', err);
        showAlert('Failed to load episodes', 'error');
    }
}

function renderEpisodes(episodesData) {
    const container = document.getElementById('episodes-container');
    
    if (episodesData.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-video text-4xl mb-4"></i><p>No episodes yet</p></div>';
        return;
    }
    
    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            ${episodesData.map(ep => `
                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition">
                    <div class="aspect-video bg-gray-900 flex items-center justify-center">
                        ${ep.thumbnail_url ? `<img src="${escapeHtml(ep.thumbnail_url)}" alt="Thumbnail" class="w-full h-full object-cover">` : '<i class="fas fa-play-circle text-white text-6xl opacity-50"></i>'}
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            ${ep.episode_number ? `<span class="px-2 py-1 bg-cyan-100 text-cyan-700 text-xs font-semibold rounded">Ep ${ep.episode_number}</span>` : ''}
                            <span class="px-2 py-1 text-xs font-semibold rounded ${ep.status === 'published' ? 'bg-green-100 text-green-700' : (ep.status === 'upcoming' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')}">
                                ${ep.status === 'published' ? 'Published' : (ep.status === 'upcoming' ? 'Upcoming' : 'Draft')}
                            </span>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">${escapeHtml(ep.title)}</h4>
                        ${ep.topic_name ? `<p class="text-sm text-blue-600 mb-2"><i class="fas fa-folder mr-1"></i>${escapeHtml(ep.topic_name)}</p>` : ''}
                        <p class="text-sm text-gray-600 mb-3">${escapeHtml(ep.description?.substring(0, 80) || '')}${ep.description?.length > 80 ? '...' : ''}</p>
                        <div class="flex gap-2">
                            <button onclick="editEpisode(${ep.id})" class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteEpisode(${ep.id})" class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

async function loadTopics() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/episodes?action=topics`);
        const data = await res.json();
        
        if (data.success) {
            topics = data.data.topics;
            renderTopicsDropdown();
            document.getElementById('topics-count').textContent = topics.length;
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Load topics error:', err);
    }
}

function renderTopicsDropdown() {
    const select = document.getElementById('episode-topic');
    select.innerHTML = '<option value="">No Topic</option>' + 
        topics.filter(t => t.is_active == 1).map(t => `<option value="${t.id}">${escapeHtml(t.name)}</option>`).join('');
}

function renderTopicsList() {
    const container = document.getElementById('topics-list');
    
    if (topics.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">No topics yet</p>';
        return;
    }
    
    container.innerHTML = `
        <div class="space-y-2">
            ${topics.map(topic => `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-sm transition">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h5 class="font-semibold text-gray-800">${escapeHtml(topic.name)}</h5>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${topic.is_active == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                                ${topic.is_active == 1 ? 'Active' : 'Inactive'}
                            </span>
                            <span class="px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded-full">${topic.episode_count} episodes</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">${escapeHtml(topic.slug)}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="toggleTopicStatus(${topic.id}, ${topic.is_active})" class="px-3 py-1 text-sm ${topic.is_active == 1 ? 'text-gray-600 hover:text-gray-800' : 'text-green-600 hover:text-green-800'}" title="${topic.is_active == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${topic.is_active == 1 ? 'ban' : 'check'}"></i>
                        </button>
                        <button onclick="deleteTopic(${topic.id})" class="px-3 py-1 text-sm text-red-600 hover:text-red-800" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function openEpisodeModal(episode = null) {
    document.getElementById('episode-modal-title').textContent = episode ? 'Edit Episode' : 'New Episode';
    document.getElementById('episode-form').reset();
    
    // Explicitly clear the ID field when creating new episode
    document.getElementById('episode-id').value = '';
    
    if (episode) {
        document.getElementById('episode-id').value = episode.id;
        document.getElementById('episode-title').value = episode.title;
        document.getElementById('episode-description').value = episode.description || '';
        document.getElementById('episode-youtube').value = episode.youtube_url;
        document.getElementById('episode-thumbnail').value = episode.thumbnail_url || '';
        document.getElementById('episode-number').value = episode.episode_number || '';
        document.getElementById('episode-duration').value = episode.duration || '';
        document.getElementById('episode-topic').value = episode.topic_id || '';
        document.getElementById('episode-scripture').value = episode.scripture_reference || '';
        document.getElementById('episode-published').checked = episode.status === 'published';
    }
    
    document.getElementById('episode-modal').classList.remove('hidden');
}

function closeEpisodeModal() {
    document.getElementById('episode-modal').classList.add('hidden');
}

function editEpisode(episodeId) {
    const episode = episodes.find(ep => ep.id == episodeId);
    if (episode) {
        openEpisodeModal(episode);
    } else {
        showAlert('Episode not found', 'error');
    }
}

document.getElementById('episode-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    // POST without _method = UPDATE (has id), POST with _method=PUT = CREATE (no id)
    if (!id) formData.append('_method', 'PUT');
    
    const saveBtn = document.getElementById('episode-save-btn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/episodes`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            closeEpisodeModal();
            loadEpisodes();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Save episode error:', err);
        showAlert('Failed to save episode', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Episode';
    }
});

async function deleteEpisode(id) {
    if (!confirm('Delete this episode? This cannot be undone.')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('_method', 'DELETE');
        
        const res = await fetch(`${BASE_URL}api/admin/episodes`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadEpisodes();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Delete episode error:', err);
        showAlert('Failed to delete episode', 'error');
    }
}

function openTopicsModal() {
    renderTopicsList();
    document.getElementById('topics-modal').classList.remove('hidden');
}

function closeTopicsModal() {
    document.getElementById('topics-modal').classList.add('hidden');
    loadTopics(); // Refresh dropdown in episode form
}

document.getElementById('topic-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/episodes`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            e.target.reset();
            document.getElementById('topic-slug').value = '';
            await loadTopics();
            renderTopicsList();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Create topic error:', err);
        showAlert('Failed to create topic', 'error');
    }
});

async function toggleTopicStatus(id, currentStatus) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('action', 'topic');
    
    const topic = topics.find(t => t.id === id);
    formData.append('name', topic.name);
    formData.append('slug', topic.slug);
    formData.append('description', topic.description || '');
    formData.append('sort_order', topic.sort_order);
    formData.append('is_active', currentStatus == 1 ? 0 : 1);
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/episodes`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(currentStatus == 1 ? 'Topic deactivated' : 'Topic activated', 'success');
            await loadTopics();
            renderTopicsList();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Toggle topic error:', err);
        showAlert('Failed to update topic', 'error');
    }
}

async function deleteTopic(id) {
    if (!confirm('Delete this topic?')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('action', 'topic');
        formData.append('_method', 'DELETE');
        
        const res = await fetch(`${BASE_URL}api/admin/episodes`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            await loadTopics();
            renderTopicsList();
        } else showAlert(data.message, 'error');
    } catch (err) {
        console.error('Delete topic error:', err);
        showAlert('Failed to delete topic', 'error');
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
