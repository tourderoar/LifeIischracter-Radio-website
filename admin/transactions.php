<?php
/**
 * Life is Character Radio - Transactions
 * 
 * Location: /admin/transactions.php
 * 
 * View all transactions (donations + event registrations)
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$pageTitle = 'Transactions';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container" class="mb-6"></div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-800 mt-1" id="total-revenue">$0.00</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Donations</p>
                <p class="text-2xl font-bold text-gray-800 mt-1" id="total-donations">$0.00</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-heart text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Event Revenue</p>
                <p class="text-2xl font-bold text-gray-800 mt-1" id="total-events">$0.00</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Transactions</p>
                <p class="text-2xl font-bold text-gray-800 mt-1" id="total-count">0</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exchange-alt text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Transactions List -->
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">All Transactions</h2>
            <button onclick="exportTransactions()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-download mr-2"></i> Export CSV
            </button>
        </div>
        
        <div class="flex flex-col md:flex-row gap-4">
            <select id="filter-type" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="all">All Types</option>
                <option value="donation">Donations</option>
                <option value="event_registration">Event Registrations</option>
            </select>
            
            <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="all">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
            
            <input type="date" id="date-from" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
            <input type="date" id="date-to" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500">
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody id="transactions-tbody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        <p>Loading transactions...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span id="showing-count">0</span> transactions
        </div>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_URL ?>api/admin';
const CSRF_TOKEN = '<?= $csrf_token ?>';

async function loadTransactions() {
    const type = document.getElementById('filter-type').value;
    const status = document.getElementById('filter-status').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    
    try {
        const params = new URLSearchParams({ 
            type, 
            status, 
            date_from: dateFrom, 
            date_to: dateTo,
            limit: 100 
        });
        
        const response = await fetch(`${API_BASE}/transactions?${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderTransactions(data.data.transactions);
            updateStats(data.data.stats);
            document.getElementById('showing-count').textContent = data.data.transactions.length;
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('Failed to load transactions', 'error');
    }
}

function renderTransactions(transactions) {
    const tbody = document.getElementById('transactions-tbody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No transactions found</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(txn => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">#${txn.id}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${txn.transaction_type === 'donation' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'}">
                    ${txn.transaction_type === 'donation' ? 'Donation' : 'Event Reg'}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(txn.user_name || 'Anonymous')}</td>
            <td class="px-6 py-4 text-sm font-semibold text-gray-900">$${parseFloat(txn.amount).toFixed(2)}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(txn.payment_method || 'N/A')}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(txn.payment_status)}">
                    ${txn.payment_status}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">${new Date(txn.created_at).toLocaleString()}</td>
        </tr>
    `).join('');
}

function updateStats(stats) {
    document.getElementById('total-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toFixed(2);
    document.getElementById('total-donations').textContent = '$' + parseFloat(stats.total_donations || 0).toFixed(2);
    document.getElementById('total-events').textContent = '$' + parseFloat(stats.total_events || 0).toFixed(2);
    document.getElementById('total-count').textContent = stats.total_count || 0;
}

function getStatusColor(status) {
    const colors = {
        'completed': 'bg-green-100 text-green-800',
        'pending': 'bg-amber-100 text-amber-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function exportTransactions() {
    const type = document.getElementById('filter-type').value;
    const status = document.getElementById('filter-status').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    
    const params = new URLSearchParams({ 
        action: 'export',
        type, 
        status, 
        date_from: dateFrom, 
        date_to: dateTo 
    });
    
    window.location.href = `${API_BASE}/transactions?${params}`;
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

// Event listeners
document.getElementById('filter-type').addEventListener('change', loadTransactions);
document.getElementById('filter-status').addEventListener('change', loadTransactions);
document.getElementById('date-from').addEventListener('change', loadTransactions);
document.getElementById('date-to').addEventListener('change', loadTransactions);

loadTransactions();
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
