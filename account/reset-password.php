<?php
/**
 * Life is Character Radio - Reset Password
 * Location: /account/reset-password.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$token = $_GET['token'] ?? '';

$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-6 w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h1 class="text-3xl font-cinzel font-bold text-brand-navy mb-2 text-center">Create New Password</h1>
            <p class="text-gray-600 text-center mb-8">Enter your new password below</p>
            
            <div id="messageContainer"></div>
            
            <form id="resetForm" class="space-y-5">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input type="password" name="password" required minlength="8" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                    <p class="text-sm text-gray-500 mt-1">Must contain: uppercase, lowercase, number, and special character</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="8" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <button type="submit" id="resetBtn" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                    Reset Password
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                <a href="<?= BASE_URL ?>account/forgot-password" class="text-cyan-600 hover:text-cyan-700 font-semibold">Request New Link</a>
            </div>
        </div>
    </div>
</section>

<script>
window.API_BASE_URL = '<?= BASE_URL ?>api/user/';
</script>
<script src="<?= BASE_URL ?>js/user-api.js"></script>
<script>
const token = '<?= htmlspecialchars($token) ?>';

if (!token) {
    document.getElementById('messageContainer').innerHTML = `
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>No reset token provided.</span>
            </div>
        </div>
    `;
    document.getElementById('resetForm').style.display = 'none';
}

document.getElementById('resetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageContainer = document.getElementById('messageContainer');
    const submitBtn = document.getElementById('resetBtn');
    const form = e.target;
    
    UIHelpers.clearValidationErrors();
    messageContainer.innerHTML = '';
    UIHelpers.showLoading(submitBtn, 'Resetting Password...');
    
    try {
        const response = await UserAPI.resetPassword(
            token,
            form.password.value,
            form.confirm_password.value
        );
        
        // Show success message
        UIHelpers.showSuccess(messageContainer, response.message);
        form.style.display = 'none';
        
        // Redirect after 2 seconds
        setTimeout(() => window.location.href = response.data.redirect, 2000);
        
    } catch (error) {
        UIHelpers.hideLoading(submitBtn);
        
        if (error.errors) {
            UIHelpers.showValidationErrors(error.errors);
        }
        UIHelpers.showError(messageContainer, error.message || 'Password reset failed.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
