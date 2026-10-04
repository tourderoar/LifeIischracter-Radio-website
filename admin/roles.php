<?php
/**
 * Life is Character Radio - Roles Management
 * 
 * Location: /admin/roles.php
 * 
 * Manage roles and permissions (Super Admin only)
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_roles');

$pageTitle = 'Roles & Permissions';
$csrf_token = generateCsrfToken();

$availablePermissions = [
    'manage_events' => 'Manage Events',
    'manage_donations' => 'Manage Donations',
    'manage_blog' => 'Manage Blog',
    'manage_episodes' => 'Manage Episodes',
    'manage_users' => 'Manage Users',
    'manage_admins' => 'Manage Admins',
    'manage_roles' => 'Manage Roles',
    'view_reports' => 'View Reports',
    'manage_settings' => 'Manage Settings'
];

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Roles List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Roles & Permissions</h2>
            <button onclick="openRoleModal()" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                <i class="fas fa-plus mr-2"></i> Add Role
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admins</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="roles-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading roles...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Role Modal -->
<div id="role-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h3 class="text-2xl font-bold text-gray-800" id="modal-title">Add Role</h3>
            <button onclick="closeRoleModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="role-form" class="p-6">
            <input type="hidden" id="role-id" name="id">
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Role Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Permissions <span class="text-red-500">*</span></label>
                <div class="space-y-2 bg-gray-50 p-4 rounded-lg max-h-96 overflow-y-auto">
                    <?php foreach ($availablePermissions as $perm => $label): ?>
                    <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="<?= $perm ?>" 
                               class="permission-checkbox mr-3 w-4 h-4 text-cyan-600 focus:ring-cyan-500">
                        <span class="text-gray-700"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-sm text-gray-500 mt-2">Select at least one permission</p>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" class="mr-2" checked>
                    <span class="text-gray-700">Active</span>
                </label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeRoleModal()" class="px-6 py-2 border rounded-lg hover:bg-gray-100">
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

async function loadRoles() {
    try {
        const response = await fetch(`${API_BASE}/roles`);
        const data = await response.json();
        
        if (data.success) {
            renderRoles(data.data.roles);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load roles', 'error');
    }
}

function renderRoles(roles) {
    const tbody = document.getElementById('roles-tbody');
    
    if (roles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No roles found</td></tr>';
        return;
    }
    
    tbody.innerHTML = roles.map(role => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">${escapeHtml(role.name)}</div>
                <div class="text-sm text-gray-500">${escapeHtml(role.description || '')}</div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                    ${role.permissions.slice(0, 3).map(p => 
                        `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">${p.replace(/_/g, ' ')}</span>`
                    ).join('')}
                    ${role.permissions.length > 3 ? `<span class="text-xs text-gray-500">+${role.permissions.length - 3} more</span>` : ''}
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    ${role.admin_count} admin${role.admin_count !== 1 ? 's' : ''}
                </span>
            </td>
            <td class="px-6 py-4">
                ${role.is_active ? 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' : 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>'
                }
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex gap-2">
                    ${role.name !== 'Super Admin' ? `
                        <button onclick='editRole(${JSON.stringify(role)})' class="text-blue-600 hover:text-blue-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteRole(${role.id}, '${escapeHtml(role.name)}')" class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : '<span class="text-gray-400 text-xs">Protected</span>'}
                </div>
            </td>
        </tr>
    `).join('');
}

function openRoleModal() {
    document.getElementById('role-id').value = '';
    document.getElementById('role-form').reset();
    document.getElementById('modal-title').textContent = 'Add Role';
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('is_active').checked = true;
    document.getElementById('role-modal').classList.remove('hidden');
    document.getElementById('role-modal').classList.add('flex');
}

function editRole(role) {
    document.getElementById('role-id').value = role.id;
    document.getElementById('name').value = role.name;
    document.getElementById('description').value = role.description || '';
    document.getElementById('is_active').checked = role.is_active;
    
    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.checked = role.permissions.includes(cb.value);
    });
    
    document.getElementById('modal-title').textContent = 'Edit Role';
    document.getElementById('role-modal').classList.remove('hidden');
    document.getElementById('role-modal').classList.add('flex');
}

function closeRoleModal() {
    document.getElementById('role-modal').classList.add('hidden');
    document.getElementById('role-modal').classList.remove('flex');
}

document.getElementById('role-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const checkedPerms = Array.from(document.querySelectorAll('.permission-checkbox:checked')).map(cb => cb.value);
    if (checkedPerms.length === 0) {
        showAlert('Please select at least one permission', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    const id = document.getElementById('role-id').value;
    if (!id) {
        formData.append('_method', 'PUT');
    }
    
    try {
        const response = await fetch(`${API_BASE}/roles`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            closeRoleModal();
            loadRoles();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to save role', 'error');
    }
});

async function deleteRole(roleId, roleName) {
    if (!confirm(`Are you sure you want to delete the role "${roleName}"?`)) return;
    
    try {
        const formData = new FormData();
        formData.append('id', roleId);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('_method', 'DELETE');
        
        const response = await fetch(`${API_BASE}/roles`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadRoles();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to delete role', 'error');
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
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initial load
loadRoles();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
