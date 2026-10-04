<?php
/**
 * Life is Character Radio - Contact Submissions
 * 
 * Location: /admin/contact.php
 * 
 * View and manage contact form submissions
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$pageTitle = 'Contact Submissions';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Submissions</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-submissions">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-envelope text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">New</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="new-count">-</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Responded</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="responded-count">-</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Submissions List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Contact Submissions</h2>
        </div>
        
        <div class="flex gap-4">
            <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="all">All Status</option>
                <option value="new">New</option>
                <option value="read">Read</option>
                <option value="responded">Responded</option>
            </select>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="submissions-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading submissions...</p>
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
            <h3 class="text-2xl font-bold text-gray-800">Submission Details</h3>
            <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="submission-details" class="p-6"></div>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>api/admin';
const CSRF_TOKEN = '<?= $csrf_token ?>';

async function loadSubmissions() {
    const status = document.getElementById('filter-status').value;
    
    try {
        const params = new URLSearchParams({ status, limit: 100 });
        const response = await fetch(`${API_BASE}/contact?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderSubmissions(data.data.submissions);
            updateStats(data.data.submissions);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load submissions', 'error');
    }
}

function renderSubmissions(submissions) {
    const tbody = document.getElementById('submissions-tbody');
    
    if (submissions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No submissions found</td></tr>';
        return;
    }
    
    tbody.innerHTML = submissions.map(sub => `
        <tr class="hover:bg-gray-50 ${sub.status === 'new' ? 'bg-blue-50' : ''}">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(sub.name)}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(sub.email)}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(sub.subject)}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${new Date(sub.submitted_at).toLocaleString()}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(sub.status)}">
                    ${sub.status}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex gap-2">
                    <button onclick='viewSubmission(${JSON.stringify(sub)})' class="text-cyan-600 hover:text-cyan-900" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="updateStatus(${sub.id}, 'responded')" class="text-green-600 hover:text-green-900" title="Mark Responded">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="deleteSubmission(${sub.id})" class="text-red-600 hover:text-red-900" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function viewSubmission(sub) {
    const details = document.getElementById('submission-details');
    details.innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">Name</label>
                <p class="mt-1">${escapeHtml(sub.name)}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Email</label>
                <p class="mt-1"><a href="mailto:${escapeHtml(sub.email)}" class="text-cyan-600 hover:underline">${escapeHtml(sub.email)}</a></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Phone</label>
                <p class="mt-1">${escapeHtml(sub.phone || 'Not provided')}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Subject</label>
                <p class="mt-1">${escapeHtml(sub.subject)}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Message</label>
                <p class="mt-1 whitespace-pre-wrap">${escapeHtml(sub.message)}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Submitted</label>
                <p class="mt-1">${new Date(sub.submitted_at).toLocaleString()}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">IP Address</label>
                <p class="mt-1">${escapeHtml(sub.ip_address || 'Unknown')}</p>
            </div>
            <div class="flex gap-3">
                <button onclick="updateStatus(${sub.id}, 'read')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Mark as Read
                </button>
                <button onclick="updateStatus(${sub.id}, 'responded')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Mark as Responded
                </button>
            </div>
        </div>
    `;
    
    if (sub.status === 'new') {
        updateStatus(sub.id, 'read', false);
    }
    
    document.getElementById('view-modal').classList.remove('hidden');
    document.getElementById('view-modal').classList.add('flex');
}

function closeViewModal() {
    document.getElementById('view-modal').classList.add('hidden');
    document.getElementById('view-modal').classList.remove('flex');
    loadSubmissions();
}

async function updateStatus(id, status, reload = true) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(`${API_BASE}/contact`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && reload) {
            showAlert('Status updated successfully', 'success');
            loadSubmissions();
        }
    } catch (error) {
        if (reload) showAlert('Failed to update status', 'error');
    }
}

async function deleteSubmission(id) {
    if (!confirm('Are you sure you want to delete this submission?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/contact?id=${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadSubmissions();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to delete submission', 'error');
    }
}

function updateStats(submissions) {
    document.getElementById('total-submissions').textContent = submissions.length;
    document.getElementById('new-count').textContent = submissions.filter(s => s.status === 'new').length;
    document.getElementById('responded-count').textContent = submissions.filter(s => s.status === 'responded').length;
}

function getStatusColor(status) {
    const colors = {
        'new': 'bg-amber-100 text-amber-800',
        'read': 'bg-blue-100 text-blue-800',
        'responded': 'bg-green-100 text-green-800'
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

document.getElementById('filter-status').addEventListener('change', loadSubmissions);

loadSubmissions();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
