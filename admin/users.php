<?php
/**
 * Life is Character Radio - Users Management
 * 
 * Location: /admin/users.php
 * 
 * Manage registered users: view, activate/deactivate, view transactions
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_users');

$pageTitle = 'Users Management';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Users</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-users">-</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Active Users</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="active-users">-</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">New This Month</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="new-users">-</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-plus text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Users List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Registered Users</h2>
        </div>
        
        <div class="flex flex-col md:flex-row gap-4">
            <input type="text" id="search-users" placeholder="Search by name, email, or phone..." 
                   class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
            
            <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="users-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading users...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span id="showing-count">0</span> of <span id="total-count">0</span> users
        </div>
        <div id="pagination" class="flex gap-2"></div>
    </div>
</div>

<!-- View User Modal -->
<div id="view-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto m-4">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h3 class="text-2xl font-bold text-gray-800">User Details</h3>
            <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="user-details" class="p-6"></div>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>api/admin';
const CSRF_TOKEN = '<?= $csrf_token ?>';
let currentPage = 0;
const limit = 50;

// Load users
async function loadUsers() {
    const search = document.getElementById('search-users').value;
    const status = document.getElementById('filter-status').value;
    
    try {
        const params = new URLSearchParams({
            search,
            status,
            limit,
            offset: currentPage * limit
        });
        
        const response = await fetch(`${API_BASE}/users?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderUsers(data.data.users);
            updatePagination(data.data.total);
            updateStats();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load users', 'error');
    }
}

function renderUsers(users) {
    const tbody = document.getElementById('users-tbody');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-cyan-100 flex items-center justify-center">
                        <span class="text-cyan-600 font-semibold">${user.full_name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${escapeHtml(user.full_name)}</div>
                        <div class="text-sm text-gray-500">ID: ${user.id}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">${escapeHtml(user.email)}</div>
                <div class="text-sm text-gray-500">${user.phone || 'N/A'}</div>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                    ${user.transaction_count} transactions
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                ${new Date(user.created_at).toLocaleDateString()}
            </td>
            <td class="px-6 py-4">
                ${user.is_active ? 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' : 
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>'
                }
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex gap-2">
                    <button onclick="toggleUserStatus(${user.id}, ${user.is_active})" 
                            class="text-${user.is_active ? 'red' : 'green'}-600 hover:text-${user.is_active ? 'red' : 'green'}-900"
                            title="${user.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="fas fa-${user.is_active ? 'ban' : 'check'}"></i>
                    </button>
                    <button onclick="viewUser(${user.id})" class="text-cyan-600 hover:text-cyan-900" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function toggleUserStatus(userId, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} this user?`)) return;
    
    try {
        const formData = new FormData();
        formData.append('id', userId);
        formData.append('action', 'toggle_status');
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(`${API_BASE}/users`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadUsers();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to update user status', 'error');
    }
}

async function viewUser(userId) {
    const details = document.getElementById('user-details');
    const modal = document.getElementById('view-modal');

    details.innerHTML = '<div class="py-12 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-3xl"></i></div>';
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    try {
        const response = await fetch(`${API_BASE}/users?id=${userId}`);
        const data = await response.json();

        if (!data.success) {
            details.innerHTML = `<p class="text-center text-red-600 py-8">${escapeHtml(data.message)}</p>`;
            return;
        }

        const user = data.data.user;
        const transactions = data.data.transactions;

        const rows = transactions.length === 0
            ? '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No transactions</td></tr>'
            : transactions.map(t => `
                <tr class="border-t">
                    <td class="px-4 py-2 text-sm text-gray-700">${t.transaction_type.replace('_', ' ')}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 font-medium">$${Number(t.amount).toFixed(2)}</td>
                    <td class="px-4 py-2"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${getPaymentStatusColor(t.payment_status)}">${t.payment_status}</span></td>
                    <td class="px-4 py-2 text-sm text-gray-500">${new Date(t.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');

        details.innerHTML = `
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-full bg-cyan-100 flex items-center justify-center">
                        <span class="text-cyan-600 text-2xl font-semibold">${user.full_name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900">${escapeHtml(user.full_name)}</div>
                        ${user.is_active
                            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>'
                            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>'}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Email</label><p class="text-sm text-gray-900">${escapeHtml(user.email)}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Phone</label><p class="text-sm text-gray-900">${user.phone ? escapeHtml(user.phone) : 'N/A'}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">User ID</label><p class="text-sm text-gray-900">${user.id}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Joined</label><p class="text-sm text-gray-900">${new Date(user.created_at).toLocaleString()}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Last Login</label><p class="text-sm text-gray-900">${user.last_login ? new Date(user.last_login).toLocaleString() : 'Never'}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase">Total Spent</label><p class="text-sm text-gray-900 font-semibold">$${Number(user.total_spent).toFixed(2)}</p></div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Transaction History (${user.transaction_count})</h4>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        details.innerHTML = '<p class="text-center text-red-600 py-8">Failed to load user details</p>';
    }
}

function closeViewModal() {
    const modal = document.getElementById('view-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function getPaymentStatusColor(status) {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-amber-100 text-amber-800',
        failed: 'bg-red-100 text-red-800',
        refunded: 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

async function updateStats() {
    try {
        const response = await fetch(`${API_BASE}/users?status=all&limit=1000`);
        const data = await response.json();
        
        if (data.success) {
            const users = data.data.users;
            const activeUsers = users.filter(u => u.is_active).length;
            const thisMonth = users.filter(u => {
                const created = new Date(u.created_at);
                const now = new Date();
                return created.getMonth() === now.getMonth() && created.getFullYear() === now.getFullYear();
            }).length;
            
            document.getElementById('total-users').textContent = users.length;
            document.getElementById('active-users').textContent = activeUsers;
            document.getElementById('new-users').textContent = thisMonth;
        }
    } catch (error) {
        console.error('Failed to load stats');
    }
}

function updatePagination(total) {
    const totalPages = Math.ceil(total / limit);
    const pagination = document.getElementById('pagination');
    
    document.getElementById('showing-count').textContent = Math.min((currentPage + 1) * limit, total);
    document.getElementById('total-count').textContent = total;
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '';
    if (currentPage > 0) {
        html += `<button onclick="changePage(${currentPage - 1})" class="px-3 py-1 border rounded hover:bg-gray-100">Previous</button>`;
    }
    
    for (let i = 0; i < totalPages; i++) {
        if (i === currentPage) {
            html += `<button class="px-3 py-1 bg-cyan-500 text-white rounded">${i + 1}</button>`;
        } else if (i < 3 || i > totalPages - 4 || Math.abs(i - currentPage) < 2) {
            html += `<button onclick="changePage(${i})" class="px-3 py-1 border rounded hover:bg-gray-100">${i + 1}</button>`;
        } else if (i === 3 || i === totalPages - 4) {
            html += `<span class="px-2">...</span>`;
        }
    }
    
    if (currentPage < totalPages - 1) {
        html += `<button onclick="changePage(${currentPage + 1})" class="px-3 py-1 border rounded hover:bg-gray-100">Next</button>`;
    }
    
    pagination.innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadUsers();
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

// Event listeners
document.getElementById('search-users').addEventListener('input', () => {
    currentPage = 0;
    loadUsers();
});

document.getElementById('filter-status').addEventListener('change', () => {
    currentPage = 0;
    loadUsers();
});

// Initial load
loadUsers();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
