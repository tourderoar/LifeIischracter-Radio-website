<?php
/**
 * Life is Character Radio - Donations Management
 * 
 * Location: /admin/donations.php
 * 
 * Manage donation types and view all donations
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();
requirePermission('manage_donations');

$pageTitle = 'Donations Management';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Donations</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-donations">-</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-hand-holding-heart text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="total-revenue">$0</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Donation Types</p>
                <p class="text-3xl font-bold text-gray-800 mt-1" id="types-count">-</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-list text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="bg-white rounded-xl shadow-sm mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchTab('types')" id="tab-types" class="tab-btn tab-active px-6 py-4 border-b-2 font-medium text-sm transition">
                Donation Types
            </button>
            <button onclick="switchTab('donations')" id="tab-donations" class="tab-btn px-6 py-4 border-b-2 font-medium text-sm transition">
                All Donations
            </button>
        </nav>
    </div>
</div>

<!-- Donation Types Section -->
<div id="section-types" class="bg-white rounded-xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Donation Types</h3>
        <button onclick="openTypeModal()" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>New Type
        </button>
    </div>
    <div id="types-container">
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
            <p class="text-gray-600 mt-4">Loading donation types...</p>
        </div>
    </div>
</div>

<!-- Donations List Section -->
<div id="section-donations" class="hidden bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">All Donations</h3>
    <div id="donations-container">
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
            <p class="text-gray-600 mt-4">Loading donations...</p>
        </div>
    </div>
</div>

<!-- Donation Type Modal -->
<div id="type-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="modal-title" class="text-2xl font-bold text-gray-800">New Donation Type</h3>
                <button onclick="closeTypeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <form id="type-form" class="p-6 space-y-4">
            <input type="hidden" id="type-id" name="id">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Name *</label>
                <input type="text" id="type-name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Description</label>
                <textarea id="type-description" name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Suggested Amount ($)</label>
                    <input type="number" id="type-amount" name="suggested_amount" min="0" step="0.01" placeholder="Optional" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Sort Order</label>
                    <input type="number" id="type-order" name="sort_order" value="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="type-active" name="is_active" value="1" checked class="w-5 h-5 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                <label for="type-active" class="ml-2 text-gray-700 font-medium">Active</label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeTypeModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" id="save-btn" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-lg font-semibold hover:shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Save Type
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.tab-btn { border-color: transparent; color: #6b7280; }
.tab-btn:hover { color: #374151; border-color: #d1d5db; }
.tab-active { color: #0891b2; border-color: #0891b2; }
</style>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= htmlspecialchars($csrf_token) ?>';
let currentTab = 'types';
let types = [];
let donations = [];

document.addEventListener('DOMContentLoaded', () => {
    loadTypes();
    loadDonations();
});

function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('tab-active'));
    document.getElementById(`tab-${tab}`).classList.add('tab-active');
    
    document.getElementById('section-types').classList.toggle('hidden', tab !== 'types');
    document.getElementById('section-donations').classList.toggle('hidden', tab !== 'donations');
}

async function loadTypes() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/donations?action=types`);
        const data = await res.json();
        
        if (data.success) {
            types = data.data.types;
            renderTypes(types);
            document.getElementById('types-count').textContent = types.length;
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Load types error:', err);
        showAlert('Failed to load donation types', 'error');
    }
}

function renderTypes(typesData) {
    const container = document.getElementById('types-container');
    
    if (typesData.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-inbox text-4xl mb-4"></i><p>No donation types found</p></div>';
        return;
    }
    
    container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            ${typesData.map(type => `
                <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition ${type.is_active ? '' : 'opacity-60'}">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="text-lg font-semibold text-gray-800">${escapeHtml(type.name)}</h4>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${type.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                            ${type.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">${escapeHtml(type.description || 'No description')}</p>
                    ${type.suggested_amount ? `<p class="text-cyan-600 font-bold text-xl mb-4">$${Number(type.suggested_amount).toFixed(2)}</p>` : ''}
                    <div class="flex gap-2">
                        <button onclick="editType(${type.id})" class="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition font-medium">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </button>
                        <button onclick="deleteType(${type.id})" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

async function loadDonations() {
    try {
        const res = await fetch(`${BASE_URL}api/admin/donations?action=donations`);
        const data = await res.json();
        
        if (data.success) {
            donations = data.data.donations;
            renderDonations(donations);
            document.getElementById('total-donations').textContent = data.data.total;
            document.getElementById('total-revenue').textContent = '$' + Number(data.data.totalRevenue).toFixed(2);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Load donations error:', err);
        showAlert('Failed to load donations', 'error');
    }
}

function renderDonations(donationsData) {
    const container = document.getElementById('donations-container');
    
    if (donationsData.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500"><i class="fas fa-hand-holding-heart text-4xl mb-4"></i><p>No donations yet</p></div>';
        return;
    }
    
    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recurring</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${donationsData.map(donation => `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">${donation.donor_name || 'Anonymous'}</div>
                                <div class="text-sm text-gray-500">${donation.donor_email || 'N/A'}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(donation.donation_type_name || 'N/A')}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">$${Number(donation.amount).toFixed(2)}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">${donation.is_recurring ? '<span class="text-blue-600"><i class="fas fa-repeat mr-1"></i>Yes</span>' : 'No'}</td>
                            <td class="px-6 py-4"><span class="status-badge status-${donation.payment_status}">${donation.payment_status}</span></td>
                            <td class="px-6 py-4 text-sm text-gray-600">${formatDate(donation.donated_at)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function openTypeModal(type = null) {
    document.getElementById('modal-title').textContent = type ? 'Edit Donation Type' : 'New Donation Type';
    document.getElementById('type-form').reset();
    
    // Explicitly clear the ID field when creating new type
    document.getElementById('type-id').value = '';
    
    if (type) {
        document.getElementById('type-id').value = type.id;
        document.getElementById('type-name').value = type.name;
        document.getElementById('type-description').value = type.description || '';
        document.getElementById('type-amount').value = type.suggested_amount || '';
        document.getElementById('type-order').value = type.sort_order;
        document.getElementById('type-active').checked = type.is_active == 1;
    }
    
    document.getElementById('type-modal').classList.remove('hidden');
}

function closeTypeModal() {
    document.getElementById('type-modal').classList.add('hidden');
}

function editType(typeId) {
    const type = types.find(t => t.id == typeId);
    if (type) {
        openTypeModal(type);
    } else {
        showAlert('Donation type not found', 'error');
    }
}

document.getElementById('type-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const isEdit = !!id;
    
    if (isEdit) formData.append('_method', 'PUT');
    if (!formData.get('is_active')) formData.append('is_active', '0');
    
    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const res = await fetch(`${BASE_URL}api/admin/donations`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            closeTypeModal();
            loadTypes();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Save type error:', err);
        showAlert('Failed to save donation type', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Type';
    }
});

async function deleteType(id) {
    if (!confirm('Deactivate this donation type?')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        formData.append('_method', 'DELETE');
        
        const res = await fetch(`${BASE_URL}api/admin/donations`, { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            loadTypes();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Delete type error:', err);
        showAlert('Failed to delete donation type', 'error');
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

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
.status-badge { padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
.status-completed { background-color: #d1fae5; color: #065f46; }
.status-pending { background-color: #fef3c7; color: #92400e; }
.status-failed { background-color: #fee2e2; color: #991b1b; }
</style>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
