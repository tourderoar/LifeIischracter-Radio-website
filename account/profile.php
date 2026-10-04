<?php
/**
 * Life is Character Radio - User Profile
 * Location: /account/profile.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireUserAuth();

$pageTitle = 'Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="pt-28 pb-12 bg-gradient-to-b from-gray-50 to-white min-h-screen">
    <div class="max-w-4xl mx-auto px-6">
        <div class="mb-8">
            <h1 class="text-4xl font-cinzel font-bold text-brand-navy mb-2">My Profile</h1>
            <p class="text-gray-600">Manage your account information</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="text-center mb-6" id="userSidebar">
                    <div class="w-24 h-24 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full mx-auto flex items-center justify-center mb-4">
                        <span class="text-3xl font-bold text-white" id="userInitial"><?= strtoupper(substr($_SESSION['user_name'] ?? '', 0, 1)) ?></span>
                    </div>
                    <h3 class="font-cinzel font-bold text-brand-navy text-xl" id="userName"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></h3>
                    <p class="text-gray-500 text-sm" id="userEmail"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span id="joinDate">Loading...</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-green-600 font-semibold">Active Account</span>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t">
                    <a href="<?= BASE_URL ?>account/dashboard" class="block text-center text-cyan-600 hover:text-cyan-700 font-semibold mb-2">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>account/transactions" class="block text-center text-cyan-600 hover:text-cyan-700 font-semibold">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Transactions
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Profile Information -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-cinzel font-bold text-brand-navy mb-6">Profile Information</h2>
                    
                    <div id="profileMessageContainer"></div>
                    
                    <form id="profileForm" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="full_name" id="fullNameInput" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="emailInput" disabled
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                            <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" id="phoneInput" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                        </div>
                        
                        <button type="submit" id="profileBtn" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                            Update Profile
                        </button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-cinzel font-bold text-brand-navy mb-6">Change Password</h2>
                    
                    <div id="passwordMessageContainer"></div>
                    
                    <form id="passwordForm" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" required minlength="8" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                            <p class="text-sm text-gray-500 mt-1">Must contain: uppercase, lowercase, number, and special character</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="8" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                        </div>
                        
                        <button type="submit" id="passwordBtn" class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                            Change Password
                        </button>
                    </form>
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
// Load profile data
async function loadProfile() {
    try {
        const response = await UserAPI.getProfile();
        const user = response.data;
        
        // Update form fields
        document.getElementById('fullNameInput').value = user.full_name || '';
        document.getElementById('phoneInput').value = user.phone || '';
        document.getElementById('emailInput').value = user.email || '';
        
        // Update sidebar
        document.getElementById('userName').textContent = user.full_name;
        document.getElementById('userEmail').textContent = user.email;
        document.getElementById('userInitial').textContent = user.full_name.charAt(0).toUpperCase();
        document.getElementById('joinDate').textContent = 'Joined ' + UIHelpers.formatDate(user.created_at).replace(/\d{1,2},/, '');
        
    } catch (error) {
        console.error('Failed to load profile:', error);
    }
}

// Profile form submission
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageContainer = document.getElementById('profileMessageContainer');
    const submitBtn = document.getElementById('profileBtn');
    const form = e.target;
    
    // Clear previous errors
    UIHelpers.clearValidationErrors();
    messageContainer.innerHTML = '';
    
    const fullName = form.full_name.value.trim();
    const phone = form.phone.value.trim();
    
    // Show loading
    UIHelpers.showLoading(submitBtn, 'Updating...');
    
    try {
        const response = await UserAPI.updateProfile(fullName, phone);
        UIHelpers.hideLoading(submitBtn);
        
        // Show success message
        UIHelpers.showSuccess(messageContainer, response.message);
        
        // Update sidebar
        document.getElementById('userName').textContent = fullName;
        document.getElementById('userInitial').textContent = fullName.charAt(0).toUpperCase();
        
    } catch (error) {
        UIHelpers.hideLoading(submitBtn);
        
        if (error.errors) {
            UIHelpers.showValidationErrors(error.errors);
        }
        UIHelpers.showError(messageContainer, error.message || 'Failed to update profile. Please try again.');
    }
});

// Password form submission
document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageContainer = document.getElementById('passwordMessageContainer');
    const submitBtn = document.getElementById('passwordBtn');
    const form = e.target;
    
    // Clear previous errors
    UIHelpers.clearValidationErrors();
    messageContainer.innerHTML = '';
    
    const currentPassword = form.current_password.value;
    const newPassword = form.new_password.value;
    const confirmPassword = form.confirm_password.value;
    
    // Show loading
    UIHelpers.showLoading(submitBtn, 'Changing Password...');
    
    try {
        const response = await UserAPI.updatePassword(currentPassword, newPassword, confirmPassword);
        UIHelpers.hideLoading(submitBtn);
        
        // Show success message
        UIHelpers.showSuccess(messageContainer, response.message);
        
        // Clear form
        form.reset();
        
    } catch (error) {
        UIHelpers.hideLoading(submitBtn);
        
        if (error.errors) {
            UIHelpers.showValidationErrors(error.errors);
        }
        UIHelpers.showError(messageContainer, error.message || 'Failed to change password. Please try again.');
    }
});

// Load profile on page load
loadProfile();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
