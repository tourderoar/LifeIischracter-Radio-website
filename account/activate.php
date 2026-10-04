<?php
/**
 * Life is Character Radio - Account Activation
 * Location: /account/activate.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$token = $_GET['token'] ?? '';

$pageTitle = 'Activate Account';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-screen flex items-center">
    <div class="max-w-md mx-auto px-6 w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div id="activationResult">
                <svg class="animate-spin w-20 h-20 mx-auto text-cyan-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">Activating your account...</p>
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
const resultContainer = document.getElementById('activationResult');

async function activateAccount() {
    if (!token) {
        resultContainer.innerHTML = `
            <div class="mb-6">
                <svg class="w-20 h-20 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-cinzel font-bold text-brand-navy mb-4">Activation Failed</h1>
            <p class="text-gray-600 mb-8">No activation token provided.</p>
            <a href="<?= BASE_URL ?>account/resend-activation" class="inline-block bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                Resend Activation Email
            </a>
        `;
        return;
    }
    
    try {
        const response = await UserAPI.activate(token);
        
        // Show success
        resultContainer.innerHTML = `
            <div class="mb-6">
                <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-cinzel font-bold text-brand-navy mb-4">Account Activated!</h1>
            <p class="text-gray-600 mb-8">${response.message}</p>
            <a href="${response.data.redirect}" class="inline-block bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                Go to Dashboard
            </a>
        `;
        
        // Redirect after 2 seconds
        setTimeout(() => window.location.href = response.data.redirect, 2000);
        
    } catch (error) {
        resultContainer.innerHTML = `
            <div class="mb-6">
                <svg class="w-20 h-20 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-cinzel font-bold text-brand-navy mb-4">Activation Failed</h1>
            <p class="text-gray-600 mb-8">${error.message || 'Invalid or expired activation link.'}</p>
            <div class="space-y-3">
                <a href="<?= BASE_URL ?>account/resend-activation" class="block bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:shadow-lg transition duration-300 transform hover:scale-105">
                    Resend Activation Email
                </a>
                <a href="<?= BASE_URL ?>account/register" class="block text-cyan-600 hover:text-cyan-700 font-semibold py-2">
                    Create New Account
                </a>
            </div>
        `;
    }
}

// Auto-activate on page load
activateAccount();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
