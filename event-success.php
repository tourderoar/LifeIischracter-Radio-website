<?php
/**
 * Life is Character Radio - Event Registration Success
 * Location: /event-success.php
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

requireUserAuth();

$title = "Registration Successful";
include __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen bg-gradient-to-b from-[#111827] to-[#001A2E] pt-24 pb-16">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-2xl shadow-2xl p-8 text-center">
            <div class="mb-6">
                <i class="fas fa-check-circle text-[#00E5FF] text-6xl mb-4"></i>
                <h1 class="font-['Cinzel'] text-4xl text-[#00E5FF] mb-4">Registration Successful!</h1>
                <p class="text-white font-['Inter'] text-lg mb-6">Thank you for registering! You will receive a confirmation email shortly.</p>
            </div>
            
            <div class="bg-[#001A2E]/50 rounded-xl p-6 mb-6">
                <h2 class="font-['Cinzel'] text-2xl text-[#FFD700] mb-4">What's Next?</h2>
                <ul class="text-left text-white font-['Inter'] space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-[#00E5FF] mr-3 mt-1"></i>
                        <span>Check your email for registration confirmation and event details</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-calendar text-[#00E5FF] mr-3 mt-1"></i>
                        <span>Add the event to your calendar</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user text-[#00E5FF] mr-3 mt-1"></i>
                        <span>View your registration in your dashboard</span>
                    </li>
                </ul>
            </div>
            
            <div class="flex gap-4 justify-center">
                <a href="<?= BASE_URL ?>account/dashboard" class="bg-[#00E5FF] text-[#001A2E] px-8 py-3 rounded-lg font-['Inter'] font-bold hover:bg-[#0099CC] transition-colors">
                    <i class="fas fa-th-large mr-2"></i>Go to Dashboard
                </a>
                <a href="<?= BASE_URL ?>events" class="bg-[#001A2E] text-[#00E5FF] px-8 py-3 rounded-lg font-['Inter'] font-bold border-2 border-[#00E5FF] hover:bg-[#00E5FF] hover:text-[#001A2E] transition-colors">
                    <i class="fas fa-calendar-alt mr-2"></i>View More Events
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
