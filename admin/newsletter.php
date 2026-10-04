<?php
/**
 * Life is Character Radio - Newsletter Subscribers
 * 
 * Location: /admin/newsletter.php
 * 
 * View and manage newsletter subscribers
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$pageTitle = 'Newsletter Subscribers';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Subscribers</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-subscribers">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Active Subscriptions</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="active-count">-</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Subscribers List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Newsletter Subscribers</h2>
            <button onclick="exportSubscribers()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-download mr-2"></i> Export CSV
            </button>
        </div>
        
        <div class="flex gap-4">
            <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Unsubscribed</option>
            </select>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="subscribers-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading subscribers...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>api/admin';
const CSRF_TOKEN = '<?= $csrf_token ?>';

async function loadSubscribers() {
    const status = document.getElementById('filter-status').value;
    
    try {
        const params = new URLSearchParams({ status, limit: 100 });
        const response = await fetch(`${API_BASE}/newsletter?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderSubscribers(data.data.subscribers);
            updateStats(data.data.subscribers);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load subscribers', 'error');
    }
}

function renderSubscribers(subscribers) {
    const tbody = document.getElementById('subscribers-tbody');
    
    if (subscribers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No subscribers found</td></tr>';
        return;
    }
    
    tbody.innerHTML = subscribers.map(sub => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(sub.name || 'N/A')}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(sub.email)}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${new Date(sub.subscribed_at).toLocaleDateString()}</td>
            <td class="px-6 py-4">
                ${sub.is_active ? 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' : 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unsubscribed</span>'
                }
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex gap-2">
                    <button onclick="toggleSubscription(${sub.id}, ${sub.is_active})" 
                            class="text-${sub.is_active ? 'amber' : 'green'}-600 hover:text-${sub.is_active ? 'amber' : 'green'}-900"
                            title="${sub.is_active ? 'Unsubscribe' : 'Resubscribe'}">
                        <i class="fas fa-${sub.is_active ? 'toggle-on' : 'toggle-off'}"></i>
                    </button>
                    <button onclick="deleteSubscriber(${sub.id})" class="text-red-600 hover:text-red-900" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function toggleSubscription(id, currentStatus) {
    const action = currentStatus ? 'unsubscribe' : 'resubscribe';
    if (!confirm(`Are you sure you want to ${action} this subscriber?`)) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(`${API_BASE}/newsletter`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadSubscribers();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to update subscription', 'error');
    }
}

async function deleteSubscriber(id) {
    if (!confirm('Are you sure you want to permanently delete this subscriber?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/newsletter?id=${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadSubscribers();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to delete subscriber', 'error');
    }
}

function exportSubscribers() {
    window.location.href = `${API_BASE}/newsletter?action=export`;
}

function updateStats(subscribers) {
    document.getElementById('total-subscribers').textContent = subscribers.length;
    document.getElementById('active-count').textContent = subscribers.filter(s => s.is_active).length;
}

function showAlert(message, type = 'info') {
    const colors = {
        success: 'green',
        error: 'red',
        info: 'blue'
    };
    const color = colors[type] || 'blue';
    
    const alert = document.createElement('div');
    alert.className = `bg-${color}-100 border border-${color}-400 text-${color}-700 px-4 py-3 rounded relative`;
    alert.innerHTML = `
        <span class="block sm:inline">${message}</span>
        <button onclick="this.parentElement.remove()" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const container = document.getElementById('alert-container');
    container.innerHTML = '';
    container.appendChild(alert);
    
    setTimeout(() => alert.remove(), 5000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('filter-status').addEventListener('change', loadSubscribers);

loadSubscribers();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
