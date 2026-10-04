<?php
/**
 * Life is Character Radio - Donate Page
 * 
 * Location: /donate.php
 * 
 * Description:
 * Donation page with Stripe integration, multiple giving tiers, and recurring donation options.
 * Showcases ministry impact and testimonials to encourage giving.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Donate - Support Our Ministry';
$currentPage = 'donate';

include __DIR__ . '/includes/header.php';
?>
<script src="https://js.stripe.com/v3/"></script>
<?php

// Get user info from session for autofill (set at login)
$userFullName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
?>

<section class="pt-24 pb-20 bg-gray-800">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold font-cinematic mb-6 text-brand-cyan">Support Our Ministry</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto mb-8">
                Help us reach more lives with the transformative message of character-driven victory through God's Word
            </p>
            <div class="inline-flex items-center gap-2 bg-brand-cyan/10 border border-brand-cyan/30 px-6 py-3 rounded-lg">
                <i class="fas fa-shield-alt text-brand-cyan"></i>
                <span class="text-gray-300">Secure donation powered by Stripe</span>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-12 items-start">
            <!-- Donation Form -->
            <div class="bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20 sticky top-24">
                <h2 class="text-3xl font-bold text-brand-cyan mb-6">Make a Donation</h2>
                
                <form id="donation-form" class="space-y-6">
                    <!-- Anonymous Donation Checkbox (Only for guests) -->
                    <?php if (!$isLoggedIn): ?>
                    <div class="bg-gray-600/30 border border-gray-600 rounded-lg p-4">
                        <div class="flex items-start">
                            <input type="checkbox" name="anonymous" id="anonymous-donation" class="mt-1 mr-3 w-5 h-5 text-brand-cyan bg-gray-700 border-gray-600 rounded focus:ring-brand-cyan">
                            <div>
                                <label for="anonymous-donation" class="text-white font-semibold cursor-pointer">Make this donation anonymous</label>
                                <p class="text-gray-400 text-xs mt-1">Your donation will be private. No account will be created.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Amount: the presets and the free-entry field are one control group,
                         so the tiers read as shortcuts rather than the only options. -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-300 mb-4 uppercase tracking-wide">Choose an amount</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <button type="button" data-amount="25" onclick="selectDonationAmount(25)" class="donation-tier border-2 border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-2xl font-bold text-brand-cyan">$25</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Supporter</div>
                            </button>
                            <button type="button" data-amount="50" onclick="selectDonationAmount(50)" class="donation-tier border-2 border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-2xl font-bold text-brand-cyan">$50</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Advocate</div>
                            </button>
                            <button type="button" data-amount="100" onclick="selectDonationAmount(100)" class="donation-tier border-2 border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-2xl font-bold text-brand-cyan">$100</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Champion</div>
                            </button>
                            <button type="button" data-amount="250" onclick="selectDonationAmount(250)" class="donation-tier border-2 border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-2xl font-bold text-brand-cyan">$250</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Victory Partner</div>
                            </button>
                            <button type="button" data-amount="500" onclick="selectDonationAmount(500)" class="donation-tier border-2 border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-2xl font-bold text-brand-cyan">$500</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Character Builder</div>
                            </button>
                            <button type="button" data-amount="1000" onclick="selectDonationAmount(1000)" class="donation-tier border-2 border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-2xl font-bold text-brand-cyan">$1000</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Legacy Maker</div>
                            </button>
                            <button type="button" id="other-amount-tier" onclick="chooseOtherAmount()" class="donation-tier col-span-2 md:col-span-3 border-2 border-dashed border-gray-600 hover:border-brand-cyan text-center p-4 rounded-lg transition-all group">
                                <div class="text-lg font-semibold text-brand-cyan">Other amount</div>
                                <div class="text-xs text-gray-400 group-hover:text-gray-300">Give any amount you choose</div>
                            </button>
                        </div>

                        <div class="mt-4">
                            <label for="donation-amount" class="block text-sm font-medium text-gray-300 mb-2">Or enter any amount *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-gray-400 text-lg">$</span>
                                <input type="number" id="donation-amount" name="amount" min="1" step="0.01" required
                                       class="w-full pl-8 pr-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-lg focus:outline-none focus:border-brand-cyan transition-colors"
                                       placeholder="Enter any amount">
                            </div>
                            <p id="amount-echo" class="text-gray-400 text-xs mt-2">Any amount helps.</p>
                        </div>
                    </div>

                    <!-- Hidden frequency field - only one-time for now -->
                    <input type="hidden" name="donationType" id="donation-type" value="one-time">

                    <div class="grid md:grid-cols-2 gap-4">
                        <div id="donor-name-section">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Full Name *</label>
                            <input type="text" name="donorName" id="donor-name" required
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                   placeholder="Your full name" value="<?= htmlspecialchars($userFullName ?? '') ?>" <?= $isLoggedIn ? 'readonly' : '' ?>>
                            <p class="text-gray-400 text-xs mt-1"><?= $isLoggedIn ? 'From your account' : 'An account will be created for you to track your donations' ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email Address *</label>
                            <input type="email" name="donorEmail" id="donor-email" required
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                   placeholder="your@email.com" value="<?= htmlspecialchars($userEmail ?? '') ?>" <?= $isLoggedIn ? 'readonly' : '' ?>>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Message (Optional)</label>
                        <textarea name="message" rows="3"
                                  class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                  placeholder="Share your heart for the ministry..."></textarea>
                    </div>

                    <!-- Stripe Elements Card Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Payment Information *</label>
                        <div id="card-element" class="w-full p-4 bg-gray-700 border border-gray-600 rounded-lg min-h-[50px]">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                        <div id="card-errors" role="alert" class="text-red-400 text-sm mt-2"></div>
                    </div>

                    <div id="donation-message" class="hidden p-4 rounded-lg"></div>

                    <button type="submit" id="donate-button" class="w-full bg-brand-cyan text-gray-900 py-4 rounded-lg font-bold text-lg hover:bg-brand-blue transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="button-text">
                            <i class="fas fa-heart mr-2"></i>Donate Now
                        </span>
                    </button>
                </form>

                <p class="text-gray-400 text-sm text-center mt-4">
                    <i class="fas fa-lock mr-1"></i>Secure donation processing • Your information is protected
                </p>
            </div>

            <!-- Impact Stories & Information -->
            <div class="space-y-8">
                <div class="bg-gray-700/30 p-8 rounded-lg border border-brand-cyan/20">
                    <h3 class="text-2xl font-bold text-brand-cyan mb-6">Your Impact</h3>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-full flex-shrink-0">
                                <i class="fas fa-microphone text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-white mb-2">Radio & TV Broadcasting</h4>
                                <p class="text-gray-300">Your support helps us reach more listeners every week.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-full flex-shrink-0">
                                <i class="fas fa-book-open text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-white mb-2">Content Creation</h4>
                                <p class="text-gray-300">Fund the production of quality biblical content, devotionals, and character-building resources.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-full flex-shrink-0">
                                <i class="fas fa-hands-helping text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-white mb-2">Ministry Outreach</h4>
                                <p class="text-gray-300">Support community outreach programs and events that build character and transform lives.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-full flex-shrink-0">
                                <i class="fas fa-globe text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-white mb-2">Digital Ministry</h4>
                                <p class="text-gray-300">Expand our online presence to reach more souls globally through podcasts, social media, and streaming platforms.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Deductible Info -->
                <div class="bg-gray-700/30 p-6 rounded-lg border border-brand-cyan/20">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-brand-cyan text-xl mt-1"></i>
                        <div>
                            <h4 class="font-semibold text-white mb-2">Tax-Deductible Donation</h4>
                            <p class="text-gray-300 text-sm"><?= TAX_DEDUCTIBLE_NOTICE ?></p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="text-center">
                    <h3 class="text-xl font-bold text-brand-cyan mb-4">Questions About Giving?</h3>
                    <p class="text-gray-300 mb-4">We're here to help you support the ministry in the way that works best for you.</p>
                    <a href="<?php echo BASE_URL; ?>contact" class="inline-block bg-gray-700 text-brand-cyan px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Donation page functionality
let stripeInstance, stripeElements, cardElement;

// Amount selection ------------------------------------------------------
// The presets and the free-entry field are two views of one value, so the
// highlight always follows the amount in the field rather than the last
// button clicked. Typing an amount that isn't a preset selects "Other".

function clearTierHighlight() {
    document.querySelectorAll('.donation-tier').forEach(btn => {
        btn.classList.remove('border-brand-cyan', 'bg-brand-cyan/10');
        btn.classList.add('border-gray-600');
    });
}

function highlightTier(btn) {
    clearTierHighlight();
    if (!btn) return;
    btn.classList.remove('border-gray-600');
    btn.classList.add('border-brand-cyan', 'bg-brand-cyan/10');
}

function updateAmountEcho() {
    const echo = document.getElementById('amount-echo');
    if (!echo) return;
    const value = parseFloat(document.getElementById('donation-amount').value);
    echo.textContent = value > 0
        ? "You're giving $" + value.toFixed(2) + " — thank you."
        : 'Any amount helps.';
}

// Point the highlight at whichever tile matches the current amount
function syncTierToAmount() {
    const value = parseFloat(document.getElementById('donation-amount').value);
    let matched = null;

    document.querySelectorAll('.donation-tier[data-amount]').forEach(btn => {
        if (parseFloat(btn.dataset.amount) === value) matched = btn;
    });

    highlightTier(matched || (value > 0 ? document.getElementById('other-amount-tier') : null));
    updateAmountEcho();
}

function selectDonationAmount(amount) {
    document.getElementById('donation-amount').value = amount;
    syncTierToAmount();
}

function chooseOtherAmount() {
    const input = document.getElementById('donation-amount');
    input.value = '';
    highlightTier(document.getElementById('other-amount-tier'));
    updateAmountEcho();
    input.focus();
}

document.addEventListener('DOMContentLoaded', () => {
    // Typing in the amount field re-points the highlight, so a stale preset
    // can never sit selected next to a different number.
    const amountInput = document.getElementById('donation-amount');
    if (amountInput) {
        amountInput.addEventListener('input', syncTierToAmount);
    }

    // Handle anonymous donation checkbox (only for guests)
    const anonymousCheckbox = document.getElementById('anonymous-donation');
    const donorNameSection = document.getElementById('donor-name-section');
    const donorNameInput = document.getElementById('donor-name');
    
    if (anonymousCheckbox) {
        anonymousCheckbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                // Hide only fullname field, keep email visible
                donorNameSection.style.display = 'none';
                donorNameInput.removeAttribute('required');
                donorNameInput.value = '';
            } else {
                // Show fullname field
                donorNameSection.style.display = 'block';
                donorNameInput.setAttribute('required', 'required');
            }
        });
    }
    
    //Initialize Stripe Elements
    if (typeof Stripe !== 'undefined' && typeof STRIPE_PUBLISHABLE_KEY !== 'undefined') {
        stripeInstance = Stripe(STRIPE_PUBLISHABLE_KEY);
        stripeElements = stripeInstance.elements();
        
        // Create card element with custom styling
        cardElement = stripeElements.create('card', {
            style: {
                base: {
                    color: '#fff',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#9CA3AF'
                    }
                },
                invalid: {
                    color: '#EF4444',
                    iconColor: '#EF4444'
                }
            }
        });
        
        cardElement.mount('#card-element');
        
        // Handle card errors
        cardElement.on('change', ({error}) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });
    } else {
        document.getElementById('card-element').innerHTML = 
            '<div class="text-red-400 text-sm"><i class="fas fa-exclamation-triangle mr-2"></i>Payment system not available. Please check configuration.</div>';
    }
    
    // Form submission handler
    const form = document.getElementById('donation-form');
    const submitButton = document.getElementById('donate-button');
    const buttonText = document.getElementById('button-text');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const amount = parseFloat(document.getElementById('donation-amount').value);
        if (!amount || amount < 1) {
            showNotification('Please enter a donation amount of at least $1', 'error');
            return;
        }
        
        if (!cardElement) {
            showNotification('Payment system not initialized', 'error');
            return;
        }
        
        // Disable submit button
        submitButton.disabled = true;
        buttonText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        try {
            // Step 1: Validate card details BEFORE creating donation record
            const cardValidation = await stripeInstance.createPaymentMethod({
                type: 'card',
                card: cardElement,
            }).catch(() => null);
            
            if (!cardValidation || cardValidation.error) {
                const errorMsg = cardValidation?.error?.message || 'Please enter valid card details';
                throw new Error(errorMsg);
            }
            
            // Step 2: Create payment intent via API (now that we know card is valid)
            const anonymousCheckbox = document.getElementById('anonymous-donation');
            const donationData = {
                amount: amount,
                donation_type_id: null,
                donation_frequency: document.querySelector('[name="donationType"]').value,
                is_anonymous: anonymousCheckbox ? (anonymousCheckbox.checked ? 1 : 0) : 0,
                donor_name: document.getElementById('donor-name').value || null,
                donor_email: document.getElementById('donor-email').value || null,
                message: document.querySelector('[name="message"]').value || null
            };
            
            const response = await fetch('<?= BASE_URL ?>api/donations/create', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(donationData)
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to create donation');
            }
            
            // Step 3: Confirm payment with Stripe
            const {error: stripeError} = await stripeInstance.confirmCardPayment(result.data.client_secret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: donationData.donor_name || 'Anonymous',
                        email: donationData.donor_email || null
                    }
                }
            });
            
            if (stripeError) {
                throw new Error(stripeError.message);
            }
            
            // Success - redirect to success page with account creation flag
            const redirectUrl = result.data.account_created 
                ? '<?= BASE_URL ?>donation-success?new_account=1'
                : '<?= BASE_URL ?>donation-success';
            window.location.href = redirectUrl;
            
        } catch (error) {
            showNotification(error.message, 'error');
            submitButton.disabled = false;
            buttonText.innerHTML = '<i class="fas fa-heart mr-2"></i>Donate Now';
        }
    });
});

function showNotification(message, type = 'success') {
    const colors = {success: 'bg-[#00E5FF] text-[#001A2E]', error: 'bg-red-600 text-white'};
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notif = document.createElement('div');
    notif.className = `fixed top-24 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl max-w-sm transition-all duration-300 ${colors[type]}`;
    notif.innerHTML = `<div class="flex items-center font-['Inter']"><i class="fas ${icon} mr-3 text-xl"></i><p class="font-medium">${message}</p></div>`;
    
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 5000);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
