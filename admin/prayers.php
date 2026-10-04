<?php
/**
 * Life is Character Radio - Prayer Requests
 * 
 * Location: /admin/prayers.php
 * 
 * View and manage prayer requests
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$pageTitle = 'Prayer Requests';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Requests</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-requests">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-praying-hands text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Praying</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="praying-count">-</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-hands text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Answered</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="answered-count">-</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Requests List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Prayer Requests</h2>
        </div>
        
        <div class="flex gap-4">
            <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="all">All Status</option>
                <option value="new">New</option>
                <option value="praying">Praying</option>
                <option value="answered">Answered</option>
            </select>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="requests-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading prayer requests...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- View Modal -->
<div id="view-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h3 class="text-2xl font-bold text-gray-800">Prayer Request Details</h3>
            <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="request-details" class="p-6"></div>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>api/admin';
const CSRF_TOKEN = '<?= $csrf_token ?>';

async function loadRequests() {
    const status = document.getElementById('filter-status').value;
    
    try {
        const params = new URLSearchParams({ status, limit: 100 });
        const response = await fetch(`${API_BASE}/prayers?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderRequests(data.data.requests);
            updateStats(data.data.requests);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load prayer requests', 'error');
    }
}

function renderRequests(requests) {
    const tbody = document.getElementById('requests-tbody');
    
    if (requests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No prayer requests found</td></tr>';
        return;
    }
    
    tbody.innerHTML = requests.map(req => `
        <tr class="hover:bg-gray-50 ${req.status === 'new' ? 'bg-purple-50' : ''}">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                ${req.is_anonymous ? '<span class="text-gray-400 italic">Anonymous</span>' : escapeHtml(req.name)}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
                ${escapeHtml(req.request.substring(0, 100))}${req.request.length > 100 ? '...' : ''}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">${new Date(req.submitted_at).toLocaleString()}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(req.status)}">
                    ${req.status}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex gap-2">
                    <button onclick='viewRequest(${JSON.stringify(req)})' class="text-cyan-600 hover:text-cyan-900" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="updateStatus(${req.id}, 'praying')" class="text-purple-600 hover:text-purple-900" title="Mark Praying">
                        <i class="fas fa-hands"></i>
                    </button>
                    <button onclick="updateStatus(${req.id}, 'answered')" class="text-green-600 hover:text-green-900" title="Mark Answered">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="deleteRequest(${req.id})" class="text-red-600 hover:text-red-900" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function viewRequest(req) {
    const details = document.getElementById('request-details');
    details.innerHTML = `
        <div class="space-y-4">
            ${!req.is_anonymous ? `
                <div>
                    <label class="text-sm font-semibold text-gray-700">Name</label>
                    <p class="mt-1">${escapeHtml(req.name)}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Email</label>
                    <p class="mt-1">${escapeHtml(req.email || 'Not provided')}</p>
                </div>
            ` : `
                <div class="bg-gray-100 p-4 rounded-lg">
                    <p class="text-sm text-gray-600"><i class="fas fa-user-secret mr-2"></i>This request is anonymous</p>
                </div>
            `}
            <div>
                <label class="text-sm font-semibold text-gray-700">Prayer Request</label>
                <p class="mt-1 whitespace-pre-wrap bg-gray-50 p-4 rounded-lg">${escapeHtml(req.request)}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Submitted</label>
                <p class="mt-1">${new Date(req.submitted_at).toLocaleString()}</p>
            </div>
            <div class="flex gap-3 pt-4">
                <button onclick="updateStatus(${req.id}, 'praying')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Mark as Praying
                </button>
                <button onclick="updateStatus(${req.id}, 'answered')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Mark as Answered
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('view-modal').classList.remove('hidden');
    document.getElementById('view-modal').classList.add('flex');
}

function closeViewModal() {
    document.getElementById('view-modal').classList.add('hidden');
    document.getElementById('view-modal').classList.remove('flex');
    loadRequests();
}

async function updateStatus(id, status) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(`${API_BASE}/prayers`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Status updated successfully', 'success');
            closeViewModal();
            loadRequests();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to update status', 'error');
    }
}

async function deleteRequest(id) {
    if (!confirm('Are you sure you want to delete this prayer request?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/prayers?id=${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadRequests();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to delete prayer request', 'error');
    }
}

function updateStats(requests) {
    document.getElementById('total-requests').textContent = requests.length;
    document.getElementById('praying-count').textContent = requests.filter(r => r.status === 'praying').length;
    document.getElementById('answered-count').textContent = requests.filter(r => r.status === 'answered').length;
}

function getStatusColor(status) {
    const colors = {
        'new': 'bg-amber-100 text-amber-800',
        'praying': 'bg-purple-100 text-purple-800',
        'answered': 'bg-green-100 text-green-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
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

document.getElementById('filter-status').addEventListener('change', loadRequests);

loadRequests();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
