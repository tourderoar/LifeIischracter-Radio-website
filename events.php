<?php
/**
 * Life is Character Radio - Events Page
 * 
 * Location: /events.php
 * 
 * Description:
 * Events listing page showing upcoming and past ministry events.
 * Features filtering by status and registration capabilities.
 */

require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Events - Join Our Ministry Gatherings';
$currentPage = 'events';
include __DIR__ . '/includes/header.php';
?>
<script src="https://js.stripe.com/v3/"></script>

<section class="pt-24 pb-20 bg-gray-800">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold font-cinematic mb-6 text-brand-cyan">Ministry Events</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Join us for transformative gatherings, conferences, and community events
            </p>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap gap-4 mb-8 justify-center items-center">
            <select id="status-filter" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan">
                <option value="">All Events</option>
                <option value="upcoming" selected>Upcoming Events</option>
                <option value="past">Past Events</option>
            </select>
            <button id="clear-filters" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition-colors">
                <i class="fas fa-redo mr-2"></i>Reset Filters
            </button>
            <div class="text-gray-300 px-4 py-2 bg-gray-700/50 rounded-lg">
                <i class="fas fa-calendar-alt mr-2"></i>
                <span id="event-count">Loading events...</span>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <i class="fas fa-spinner animate-spin text-4xl text-brand-cyan mb-4"></i>
            <p class="text-gray-400">Loading events...</p>
        </div>

        <!-- Events Grid -->
        <div id="events-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 hidden">
            <!-- Events will be loaded here via JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <i class="fas fa-calendar-times text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">No events found</h3>
            <p class="text-gray-500 mb-6">Check back soon for upcoming ministry events</p>
            <a href="<?php echo BASE_URL; ?>contact" class="inline-block bg-brand-cyan text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                Contact Us for Event Information
            </a>
        </div>
    </div>
</section>

