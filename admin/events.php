<?php
/**
 * Life is Character Radio - Events Management
 * 
 * Location: /admin/events.php
 * 
 * Full CRUD for events with API-first architecture
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_events');

$pageTitle = 'Events Management';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Upcoming Events</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="upcoming-count">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Past Events</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="past-count">-</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-history text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Registrations</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-reg">-</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchTab('events')" id="tab-events" class="tab-btn tab-active px-6 py-4 border-b-2 font-medium text-sm transition">
                Events
            </button>
            <button onclick="switchTab('registrations')" id="tab-registrations" class="tab-btn px-6 py-4 border-b-2 font-medium text-sm transition">
                Registrations
            </button>
        </nav>
    </div>
</div>

<!-- Events Section -->
<div id="section-events">
<!-- Toolbar -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex gap-2">
            <button onclick="filterEvents('all')" id="filter-all" class="filter-btn filter-active px-4 py-2 rounded-lg font-medium transition">
                All
            </button>
            <button onclick="filterEvents('upcoming')" id="filter-upcoming" class="filter-btn px-4 py-2 rounded-lg font-medium transition">
                Upcoming
            </button>
            <button onclick="filterEvents('past')" id="filter-past" class="filter-btn px-4 py-2 rounded-lg font-medium transition">
                Past
            </button>
            <button onclick="filterEvents('cancelled')" id="filter-cancelled" class="filter-btn px-4 py-2 rounded-lg font-medium transition">
                Cancelled
            </button>
        </div>
        <button onclick="openEventModal()" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>New Event
        </button>
    </div>
</div>

<!-- Events Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Events List</h3>
        <div id="events-container" class="overflow-x-auto">
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
                <p class="text-gray-600 mt-4">Loading events...</p>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Registrations Section -->
<div id="section-registrations" class="hidden bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">Event Registrations</h3>
    <div id="registrations-container">
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
            <p class="text-gray-600 mt-4">Loading registrations...</p>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="event-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="modal-title" class="text-2xl font-bold text-gray-800">New Event</h3>
                <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <form id="event-form" class="p-6 space-y-4">
            <input type="hidden" id="event-id" name="id">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Title *</label>
                <input type="text" id="event-title" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Description *</label>
                <textarea id="event-description" name="description" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"></textarea>
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Featured Image</label>
                <input type="hidden" id="current-image" name="current_image" value="">
                <input type="file" id="event-image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                <p class="text-sm text-gray-500 mt-1">Supported: JPEG, PNG, GIF, WebP (max 5MB)</p>
                <div id="current-image-display" class="mt-3 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                    <img id="current-img" src="" alt="Current" class="max-w-xs h-auto rounded border border-gray-300 mb-2">
                    <p class="text-xs text-gray-500">Upload a new image to replace it</p>
                </div>
                <div id="image-preview" class="mt-3 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">New Image Preview:</p>
                    <img id="preview-img" src="" alt="Preview" class="max-w-xs h-auto rounded border border-gray-300">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Event Date & Time *</label>
                    <input type="datetime-local" id="event-date" name="event_date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Cost (USD)</label>
                    <input type="number" id="event-cost" name="cost" min="0" step="0.01" value="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Location *</label>
                <input type="text" id="event-location" name="location" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Max Capacity</label>
                    <input type="number" id="event-capacity" name="max_capacity" min="1" placeholder="Leave empty for unlimited" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Status</label>
                    <select id="event-status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        <option value="upcoming">Upcoming</option>
                        <option value="past">Past</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEventModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" id="save-btn" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Save Event
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.filter-btn { background-color: #f3f4f6; color: #4b5563; }
.filter-btn:hover { background-color: #e5e7eb; }
.filter-active { background: linear-gradient(to right, #06b6d4, #2563eb); color: white; }
.tab-btn { border-bottom-color: transparent; color: #6b7280; }
.tab-btn:hover { color: #111827; border-bottom-color: #d1d5db; }
.tab-active { color: #06b6d4; border-bottom-color: #06b6d4 !important; }
.status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.status-upcoming { background-color: #dbeafe; color: #1e40af; }
.status-past { background-color: #e5e7eb; color: #374151; }
.status-cancelled { background-color: #fee2e2; color: #991b1b; }
.status-pending { background-color: #fef3c7; color: #92400e; }
.status-completed { background-color: #d1fae5; color: #065f46; }
.status-failed { background-color: #fee2e2; color: #991b1b; }
</style>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';
let currentFilter = 'all';
let events = [];
let registrations = [];
let currentTab = 'events';

// Load events on page load
document.addEventListener('DOMContentLoaded', () => {
    loadEvents();
    loadRegistrations();
});

// Tab switching
function switchTab(tab) {
    currentTab = tab;
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('tab-active'));
    document.getElementById(`tab-${tab}`).classList.add('tab-active');
    
    // Show/hide sections
    document.getElementById('section-events').classList.toggle('hidden', tab !== 'events');
    document.getElementById('section-registrations').classList.toggle('hidden', tab !== 'registrations');
}

async function loadEvents() {
    try {
        const url = `${BASE_URL}api/admin/events${currentFilter !== 'all' ? `?status=${currentFilter}` : ''}`;
        const res = await fetch(url);
        const data = await res.json();
        
        if (data.success) {
            events = data.data.events;
            renderEvents(events);
            updateStats();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Load events error:', err);
        showAlert('Failed to load events', 'error');
    }
}

function renderEvents(eventsData) {
    const container = document.getElementById('events-container');
    
    if (eventsData.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-calendar-xmark text-4xl mb-4"></i><p>No events found</p></div>';
        return;
    }
    
    container.innerHTML = `
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                ${eventsData.map(event => `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4"><div class="font-medium text-gray-900">${escapeHtml(event.title)}</div><div class="text-sm text-gray-500">${escapeHtml(event.created_by_name)}</div></td>
                        <td class="px-6 py-4 text-sm text-gray-600">${formatDateTime(event.event_date)}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(event.location)}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${event.cost > 0 ? '$' + Number(event.cost).toFixed(2) : 'Free'}</td>
                        <td class="px-6 py-4"><span class="status-badge status-${event.status}">${event.status}</span></td>
                        <td class="px-6 py-4 text-sm text-gray-600">${event.current_registrations}${event.max_capacity ? `/${event.max_capacity}` : ''}</td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="editEvent(${event.id})" class="text-blue-600 hover:text-blue-800 mr-3" title="Edit"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteEvent(${event.id})" class="text-red-600 hover:text-red-800" title="Cancel"><i class="fas fa-ban"></i></button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function updateStats() {
    document.getElementById('upcoming-count').textContent = events.filter(e => e.status === 'upcoming').length;
    document.getElementById('past-count').textContent = events.filter(e => e.status === 'past').length;
    document.getElementById('total-reg').textContent = events.reduce((sum, e) => sum + parseInt(e.current_registrations), 0);
}

function filterEvents(status) {
    currentFilter = status;
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('filter-active'));
    document.getElementById(`filter-${status}`).classList.add('filter-active');
    loadEvents();
}

function openEventModal(event = null) {
    document.getElementById('modal-title').textContent = event ? 'Edit Event' : 'New Event';
    document.getElementById('event-form').reset();
    
    // Explicitly clear the ID field when creating new event
    document.getElementById('event-id').value = '';
    
    // Clear image displays
    document.getElementById('image-preview').classList.add('hidden');
    document.getElementById('current-image-display').classList.add('hidden');
    document.getElementById('current-image').value = '';
    
    if (event) {
        document.getElementById('event-id').value = event.id;
        document.getElementById('event-title').value = event.title;
        document.getElementById('event-description').value = event.description;
        document.getElementById('event-date').value = event.event_date.replace(' ', 'T').substring(0, 16);
        document.getElementById('event-location').value = event.location;
        document.getElementById('event-cost').value = event.cost;
        document.getElementById('event-capacity').value = event.max_capacity || '';
        document.getElementById('event-status').value = event.status;
        
        // Display existing image if present
        if (event.featured_image) {
            document.getElementById('current-image').value = event.featured_image;
            const imgSrc = event.featured_image.startsWith('http') ? event.featured_image : BASE_URL + 'uploads/' + event.featured_image;
            document.getElementById('current-img').src = imgSrc;
            document.getElementById('current-img').onerror = function() { 
                console.error('Failed to load image:', this.src); 
                this.parentElement.innerHTML = '<p class="text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>Image not found</p>'; 
            };
            document.getElementById('current-image-display').classList.remove('hidden');
        }
    }
    
    document.getElementById('event-modal').classList.remove('hidden');
}

function closeEventModal() {
    document.getElementById('event-modal').classList.add('hidden');
}

function editEvent(eventId) {
    const event = events.find(e => e.id == eventId);
    if (event) {
        openEventModal(event);
    } else {
        showAlert('Event not found', 'error');
    }
}

// Image preview
document.getElementById('event-image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    const img = document.getElementById('preview-img');
    
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

document.getElementById('event-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const isEdit = !!id;
    
    if (isEdit) formData.append('_method', 'PUT');
    
    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/events`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            closeEventModal();
            loadEvents();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Save event error:', err);
        showAlert('Failed to save event', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Event';
    }
});

async function deleteEvent(id) {
    if (!confirm('Cancel this event? This action cannot be undone.')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('_method', 'DELETE');
        
        const res = await fetch(`${BASE_URL}api/admin/events`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadEvents();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Delete event error:', err);
        showAlert('Failed to cancel event', 'error');
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
    alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function formatDateTime(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load registrations
async function loadRegistrations() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/events?action=registrations`);
        const data = await res.json();
        
        if (data.success) {
            registrations = data.data.registrations;
            renderRegistrations(registrations);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Load registrations error:', err);
        document.getElementById('registrations-container').innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-exclamation-triangle text-4xl mb-4"></i><p>Failed to load registrations</p></div>';
    }
}

function renderRegistrations(regsData) {
    const container = document.getElementById('registrations-container');
    
    if (regsData.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-inbox text-4xl mb-4"></i><p>No registrations found</p></div>';
        return;
    }
    
    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${regsData.map(reg => `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">${escapeHtml(reg.event_title)}</div>
                                <div class="text-sm text-gray-500">${formatDateTime(reg.event_date)}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900">${escapeHtml(reg.user_name)}</div>
                                <div class="text-sm text-gray-500">${escapeHtml(reg.user_email)}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-900 font-medium">$${Number(reg.amount_paid).toFixed(2)}</td>
                            <td class="px-6 py-4"><span class="status-badge status-${reg.payment_status}">${reg.payment_status}</span></td>
                            <td class="px-6 py-4 text-sm text-gray-600">${formatDateTime(reg.registered_at)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}
</script>

<style>
.status-badge { padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
.status-upcoming { background-color: #cffafe; color: #0e7490; }
.status-past { background-color: #f3f4f6; color: #374151; }
.status-cancelled { background-color: #fee2e2; color: #991b1b; }
</style>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
