<?php
/**
 * Life is Character Radio - Admins Management
 * 
 * Location: /admin/admins.php
 * 
 * Manage admin users and role assignments (Super Admin only)
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_admins');

$pageTitle = 'Admins Management';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Admins List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Admin Users</h2>
            <button onclick="openAdminModal()" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                <i class="fas fa-plus mr-2"></i> Add Admin
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="admins-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading admins...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Admin Modal -->
<div id="admin-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h3 class="text-2xl font-bold text-gray-800" id="modal-title">Add Admin</h3>
            <button onclick="closeAdminModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="admin-form" class="p-6">
            <input type="hidden" id="admin-id" name="id">
            
            <div id="create-fields">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Role <span class="text-red-500">*</span></label>
                <select id="role_id" name="role_id" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">Select role...</option>
                </select>
            </div>
            
            <div id="edit-fields" class="mb-4 hidden">
                <label class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" class="mr-2">
                    <span class="text-gray-700">Active</span>
                </label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeAdminModal()" class="px-6 py-2 border rounded-lg hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                    <i class="fas fa-save mr-2"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>api/admin';
const CSRF_TOKEN = '<?= $csrf_token ?>';
const CURRENT_ADMIN_ID = <?= $_SESSION['admin_id'] ?>;
let roles = [];

async function loadAdmins() {
    try {
        const response = await fetch(`${API_BASE}/admins`);
        const data = await response.json();
        
        if (data.success) {
            renderAdmins(data.data.admins);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load admins', 'error');
    }
}

async function loadRoles() {
    try {
        const response = await fetch(`${API_BASE}/roles`);
        const data = await response.json();
        
        if (data.success) {
            roles = data.data.roles;
            const select = document.getElementById('role_id');
            select.innerHTML = '<option value="">Select role...</option>' + 
                roles.filter(r => r.is_active).map(role => 
                    `<option value="${role.id}">${escapeHtml(role.name)}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Failed to load roles');
    }
}

function renderAdmins(admins) {
    const tbody = document.getElementById('admins-tbody');
    
    if (admins.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No admins found</td></tr>';
        return;
    }
    
    tbody.innerHTML = admins.map(admin => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-cyan-100 flex items-center justify-center">
                        <span class="text-cyan-600 font-semibold">${admin.full_name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                            ${escapeHtml(admin.full_name)}
                            ${admin.id === CURRENT_ADMIN_ID ? '<span class="text-xs text-cyan-600">(You)</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-500">${escapeHtml(admin.email)}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(admin.username)}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    ${escapeHtml(admin.role_name || 'No Role')}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                ${new Date(admin.created_at).toLocaleDateString()}
            </td>
            <td class="px-6 py-4">
                ${admin.is_active ? 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' : 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>'
                }
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex gap-2">
                    ${admin.id !== CURRENT_ADMIN_ID ? `
                        <button onclick='editAdmin(${JSON.stringify(admin)})' class="text-blue-600 hover:text-blue-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteAdmin(${admin.id})" class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : '<span class="text-gray-400">-</span>'}
                </div>
            </td>
        </tr>
    `).join('');
}

function openAdminModal() {
    document.getElementById('admin-id').value = '';
    document.getElementById('admin-form').reset();
    document.getElementById('modal-title').textContent = 'Add Admin';
    document.getElementById('create-fields').classList.remove('hidden');
    document.getElementById('edit-fields').classList.add('hidden');
    document.getElementById('password').required = true;
    document.getElementById('admin-modal').classList.remove('hidden');
    document.getElementById('admin-modal').classList.add('flex');
}

function editAdmin(admin) {
    document.getElementById('admin-id').value = admin.id;
    document.getElementById('role_id').value = admin.role_id;
    document.getElementById('is_active').checked = admin.is_active;
    document.getElementById('modal-title').textContent = 'Edit Admin';
    document.getElementById('create-fields').classList.add('hidden');
    document.getElementById('edit-fields').classList.remove('hidden');
    document.getElementById('admin-modal').classList.remove('hidden');
    document.getElementById('admin-modal').classList.add('flex');
}

function closeAdminModal() {
    document.getElementById('admin-modal').classList.add('hidden');
    document.getElementById('admin-modal').classList.remove('flex');
}

document.getElementById('admin-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    const id = document.getElementById('admin-id').value;
    if (!id) {
        formData.append('_method', 'PUT');
    }
    
    try {
        const response = await fetch(`${API_BASE}/admins`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            closeAdminModal();
            loadAdmins();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to save admin', 'error');
    }
});

async function deleteAdmin(adminId) {
    if (!confirm('Are you sure you want to delete this admin?')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', adminId);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('_method', 'DELETE');
        
        const response = await fetch(`${API_BASE}/admins`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadAdmins();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to delete admin', 'error');
    }
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
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initial load
loadAdmins();
loadRoles();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
