<?php
/**
 * Life is Character Radio - Forgot Password
 * Location: /account/forgot-password.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-6 w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h1 class="text-3xl font-cinzel font-bold text-brand-navy mb-2 text-center">Reset Password</h1>
            <p class="text-gray-600 text-center mb-8">Enter your email to receive a reset link</p>
            
            <div id="messageContainer"></div>
            
            <form id="forgotForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <button type="submit" id="forgotBtn" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                    Send Reset Link
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                Remember your password? <a href="<?= BASE_URL ?>account/login" class="text-cyan-600 hover:text-cyan-700 font-semibold">Sign In</a>
            </div>
        </div>
    </div>
</section>

<script>
window.API_BASE_URL = '<?= BASE_URL ?>api/user/';
</script>
<script src="<?= BASE_URL ?>js/user-api.js"></script>
<script>
document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageContainer = document.getElementById('messageContainer');
    const submitBtn = document.getElementById('forgotBtn');
    const form = e.target;
    
    UIHelpers.clearValidationErrors();
    messageContainer.innerHTML = '';
    UIHelpers.showLoading(submitBtn, 'Sending...');
    
    try {
        const response = await UserAPI.forgotPassword(form.email.value);
        UIHelpers.hideLoading(submitBtn);
        UIHelpers.showSuccess(messageContainer, response.message);
        form.reset();
    } catch (error) {
        UIHelpers.hideLoading(submitBtn);
        UIHelpers.showError(messageContainer, error.message || 'Failed to send reset link.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
