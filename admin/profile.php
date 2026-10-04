<?php
/**
 * Life is Character Radio - Admin Profile
 * 
 * Location: /admin/profile.php
 * 
 * View and edit admin profile, change password
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminAuth();

$pageTitle = 'My Profile';
$csrf_token = generateCsrfToken();

include __DIR__ . '/includes/admin_header.php';
?>

<div id="alert-container"></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Info Card -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-center" id="profile-card">
            <div class="w-24 h-24 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-spinner fa-spin text-white text-3xl"></i>
            </div>
            <p class="text-gray-500">Loading profile...</p>
        </div>
    </div>

    <!-- Update Profile Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Update Profile</h3>
            <form id="profile-form" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="full_name">
                        <i class="fas fa-user mr-2 text-cyan-600"></i>Full Name
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                    >
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="email">
                        <i class="fas fa-envelope mr-2 text-cyan-600"></i>Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                    >
                </div>

                <button 
                    type="submit"
                    class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-6 py-3 rounded-lg hover:shadow-lg transition"
                >
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Change Password</h3>
            <form id="password-form" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="current_password">
                        <i class="fas fa-lock mr-2 text-cyan-600"></i>Current Password
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                    >
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="new_password">
                        <i class="fas fa-key mr-2 text-cyan-600"></i>New Password
                    </label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        Minimum <?= PASSWORD_MIN_LENGTH ?> characters, with uppercase, lowercase, number and special character
                    </p>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="confirm_password">
                        <i class="fas fa-check-double mr-2 text-cyan-600"></i>Confirm New Password
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                    >
                </div>

                <button 
                    type="submit"
                    class="bg-gradient-to-r from-yellow-500 to-orange-600 text-white font-semibold px-6 py-3 rounded-lg hover:shadow-lg transition"
                >
                    <i class="fas fa-shield-alt mr-2"></i>Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';

// Load profile data
fetch(BASE_URL + 'api/admin/profile')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderProfile(data.data.admin);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Profile load error:', err);
        showAlert('Failed to load profile', 'error');
    });

function renderProfile(admin) {
    document.getElementById('profile-card').innerHTML = `
        <div class="w-24 h-24 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center">
            <span class="text-white text-3xl font-bold">${admin.full_name.charAt(0).toUpperCase()}</span>
        </div>
        <h3 class="text-xl font-semibold text-gray-800">${escapeHtml(admin.full_name)}</h3>
        <p class="text-cyan-600 text-sm mt-1">${escapeHtml(admin.role_name)}</p>
        <p class="text-gray-600 text-sm mt-2">${escapeHtml(admin.email)}</p>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                <p class="mb-2"><i class="fas fa-calendar mr-2 text-cyan-600"></i>Joined: ${formatDate(admin.created_at)}</p>
                <p><i class="fas fa-clock mr-2 text-cyan-600"></i>Last Update: ${formatDate(admin.updated_at)}</p>
            </div>
        </div>
    `;
    
    document.getElementById('full_name').value = admin.full_name;
    document.getElementById('email').value = admin.email;
}

// Handle profile update
document.getElementById('profile-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const res = await fetch(BASE_URL + 'api/admin/profile', {
            method: 'POST',
            body: formData
        });
        
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Profile update error:', err);
        showAlert('Failed to update profile', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';
    }
});

// Handle password change
document.getElementById('password-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Changing...';
    
    try {
        const res = await fetch(BASE_URL + 'api/admin/profile', {
            method: 'POST',
            body: formData
        });
        
        const data = await res.json();
        
        if (data.success) {
            showAlert(data.data.message, 'success');
            e.target.reset();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (err) {
        console.error('Password change error:', err);
        showAlert('Failed to change password', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-shield-alt mr-2"></i>Change Password';
    }
});

function showAlert(message, type) {
    const bgColor = type === 'success' ? 'bg-green-500/10 border-green-500 text-green-400' : 'bg-red-500/10 border-red-500 text-red-400';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `${bgColor} border px-6 py-4 rounded-lg mb-6 alert-${type}`;
    alert.innerHTML = `<i class="fas ${icon} mr-2"></i>${escapeHtml(message)}`;
    
    const container = document.getElementById('alert-container');
    container.innerHTML = '';
    container.appendChild(alert);
    
    if (type === 'success') {
        setTimeout(() => alert.remove(), 5000);
    }
    
    alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
