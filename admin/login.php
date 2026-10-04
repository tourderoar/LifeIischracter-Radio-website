<?php
/**
 * Life is Character Radio - Admin Login
 * 
 * Location: /admin/login.php
 * 
 * Admin authentication form with AJAX/API submission
 */

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . 'admin');
    exit;
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Life is Character Radio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Cinzel', serif; }
        .gradient-bg { background: linear-gradient(135deg, #001A2E 0%, #006B8F 100%); }
        .glow { box-shadow: 0 0 30px rgba(0, 229, 255, 0.3); }
        .btn-primary { background: linear-gradient(135deg, #00E5FF 0%, #0099CC 100%); transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0, 229, 255, 0.4); }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-2xl shadow-2xl glow p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mb-2">
                Life is Character
            </h1>
            <p class="text-gray-400">Admin Portal</p>
        </div>

        <div id="error-message" class="hidden bg-red-500/10 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><span id="error-text"></span>
        </div>

        <form id="login-form" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div>
                <label class="block text-gray-300 font-medium mb-2" for="email">
                    <i class="fas fa-envelope mr-2 text-cyan-400"></i>Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition"
                    placeholder="admin@lifeischaracter.com"
                >
            </div>

            <div>
                <label class="block text-gray-300 font-medium mb-2" for="password">
                    <i class="fas fa-lock mr-2 text-cyan-400"></i>Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition"
                    placeholder="••••••••"
                >
            </div>

            <button 
                type="submit"
                id="login-btn"
                class="w-full btn-primary text-gray-900 font-semibold py-3 rounded-lg flex items-center justify-center"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>

        <div class="mt-6 text-center text-gray-500 text-sm">
            <i class="fas fa-shield-halved mr-1"></i>Secure Admin Access
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const loginForm = document.getElementById('login-form');
        const loginBtn = document.getElementById('login-btn');
        const errorMessage = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Disable button and show loading
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';
            errorMessage.classList.add('hidden');
            
            try {
                const formData = new FormData(loginForm);
                const response = await fetch(BASE_URL + 'api/admin/login', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Success - redirect to admin dashboard
                    loginBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Success! Redirecting...';
                    window.location.href = data.data.redirect;
                } else {
                    // Error - show message
                    showError(data.message);
                }
            } catch (err) {
                console.error('Login error:', err);
                showError('An error occurred. Please try again.');
            } finally {
                if (loginBtn.disabled) {
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Sign In';
                }
            }
        });

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
            errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>
