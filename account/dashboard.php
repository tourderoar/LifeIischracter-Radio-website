<?php
/**
 * Life is Character Radio - User Dashboard
 * Location: /account/dashboard.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireUserAuth();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="pt-28 pb-12 bg-gradient-to-b from-gray-50 to-white min-h-screen">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-cinzel font-bold text-brand-navy mb-2">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
            <p class="text-gray-600">Here's an overview of your account</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="statsContainer">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-cyan-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Upcoming Events</p>
                        <p class="text-3xl font-bold text-brand-navy"><span class="animate-pulse">...</span></p>
                    </div>
                    <div class="bg-cyan-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Total Donations</p>
                        <p class="text-3xl font-bold text-brand-navy"><span class="animate-pulse">...</span></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-gold">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Total Transactions</p>
                        <p class="text-3xl font-bold text-brand-navy"><span class="animate-pulse">...</span></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <a href="<?= BASE_URL ?>events" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-semibold">Browse Events</span>
            </a>
            
            <a href="<?= BASE_URL ?>donate" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="font-semibold">Make Donation</span>
            </a>
            
            <a href="<?= BASE_URL ?>account/profile" class="bg-white border-2 border-cyan-500 text-brand-navy p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="font-semibold">Edit Profile</span>
            </a>
            
            <a href="<?= BASE_URL ?>account/transactions" class="bg-white border-2 border-cyan-500 text-brand-navy p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="font-semibold">View History</span>
            </a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-cinzel font-bold text-brand-navy mb-4">Upcoming Events</h2>
                <div id="upcomingEvents">
                    <div class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-cyan-500 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500 mt-2">Loading events...</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-cinzel font-bold text-brand-navy mb-4">Recent Transactions</h2>
                <div id="recentTransactions">
                    <div class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-cyan-500 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500 mt-2">Loading transactions...</p>
                    </div>
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
async function loadDashboard() {
    try {
        const data = await UserAPI.getDashboard();
        
        // Update stats
        const stats = data.data.stats;
        const statsHTML = `
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-cyan-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Upcoming Events</p>
                        <p class="text-3xl font-bold text-brand-navy">${stats.upcoming_events}</p>
                    </div>
                    <div class="bg-cyan-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Total Donations</p>
                        <p class="text-3xl font-bold text-brand-navy">$${stats.total_donations}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-gold">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Total Transactions</p>
                        <p class="text-3xl font-bold text-brand-navy">${stats.total_transactions}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('statsContainer').innerHTML = statsHTML;
        
        // Update upcoming events
        const eventsContainer = document.getElementById('upcomingEvents');
        if (data.data.upcoming_events.length === 0) {
            eventsContainer.innerHTML = `
                <p class="text-gray-500 text-center py-8">No upcoming events registered</p>
                <a href="<?= BASE_URL ?>events" class="block text-center text-cyan-600 hover:text-cyan-700 font-semibold">Browse Events</a>
            `;
        } else {
            const eventsHTML = data.data.upcoming_events.map(event => `
                <div class="border-l-4 border-cyan-500 pl-4 py-2">
                    <h3 class="font-semibold text-brand-navy">${event.title}</h3>
                    <p class="text-sm text-gray-600">${UIHelpers.formatDateTime(event.event_date)}</p>
                    <p class="text-sm text-gray-500">${event.location || ''}</p>
                </div>
            `).join('');
            eventsContainer.innerHTML = `<div class="space-y-4">${eventsHTML}</div>`;
        }
        
        // Update recent transactions
        const txContainer = document.getElementById('recentTransactions');
        if (data.data.recent_transactions.length === 0) {
            txContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No transactions yet</p>';
        } else {
            const txHTML = data.data.recent_transactions.map(tx => `
                <div class="flex items-center justify-between border-b pb-3">
                    <div>
                        <p class="font-semibold text-brand-navy">${tx.transaction_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                        <p class="text-sm text-gray-500">${UIHelpers.formatDate(tx.created_at)}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-brand-navy">${UIHelpers.formatCurrency(tx.amount)}</p>
                        <span class="text-xs px-2 py-1 rounded-full ${tx.payment_status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">
                            ${tx.payment_status.charAt(0).toUpperCase() + tx.payment_status.slice(1)}
                        </span>
                    </div>
                </div>
            `).join('');
            txContainer.innerHTML = `
                <div class="space-y-4">${txHTML}</div>
                <a href="<?= BASE_URL ?>account/transactions" class="block text-center text-cyan-600 hover:text-cyan-700 font-semibold mt-4">View All Transactions</a>
            `;
        }
        
    } catch (error) {
        console.error('Dashboard load error:', error);
        document.getElementById('statsContainer').innerHTML = `
            <div class="col-span-3 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                Failed to load dashboard data. Please refresh the page.
            </div>
        `;
    }
}

// Load dashboard on page load
loadDashboard();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
        
        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <a href="<?= BASE_URL ?>events" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-semibold">Browse Events</span>
            </a>
            
            <a href="<?= BASE_URL ?>donate" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="font-semibold">Make Donation</span>
            </a>
            
            <a href="<?= BASE_URL ?>account/profile" class="bg-white border-2 border-cyan-500 text-brand-navy p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="font-semibold">Edit Profile</span>
            </a>
            
            <a href="<?= BASE_URL ?>account/transactions" class="bg-white border-2 border-cyan-500 text-brand-navy p-4 rounded-xl hover:shadow-xl transition duration-300 transform hover:scale-105 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="font-semibold">View History</span>
            </a>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-cinzel font-bold text-brand-navy mb-4">Upcoming Events</h2>
                <?php if (empty($upcomingEvents)): ?>
                    <p class="text-gray-500 text-center py-8">No upcoming events registered</p>
                    <a href="<?= BASE_URL ?>events" class="block text-center text-cyan-600 hover:text-cyan-700 font-semibold">Browse Events</a>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($upcomingEvents as $event): ?>
                            <div class="border-l-4 border-cyan-500 pl-4 py-2">
                                <h3 class="font-semibold text-brand-navy"><?= htmlspecialchars($event['title']) ?></h3>
                                <p class="text-sm text-gray-600"><?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($event['location']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-cinzel font-bold text-brand-navy mb-4">Recent Transactions</h2>
                <?php if (empty($recentTransactions)): ?>
                    <p class="text-gray-500 text-center py-8">No transactions yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentTransactions as $tx): ?>
                            <div class="flex items-center justify-between border-b pb-3">
                                <div>
                                    <p class="font-semibold text-brand-navy"><?= ucfirst(str_replace('_', ' ', $tx['type'])) ?></p>
                                    <p class="text-sm text-gray-500"><?= date('M j, Y', strtotime($tx['created_at'])) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-brand-navy">$<?= number_format($tx['amount'], 2) ?></p>
                                    <span class="text-xs px-2 py-1 rounded-full <?= $tx['status'] === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                        <?= ucfirst($tx['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= BASE_URL ?>account/transactions" class="block text-center text-cyan-600 hover:text-cyan-700 font-semibold mt-4">View All Transactions</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
