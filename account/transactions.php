<?php
/**
 * Life is Character Radio - User Transactions
 * Location: /account/transactions.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireUserAuth();

$pageTitle = 'Transactions';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="pt-28 pb-12 bg-gradient-to-b from-gray-50 to-white min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <div class="mb-8">
            <h1 class="text-4xl font-cinzel font-bold text-brand-navy mb-2">Transaction History</h1>
            <p class="text-gray-600">View all your donations and event registrations</p>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                    <select id="filterType" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="donation">Donations</option>
                        <option value="event_registration">Event Registrations</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="filterStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button id="clearFilters" class="w-full bg-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300 transition text-center">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <div id="transactionsContainer" class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Loading state -->
            <div id="loadingState" class="p-12 text-center">
                <svg class="animate-spin h-12 w-12 text-cyan-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">Loading transactions...</p>
            </div>
            
            <!-- Empty state -->
            <div id="emptyState" class="p-12 text-center hidden">
                <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-500 text-lg mb-4">No transactions found</p>
                <div class="space-x-4">
                    <a href="<?= BASE_URL ?>donate" class="inline-block bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:shadow-lg transition">
                        Make a Donation
                    </a>
                    <a href="<?= BASE_URL ?>events" class="inline-block bg-white border-2 border-cyan-500 text-brand-navy font-semibold px-6 py-2 rounded-lg hover:shadow-lg transition">
                        Browse Events
                    </a>
                </div>
            </div>
            
            <!-- Table -->
            <div id="tableContainer" class="hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Date</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Type</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Description</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Amount</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Payment Method</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsBody" class="divide-y divide-gray-200">
                            <!-- Rows will be inserted here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary -->
                <div id="summary" class="bg-gray-50 px-6 py-4 border-t">
                    <!-- Summary will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</section>

<script>
window.API_BASE_URL = '<?= BASE_URL ?>api/user/';
</script>
<script src="<?= BASE_URL ?>js/user-api.js"></script>
<script>
const filterType = document.getElementById('filterType');
const filterStatus = document.getElementById('filterStatus');
const clearFiltersBtn = document.getElementById('clearFilters');

// Load transactions
async function loadTransactions() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const tableContainer = document.getElementById('tableContainer');
    const transactionsBody = document.getElementById('transactionsBody');
    const summary = document.getElementById('summary');
    
    // Show loading
    loadingState.classList.remove('hidden');
    emptyState.classList.add('hidden');
    tableContainer.classList.add('hidden');
    
    try {
        const response = await UserAPI.getTransactions(filterType.value, filterStatus.value);
        const transactions = response.data.transactions;
        const summaryData = response.data.summary;
        
        loadingState.classList.add('hidden');
        
        if (transactions.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            tableContainer.classList.remove('hidden');
            
            // Build table rows
            transactionsBody.innerHTML = transactions.map(tx => {
                const typeClass = tx.transaction_type === 'donation' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700';
                const statusClass = tx.payment_status === 'completed' ? 'bg-green-100 text-green-700' : 
                                   tx.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 
                                   'bg-red-100 text-red-700';
                
                return `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">${UIHelpers.formatDate(tx.created_at)}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${typeClass}">
                                ${tx.transaction_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">${tx.description || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm font-bold text-brand-navy">${UIHelpers.formatCurrency(tx.amount)}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusClass}">
                                ${tx.payment_status.charAt(0).toUpperCase() + tx.payment_status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">${tx.payment_method ? tx.payment_method.charAt(0).toUpperCase() + tx.payment_method.slice(1) : 'N/A'}</td>
                    </tr>
                `;
            }).join('');
            
            // Build summary
            summary.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Total Transactions: <strong>${summaryData.total_count}</strong></span>
                    <span class="text-gray-600">Total Amount: <strong class="text-brand-navy">${UIHelpers.formatCurrency(summaryData.total_amount)}</strong></span>
                </div>
            `;
        }
        
    } catch (error) {
        loadingState.classList.add('hidden');
        console.error('Failed to load transactions:', error);
        emptyState.innerHTML = `
            <div class="p-12 text-center">
                <svg class="w-20 h-20 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-500 text-lg">Failed to load transactions</p>
                <p class="text-gray-500 mt-2">${error.message || 'Please try again later'}</p>
            </div>
        `;
        emptyState.classList.remove('hidden');
    }
}

// Filter change handlers
filterType.addEventListener('change', loadTransactions);
filterStatus.addEventListener('change', loadTransactions);

clearFiltersBtn.addEventListener('click', () => {
    filterType.value = '';
    filterStatus.value = '';
    loadTransactions();
});

// Load transactions on page load
loadTransactions();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

        
        <div class="mt-8 text-center">
            <a href="<?= BASE_URL ?>account/dashboard" class="text-cyan-600 hover:text-cyan-700 font-semibold">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
