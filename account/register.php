<?php
/**
 * Life is Character Radio - User Registration
 * Location: /account/register.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isUserLoggedIn()) header('Location: ' . BASE_URL . 'account/dashboard');

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-md mx-auto px-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h1 class="text-4xl font-cinzel font-bold text-brand-navy mb-2 text-center">Create Account</h1>
            <p class="text-gray-600 text-center mb-8">Join our community today</p>
            
            <div id="messageContainer"></div>
            
            <form id="registerForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="full_name" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone (Optional)</label>
                    <input type="tel" name="phone" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" required minlength="8" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                    <p class="text-sm text-gray-500 mt-1">Must contain: uppercase, lowercase, number, and special character</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password *</label>
                    <input type="password" name="confirm_password" required minlength="8" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                
                <button type="submit" id="registerBtn" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                Already have an account? <a href="<?= BASE_URL ?>account/login<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" class="text-cyan-600 hover:text-cyan-700 font-semibold">Sign In</a>
            </div>
        </div>
    </div>
</section>

<script>
window.API_BASE_URL = '<?= BASE_URL ?>api/user/';
</script>
<script src="<?= BASE_URL ?>js/user-api.js"></script>
<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageContainer = document.getElementById('messageContainer');
    const submitBtn = document.getElementById('registerBtn');
    const form = e.target;
    
    // Clear previous errors
    UIHelpers.clearValidationErrors();
    messageContainer.innerHTML = '';
    
    // Get form data
    const formData = {
        full_name: form.full_name.value,
        email: form.email.value,
        phone: form.phone.value,
        password: form.password.value,
        confirm_password: form.confirm_password.value
    };
    
    // Show loading
    UIHelpers.showLoading(submitBtn, 'Creating Account...');
    
    try {
        const response = await UserAPI.register(formData);
        UIHelpers.hideLoading(submitBtn);
        
        // Show success message
        UIHelpers.showSuccess(messageContainer, response.message);
        
        // Hide form on success
        form.style.display = 'none';
        
    } catch (error) {
        UIHelpers.hideLoading(submitBtn);
        
        if (error.errors) {
            UIHelpers.showValidationErrors(error.errors);
        }
        UIHelpers.showError(messageContainer, error.message || 'Registration failed. Please try again.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
