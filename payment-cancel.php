<?php
/**
 * Life is Character Radio - Payment Cancelled
 * Location: /payment-cancel.php
 */

require_once __DIR__ . '/includes/config.php';

$title = "Payment Cancelled";
include __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen bg-gradient-to-b from-[#111827] to-[#001A2E] pt-24 pb-16">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-2xl shadow-2xl p-8 text-center">
            <div class="mb-6">
                <i class="fas fa-times-circle text-gray-400 text-6xl mb-4"></i>
                <h1 class="font-['Cinzel'] text-4xl text-[#00E5FF] mb-4">Payment Cancelled</h1>
                <p class="text-white font-['Inter'] text-lg mb-6">Your payment was not completed. No charges have been made.</p>
            </div>
            
            <div class="bg-[#001A2E]/50 rounded-xl p-6 mb-6">
                <h2 class="font-['Cinzel'] text-xl text-[#00E5FF] mb-3">Would you like to try again?</h2>
                <p class="text-gray-300 font-['Inter'] text-sm">If you experienced any issues, please contact us and we'll be happy to assist you.</p>
            </div>
            
            <div class="flex gap-4 justify-center flex-wrap">
                <a href="<?= BASE_URL ?>events" class="bg-[#00E5FF] text-[#001A2E] px-8 py-3 rounded-lg font-['Inter'] font-bold hover:bg-[#0099CC] transition-colors">
                    <i class="fas fa-calendar-alt mr-2"></i>View Events
                </a>
                <a href="<?= BASE_URL ?>donate" class="bg-[#FFD700] text-[#001A2E] px-8 py-3 rounded-lg font-['Inter'] font-bold hover:bg-[#C0C0C0] transition-colors">
                    <i class="fas fa-heart mr-2"></i>Make a Donation
                </a>
                <a href="<?= BASE_URL ?>contact" class="bg-[#001A2E] text-[#00E5FF] px-8 py-3 rounded-lg font-['Inter'] font-bold border-2 border-[#00E5FF] hover:bg-[#00E5FF] hover:text-[#001A2E] transition-colors">
                    <i class="fas fa-envelope mr-2"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