<script>
// Events page functionality
document.addEventListener('DOMContentLoaded', () => {
    const eventsGrid = document.getElementById('events-grid');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const statusFilter = document.getElementById('status-filter');
    const clearFilters = document.getElementById('clear-filters');
    const eventCount = document.getElementById('event-count');

    // Load events function
    async function loadEvents() {
        loadingState.classList.remove('hidden');
        eventsGrid.classList.add('hidden');
        emptyState.classList.add('hidden');

        try {
            const status = statusFilter.value;
            const params = new URLSearchParams();
            if (status) params.append('status', status);

            const response = await fetch(`<?= BASE_URL ?>api/events/?${params}`);
            const data = await response.json();

            loadingState.classList.add('hidden');

            if (data.success && data.data.length > 0) {
                renderEvents(data.data);
                eventsGrid.classList.remove('hidden');
                eventCount.textContent = `${data.data.length} event${data.data.length !== 1 ? 's' : ''}`;
            } else {
                emptyState.classList.remove('hidden');
                eventCount.textContent = '0 events';
            }
        } catch (error) {
            console.error('Error loading events:', error);
            loadingState.classList.add('hidden');
            emptyState.classList.remove('hidden');
            eventCount.textContent = 'Error loading';
        }
    }

    // Render events
    function renderEvents(events) {
        eventsGrid.innerHTML = events.map(event => {
            const eventDate = new Date(event.event_date);
            const isPast = event.status === 'past';
            const isFull = event.max_capacity && event.current_registrations >= event.max_capacity;
            const isFree = parseFloat(event.cost) === 0;
            
            return `
                <div class="bg-gray-700/50 rounded-lg border border-brand-cyan/20 hover:border-brand-cyan transition-all overflow-hidden group ${isPast ? 'opacity-75' : ''}">
                    ${event.featured_image ? 
                        `<div class="relative aspect-video bg-gray-800 overflow-hidden">
                            <img src="${event.featured_image}" alt="${event.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 to-transparent"></div>
                            ${isPast ? '<div class="absolute top-3 left-3 bg-gray-600 text-white px-3 py-1 rounded text-xs font-semibold">Past Event</div>' : ''}
                            ${isFull && !isPast ? '<div class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded text-xs font-semibold">Sold Out</div>' : ''}
                        </div>` :
                        `<div class="aspect-video bg-gradient-to-br from-brand-cyan/20 to-brand-blue/20 flex items-center justify-center relative">
                            <i class="fas fa-calendar-alt text-6xl text-brand-cyan/30"></i>
                            ${isPast ? '<div class="absolute top-3 left-3 bg-gray-600 text-white px-3 py-1 rounded text-xs font-semibold">Past Event</div>' : ''}
                        </div>`
                    }
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-brand-cyan text-sm font-semibold">
                                <i class="far fa-calendar mr-1"></i>${eventDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            </span>
                            ${isFree ? 
                                '<span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-xs font-semibold">FREE</span>' :
                                `<span class="bg-brand-cyan/20 text-brand-cyan px-2 py-1 rounded text-xs font-semibold">$${parseFloat(event.cost).toFixed(2)}</span>`
                            }
                        </div>
                        
                        <h3 class="text-xl font-bold text-white mb-2 line-clamp-2 group-hover:text-brand-cyan transition-colors">${event.title}</h3>
                        
                        <p class="text-gray-400 text-sm mb-4 line-clamp-3">${event.description}</p>
                        
                        <div class="flex items-center text-gray-500 text-sm mb-4">
                            <i class="fas fa-map-marker-alt mr-2 text-brand-cyan"></i>
                            <span class="truncate">${event.location}</span>
                        </div>
                        
                        ${event.max_capacity ? 
                            `<div class="mb-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Registration</span>
                                    <span>${event.current_registrations}/${event.max_capacity}</span>
                                </div>
                                <div class="w-full bg-gray-600 rounded-full h-2">
                                    <div class="bg-brand-cyan rounded-full h-2 transition-all" style="width: ${(event.current_registrations / event.max_capacity * 100)}%"></div>
                                </div>
                            </div>` : ''
                        }
                        
                        ${!isPast ? 
                            event.user_registered ?
                                '<div class="text-center bg-green-500/20 text-green-400 border border-green-500/50 py-3 rounded-lg font-semibold"><i class="fas fa-check-circle mr-2"></i>Already Registered</div>' :
                            `<button onclick="registerForEvent(${event.id}, '${event.title}', ${event.cost}, ${isFull})" 
                                     class="w-full ${isFull ? 'bg-gray-600 cursor-not-allowed' : 'bg-brand-cyan hover:bg-brand-blue'} text-gray-900 py-3 rounded-lg font-semibold transition-colors" 
                                     ${isFull ? 'disabled' : ''}>
                                ${isFull ? '<i class="fas fa-ban mr-2"></i>Event Full' : '<i class="fas fa-ticket-alt mr-2"></i>Register Now'}
                            </button>` :
                            '<div class="text-center text-gray-500 py-3"><i class="fas fa-check-circle mr-2"></i>Event Completed</div>'
                        }
                    </div>
                </div>
            `;
        }).join('');
    }

    // Register for event function with Stripe payment
    window.registerForEvent = async function(eventId, title, cost, isFull) {
        if (isFull) return;
        
        // Check if user is logged in
        if (typeof USER_LOGGED_IN === 'undefined' || !USER_LOGGED_IN) {
            // Redirect to login with return URL
            const currentUrl = encodeURIComponent(window.location.href);
            window.location.href = '<?= BASE_URL ?>account/login?redirect=' + currentUrl;
            return;
        }
        
        // Show confirmation prompt for logged-in users
        if (!confirm(`Register for ${title}?`)) {
            return;
        }
        
        const isFree = parseFloat(cost) === 0;
        
        try {
            // For free events, register directly
            if (isFree) {
                const response = await fetch('<?= BASE_URL ?>api/events/register', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({event_id: eventId})
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to register for event');
                }
                
                showNotification('Successfully registered for free event!');
                loadEvents(); // Reload events to update capacity
                return;
            }
            
            // For paid events, show payment modal
            showPaymentModal(eventId, title, cost);
            
        } catch (error) {
            showNotification(error.message, 'error');
        }
    };
    
    // Show payment modal for paid events
    function showPaymentModal(eventId, title, cost) {
        const modal = document.createElement('div');
        modal.id = 'payment-modal';
        modal.className = 'fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-gray-800 rounded-xl border-2 border-brand-cyan max-w-md w-full p-8 relative">
                <button onclick="closePaymentModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                
                <div class="text-center mb-6">
                    <div class="bg-brand-cyan/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-ticket-alt text-3xl text-brand-cyan"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">${title}</h2>
                    <p class="text-3xl font-bold text-brand-cyan">$${parseFloat(cost).toFixed(2)}</p>
                </div>
                
                <form id="event-payment-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Payment Information</label>
                        <div id="event-card-element" class="w-full p-4 bg-gray-700 border border-gray-600 rounded-lg min-h-[50px]"></div>
                        <div id="event-card-errors" class="text-red-400 text-sm mt-2"></div>
                    </div>
                    
                    <button type="submit" id="event-pay-button" class="w-full bg-brand-cyan text-gray-900 py-3 rounded-lg font-bold hover:bg-brand-blue transition-colors disabled:opacity-50">
                        <span id="event-button-text">
                            <i class="fas fa-lock mr-2"></i>Pay & Register
                        </span>
                    </button>
                </form>
                
                <p class="text-gray-400 text-xs text-center mt-4">
                    <i class="fas fa-shield-alt mr-1"></i>Secure payment processing by Stripe
                </p>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Initialize Stripe Elements for this modal
        if (typeof Stripe !== 'undefined' && typeof STRIPE_PUBLISHABLE_KEY !== 'undefined') {
            const stripe = Stripe(STRIPE_PUBLISHABLE_KEY);
            const elements = stripe.elements();
            
            const cardElement = elements.create('card', {
                style: {
                    base: {
                        color: '#fff',
                        fontFamily: 'Inter, system-ui, sans-serif',
                        fontSize: '16px',
                        '::placeholder': {color: '#9CA3AF'}
                    },
                    invalid: {color: '#EF4444', iconColor: '#EF4444'}
                }
            });
            
            cardElement.mount('#event-card-element');
            
            cardElement.on('change', ({error}) => {
                const displayError = document.getElementById('event-card-errors');
                displayError.textContent = error ? error.message : '';
            });
            
            // Handle form submission
            document.getElementById('event-payment-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitButton = document.getElementById('event-pay-button');
                const buttonText = document.getElementById('event-button-text');
                
                submitButton.disabled = true;
                buttonText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
                
                try {
                    // Step 1: Validate card details BEFORE creating registration
                    const cardValidation = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement,
                    }).catch(() => null);
                    
                    if (!cardValidation || cardValidation.error) {
                        const errorMsg = cardValidation?.error?.message || 'Please enter valid card details';
                        throw new Error(errorMsg);
                    }
                    
                    // Step 2: Create payment intent (now that we know card is valid)
                    const response = await fetch('<?= BASE_URL ?>api/events/register', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({event_id: eventId})
                    });
                    
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to register');
                    }
                    
                    // Step 3: Confirm payment with Stripe
                    const {error: stripeError} = await stripe.confirmCardPayment(result.data.client_secret, {
                        payment_method: {card: cardElement}
                    });
                    
                    if (stripeError) {
                        throw new Error(stripeError.message);
                    }
                    
                    // Success - redirect
                    window.location.href = '<?= BASE_URL ?>event-success';
                    
                } catch (error) {
                    showNotification(error.message, 'error');
                    submitButton.disabled = false;
                    buttonText.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay & Register';
                }
            });
        } else {
            document.getElementById('event-card-element').innerHTML = 
                '<div class="text-red-400 text-sm"><i class="fas fa-exclamation-triangle mr-2"></i>Payment system not available</div>';
        }
    }
    
    window.closePaymentModal = function() {
        const modal = document.getElementById('payment-modal');
        if (modal) modal.remove();
    };
    
    function showNotification(message, type = 'success') {
        const colors = {success: 'bg-[#00E5FF] text-[#001A2E]', error: 'bg-red-600 text-white'};
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const notif = document.createElement('div');
        notif.className = `fixed top-24 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl max-w-sm ${colors[type]}`;
        notif.innerHTML = `<div class="flex items-center font-['Inter']"><i class="fas ${icon} mr-3 text-xl"></i><p class="font-medium">${message}</p></div>`;
        
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 5000);
    }

    // Filter events
    statusFilter.addEventListener('change', loadEvents);
    clearFilters.addEventListener('click', () => {
        statusFilter.value = 'upcoming';
        loadEvents();
    });

    // Initial load
    loadEvents();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
