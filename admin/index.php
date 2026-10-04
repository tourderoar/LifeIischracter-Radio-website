<?php
/**
 * Life is Character Radio - Admin Dashboard
 * 
 * Location: /admin/index.php
 * 
 * Overview statistics and quick links
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$pageTitle = 'Dashboard';

include __DIR__ . '/includes/admin_header.php';
?>

<div id="dashboard-stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Loading state -->
    <div class="col-span-4 text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl text-cyan-500"></i>
        <p class="text-gray-600 mt-4">Loading dashboard...</p>
    </div>
</div>

<div id="dashboard-content" class="grid grid-cols-1 lg:grid-cols-2 gap-6" style="display:none;">
    <!-- Recent Contact Submissions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Contact Submissions</h3>
            <a href="<?= BASE_URL ?>admin/contact" class="text-cyan-600 hover:text-cyan-700 text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div id="recent-contacts"></div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
            <a href="<?= BASE_URL ?>admin/transactions" class="text-cyan-600 hover:text-cyan-700 text-sm">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div id="recent-transactions"></div>
    </div>
</div>

<!-- Quick Actions -->
<div id="quick-actions" class="mt-8" style="display:none;">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php if (hasPermission('manage_events') || isSuperAdmin()): ?>
        <a href="<?= BASE_URL ?>admin/events" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-6 text-center transition">
            <i class="fas fa-calendar-plus text-3xl text-cyan-600 mb-3"></i>
            <p class="font-medium text-gray-800">Create Event</p>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('manage_blog') || isSuperAdmin()): ?>
        <a href="<?= BASE_URL ?>admin/blog" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-6 text-center transition">
            <i class="fas fa-pen text-3xl text-blue-600 mb-3"></i>
            <p class="font-medium text-gray-800">Write Post</p>
        </a>
        <?php endif; ?>

        <?php if (hasPermission('manage_episodes') || isSuperAdmin()): ?>
        <a href="<?= BASE_URL ?>admin/episodes" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-6 text-center transition">
            <i class="fas fa-video text-3xl text-purple-600 mb-3"></i>
            <p class="font-medium text-gray-800">Add Episode</p>
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>admin/contact" class="bg-white hover:bg-gray-50 rounded-xl shadow-sm p-6 text-center transition">
            <i class="fas fa-envelope-open text-3xl text-yellow-600 mb-3"></i>
            <p class="font-medium text-gray-800">View Messages</p>
        </a>
    </div>
</div>

<script>
// Fetch dashboard data via API
fetch('<?= BASE_URL ?>api/admin/dashboard')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderDashboard(data.data);
        } else {
            showError(data.message);
        }
    })
    .catch(err => {
        console.error('Dashboard error:', err);
        showError('Failed to load dashboard data');
    });

function renderDashboard(data) {
    const stats = data.stats;
    const recent = data.recent;
    
    // Render stats cards
    document.getElementById('dashboard-stats').innerHTML = `
        <!-- Events Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-cyan-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Events</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">${stats.events.upcoming}</p>
                    <p class="text-sm text-gray-600 mt-1">Upcoming</p>
                </div>
                <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-cyan-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Users</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">${stats.users.total}</p>
                    <p class="text-sm text-gray-600 mt-1">${stats.users.active} active</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Donations Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Donations</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">$${Number(stats.donations.total_amount).toLocaleString()}</p>
                    <p class="text-sm text-gray-600 mt-1">${stats.donations.count} donations</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hand-holding-heart text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Content</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">${stats.content.episodes + stats.content.posts}</p>
                    <p class="text-sm text-gray-600 mt-1">${stats.content.episodes} episodes, ${stats.content.posts} posts</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-blog text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    `;
    
    // Render recent contacts
    if (recent.contacts.length === 0) {
        document.getElementById('recent-contacts').innerHTML = '<p class="text-gray-500 text-center py-8">No contact submissions yet</p>';
    } else {
        document.getElementById('recent-contacts').innerHTML = '<div class="space-y-3">' +
            recent.contacts.map(contact => `
                <div class="border-l-4 border-cyan-400 bg-gray-50 p-4 rounded">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">${escapeHtml(contact.name)}</p>
                            <p class="text-sm text-gray-600 mt-1">${escapeHtml(contact.subject)}</p>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap ml-4">
                            ${formatDate(contact.submitted_at)}
                        </span>
                    </div>
                </div>
            `).join('') + '</div>';
    }
    
    // Render recent transactions
    if (recent.transactions.length === 0) {
        document.getElementById('recent-transactions').innerHTML = '<p class="text-gray-500 text-center py-8">No transactions yet</p>';
    } else {
        document.getElementById('recent-transactions').innerHTML = '<div class="space-y-3">' +
            recent.transactions.map(txn => `
                <div class="border-l-4 border-green-400 bg-gray-50 p-4 rounded">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">
                                $${Number(txn.amount).toFixed(2)}
                                <span class="text-sm text-gray-600 ml-2">${txn.transaction_type.replace('_', ' ')}</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">${escapeHtml(txn.full_name || 'Anonymous')}</p>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap ml-4">
                            ${formatDate(txn.created_at)}
                        </span>
                    </div>
                </div>
            `).join('') + '</div>';
    }
    
    document.getElementById('dashboard-content').style.display = 'grid';
    document.getElementById('quick-actions').style.display = 'block';
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    document.getElementById('dashboard-stats').innerHTML = `
        <div class="col-span-4 bg-red-500/10 border border-red-500 text-red-400 px-6 py-4 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>${escapeHtml(message)}
        </div>
    `;
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
