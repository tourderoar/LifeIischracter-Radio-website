<?php
/**
 * Life is Character Radio - Donation Success
 * Location: /donation-success.php
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$title = "Thank You for Your Donation";
$newAccount = isset($_GET['new_account']) && $_GET['new_account'] == '1';
include __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen bg-gradient-to-b from-[#111827] to-[#001A2E] pt-24 pb-16">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-2xl shadow-2xl p-8 text-center">
            <div class="mb-6">
                <i class="fas fa-heart text-[#FFD700] text-6xl mb-4"></i>
                <h1 class="font-['Cinzel'] text-4xl text-[#00E5FF] mb-4">Thank You for Your Generosity!</h1>
                <p class="text-white font-['Inter'] text-lg mb-6">Your donation has been received and will help us continue our mission. May God bless you abundantly!</p>
            </div>
            
            <?php if ($newAccount): ?>
            <div class="bg-[#10b981]/20 border-2 border-[#10b981] rounded-xl p-6 mb-6">
                <div class="flex items-start gap-3 text-left">
                    <i class="fas fa-user-plus text-[#10b981] text-2xl mt-1"></i>
                    <div>
                        <h3 class="font-['Cinzel'] text-xl text-[#10b981] mb-2">Account Created!</h3>
                        <p class="text-white font-['Inter'] text-sm">We've created an account for you to track your donations and access exclusive features. <strong>Check your email</strong> for an activation link to set your password and access your account.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="bg-[#001A2E]/50 rounded-xl p-6 mb-6">
                <h2 class="font-['Cinzel'] text-2xl text-[#FFD700] mb-4">Donation Details</h2>
                <p class="text-white font-['Inter'] mb-4">A receipt has been sent to your email address for tax purposes.</p>
                <p class="text-gray-300 font-['Inter'] text-sm italic">"Give, and it will be given to you. A good measure, pressed down, shaken together and running over..." - Luke 6:38</p>
            </div>
            
            <div class="flex gap-4 justify-center flex-wrap">
                <?php if (isUserLoggedIn()): ?>
                <a href="<?= BASE_URL ?>account/dashboard" class="bg-[#00E5FF] text-[#001A2E] px-8 py-3 rounded-lg font-['Inter'] font-bold hover:bg-[#0099CC] transition-colors">
                    <i class="fas fa-th-large mr-2"></i>View Dashboard
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>" class="bg-[#001A2E] text-[#00E5FF] px-8 py-3 rounded-lg font-['Inter'] font-bold border-2 border-[#00E5FF] hover:bg-[#00E5FF] hover:text-[#001A2E] transition-colors">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
