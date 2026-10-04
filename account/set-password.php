<?php
/**
 * Life is Character Radio - Set Password & Activate Account
 * Location: /account/set-password.php
 * For guest donors to set their password during activation
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$token = $_GET['token'] ?? '';

$pageTitle = 'Set Your Password';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-6 w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-cinzel font-bold text-brand-navy mb-2">Set Your Password</h1>
                <p class="text-gray-600">Welcome! Please create a password to activate your account.</p>
            </div>

            <!-- Form -->
            <form id="setPasswordForm" class="space-y-6">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        New Password
                    </label>
                    <input type="password" id="password" name="password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition"
                        placeholder="Create a strong password">
                    <ul class="mt-2 text-xs text-gray-600 space-y-1">
                        <li>• At least 8 characters</li>
                        <li>• At least one uppercase letter</li>
                        <li>• At least one lowercase letter</li>
                        <li>• At least one number</li>
                        <li>• At least one special character (!@#$%^&*)</li>
                    </ul>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition"
                        placeholder="Re-enter your password">
                </div>

                <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"></div>
                <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm"></div>

                <button type="submit" id="submitBtn"
                    class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                    Activate Account & Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Already activated? 
                    <a href="<?= BASE_URL ?>account/login" class="text-cyan-600 hover:text-cyan-700 font-semibold">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
const form = document.getElementById('setPasswordForm');
const errorDiv = document.getElementById('errorMessage');
const successDiv = document.getElementById('successMessage');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const token = document.querySelector('input[name="token"]').value;
    
    // Hide messages
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    // Validate
    if (!token) {
        errorDiv.textContent = 'Invalid activation link. Please use the link from your email.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (password.length < 8) {
        errorDiv.textContent = 'Password must be at least 8 characters long.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Validate password strength
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
    
    if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
        errorDiv.textContent = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (password !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Submit
    submitBtn.disabled = true;
    submitBtn.textContent = 'Activating...';
    
    try {
        const response = await fetch('<?= BASE_URL ?>api/user/activate-with-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            successDiv.textContent = data.message;
            successDiv.classList.remove('hidden');
            form.reset();
            
            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = data.data.redirect || '<?= BASE_URL ?>account/dashboard';
            }, 1500);
        } else {
            errorDiv.textContent = data.message || 'Activation failed. Please try again.';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Activate Account & Sign In';
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Activate Account & Sign In';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
