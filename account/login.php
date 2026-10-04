<?php
/**
 * Life is Character Radio - User Login
 * Location: /account/login.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isUserLoggedIn()) header('Location: ' . BASE_URL . 'account/dashboard');

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-6 w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h1 class="text-4xl font-cinzel font-bold text-brand-navy mb-2 text-center">Welcome Back</h1>
            <p class="text-gray-600 text-center mb-8">Sign in to your account</p>
            
            <div id="messageContainer"></div>
            
            <form id="loginForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <div class="flex items-center justify-between text-sm">
                    <a href="<?= BASE_URL ?>account/forgot-password" class="text-cyan-600 hover:text-cyan-700 font-semibold">Forgot Password?</a>
                </div>
                
                <button type="submit" id="loginBtn" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                    Sign In
                </button>
            </form>
            
            <div class="mt-6 text-center space-y-3">
                <p class="text-sm text-gray-600">
                    Don't have an account? <a href="<?= BASE_URL ?>account/register<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" class="text-cyan-600 hover:text-cyan-700 font-semibold">Create Account</a>
                </p>
                <p class="text-sm text-gray-600">
                    Need activation link? <a href="<?= BASE_URL ?>account/resend-activation" class="text-cyan-600 hover:text-cyan-700 font-semibold">Resend Activation</a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
window.API_BASE_URL = '<?= BASE_URL ?>api/user/';
// Get redirect parameter from URL if present
const urlParams = new URLSearchParams(window.location.search);
const redirectUrl = urlParams.get('redirect');
</script>
<script src="<?= BASE_URL ?>js/user-api.js"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageContainer = document.getElementById('messageContainer');
    const submitBtn = document.getElementById('loginBtn');
    const form = e.target;
    
    // Clear previous errors
    UIHelpers.clearValidationErrors();
    messageContainer.innerHTML = '';
    
    // Show loading
    UIHelpers.showLoading(submitBtn, 'Signing In...');
    
    try {
        const response = await UserAPI.login(form.email.value, form.password.value, false, redirectUrl);
        
        // Redirect on success
        window.location.href = response.data.redirect;
        
    } catch (error) {
        UIHelpers.hideLoading(submitBtn);
        UIHelpers.showError(messageContainer, error.message || 'Login failed. Please try again.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
