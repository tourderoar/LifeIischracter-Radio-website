// Life is Character Radio - Frontend JavaScript

// Global state
let currentEpisodes = [];
let currentBlogPosts = [];
let currentPage = window.location.pathname;

// Page-specific functionality
const pageHandlers = {
    '/': handleHomePage,
    '/about': handleAboutPage,
    '/episodes': handleEpisodesPage,
    '/blog': handleBlogPage,
    '/donate': handleDonatePage,
    '/contact': handleContactPage
};

// Utility functions
function navigateToPage(url) {
    window.location.href = url;
}

// Remove old scrollToSection function - no longer needed for multi-page

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function formatDuration(seconds) {
    if (!seconds) return '';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

function truncateText(text, maxLength = 150) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform translate-x-full transition-transform duration-300 ${
        type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <p>${message}</p>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Animate out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Modal functions
function openContactModal() {
    document.getElementById('contact-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openPrayerModal() {
    document.getElementById('prayer-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openDonateModal() {
    document.getElementById('donate-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function setDonationAmount(amount) {
    const amountInput = document.querySelector('#donate-form input[name="amount"]');
    amountInput.value = amount;
    
    // Update button styles
    document.querySelectorAll('.donation-amount-btn').forEach(btn => {
        btn.classList.remove('border-brand-cyan', 'text-brand-cyan', 'bg-brand-cyan/10');
        btn.classList.add('border-gray-600', 'text-gray-300');
    });
    
    event.target.classList.remove('border-gray-600', 'text-gray-300');
    event.target.classList.add('border-brand-cyan', 'text-brand-cyan', 'bg-brand-cyan/10');
}

// API functions
async function loadEpisodes() {
    try {
        const response = await axios.get('/api/episodes?limit=6');
        currentEpisodes = response.data.episodes;
        renderEpisodes();
    } catch (error) {
        console.error('Error loading episodes:', error);
        showNotification('Failed to load episodes', 'error');
    }
}

async function loadBlogPosts() {
    try {
        const response = await axios.get('/api/blog?limit=6');
        currentBlogPosts = response.data.posts;
        renderBlogPosts();
    } catch (error) {
        console.error('Error loading blog posts:', error);
        showNotification('Failed to load blog posts', 'error');
    }
}

// Render functions
function renderEpisodes() {
    const episodesGrid = document.getElementById('episodes-grid');
    if (!episodesGrid) return;

    if (currentEpisodes.length === 0) {
        episodesGrid.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i class="fas fa-microphone text-4xl text-gray-600 mb-4"></i>
                <p class="text-gray-400">No episodes available yet. Check back soon!</p>
            </div>
        `;
        return;
    }

    episodesGrid.innerHTML = currentEpisodes.map(episode => `
        <div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700 hover:border-brand-cyan/50 transition-colors group">
            <div class="relative">
                <img src="${episode.thumbnail_url || '/static/images/episode-placeholder.jpg'}" 
                     alt="${episode.title}" 
                     class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-black/40 group-hover:bg-black/60 transition-colors flex items-center justify-center">
                    ${episode.youtube_url || episode.video_url ? `
                        <a href="${episode.youtube_url || episode.video_url}" target="_blank" class="bg-brand-cyan text-gray-900 p-4 rounded-full hover:bg-brand-blue transition-colors transform hover:scale-110">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    ` : `
                        <button class="bg-brand-cyan text-gray-900 p-4 rounded-full hover:bg-brand-blue transition-colors transform hover:scale-110">
                            <i class="fas fa-play text-xl"></i>
                        </button>
                    `}
                </div>
                ${episode.duration ? `
                    <div class="absolute bottom-2 right-2 bg-black/80 text-white px-2 py-1 rounded text-sm">
                        ${formatDuration(episode.duration)}
                    </div>
                ` : ''}
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-brand-cyan/20 text-brand-cyan px-2 py-1 rounded text-xs font-semibold">
                        ${episode.topic || 'Episode'}
                    </span>
                    ${episode.episode_number ? `
                        <span class="text-gray-400 text-sm">#${episode.episode_number}</span>
                    ` : ''}
                </div>
                <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-brand-cyan transition-colors">
                    ${episode.title}
                </h3>
                <p class="text-gray-400 mb-3 text-sm leading-relaxed">
                    ${truncateText(episode.description || '')}
                </p>
                ${episode.scripture_reference ? `
                    <p class="text-brand-cyan text-sm font-medium mb-4">
                        <i class="fas fa-book mr-1"></i>${episode.scripture_reference}
                    </p>
                ` : ''}
                <div class="flex items-center justify-between">
                    <span class="text-gray-500 text-sm">
                        ${episode.published_at ? formatDate(episode.published_at) : 'Coming Soon'}
                    </span>
                    <div class="flex gap-2">
                        ${episode.audio_url ? `
                            <button class="text-brand-cyan hover:text-brand-blue transition-colors" title="Listen">
                                <i class="fas fa-headphones"></i>
                            </button>
                        ` : ''}
                        ${episode.video_url || episode.youtube_url ? `
                            <a href="${episode.youtube_url || episode.video_url}" target="_blank" class="text-brand-cyan hover:text-brand-blue transition-colors" title="Watch on YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        ` : ''}
                        <button class="text-brand-cyan hover:text-brand-blue transition-colors" title="Share">
                            <i class="fas fa-share"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function renderBlogPosts() {
    const blogGrid = document.getElementById('blog-grid');
    if (!blogGrid) return;

    if (currentBlogPosts.length === 0) {
        blogGrid.innerHTML = `
            <div class="col-span-full text-center py-12">
                <i class="fas fa-pen-alt text-4xl text-gray-600 mb-4"></i>
                <p class="text-gray-400">No blog posts available yet. Check back soon!</p>
            </div>
        `;
        return;
    }

    blogGrid.innerHTML = currentBlogPosts.map(post => `
        <article class="bg-gray-700 rounded-lg overflow-hidden border border-gray-600 hover:border-brand-cyan/50 transition-colors group">
            <div class="relative">
                <img src="${post.featured_image || '/static/images/blog-placeholder.jpg'}" 
                     alt="${post.title}" 
                     class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-4 left-4">
                    ${post.tags ? JSON.parse(post.tags).slice(0, 2).map(tag => `
                        <span class="bg-brand-cyan/20 text-brand-cyan px-2 py-1 rounded text-xs font-semibold mr-2">
                            ${tag}
                        </span>
                    `).join('') : ''}
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-brand-cyan transition-colors">
                    ${post.title}
                </h3>
                <p class="text-gray-400 mb-3 text-sm leading-relaxed">
                    ${truncateText(post.excerpt || '')}
                </p>
                ${post.scripture_reference ? `
                    <p class="text-brand-cyan text-sm font-medium mb-4">
                        <i class="fas fa-book mr-1"></i>${post.scripture_reference}
                    </p>
                ` : ''}
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center text-gray-500">
                        <i class="fas fa-user mr-2"></i>
                        <span>${post.author_name || 'Paul Kumeni Agbo'}</span>
                    </div>
                    <span class="text-gray-500">
                        ${post.published_at ? formatDate(post.published_at) : 'Draft'}
                    </span>
                </div>
                <div class="mt-4">
                    <button class="text-brand-cyan hover:text-brand-blue font-medium text-sm transition-colors">
                        Read More <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </article>
    `).join('');
}

// Form handlers
async function handleNewsletterSubscription(event) {
    event.preventDefault();
    
    const email = document.getElementById('newsletter-email').value;
    if (!email) return;

    try {
        const response = await axios.post('/api/newsletter/subscribe', { email });
        showNotification(response.data.message);
        document.getElementById('newsletter-email').value = '';
    } catch (error) {
        const message = error.response?.data?.error || 'Failed to subscribe to newsletter';
        showNotification(message, 'error');
    }
}

async function handleContactForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);

    try {
        const response = await axios.post('/api/contact', data);
        showNotification(response.data.message);
        event.target.reset();
        closeModal('contact-modal');
    } catch (error) {
        const message = error.response?.data?.error || 'Failed to send message';
        showNotification(message, 'error');
    }
}

async function handlePrayerForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    data.isAnonymous = document.getElementById('anonymous-prayer').checked;

    try {
        const response = await axios.post('/api/prayer-request', data);
        showNotification(response.data.message);
        event.target.reset();
        closeModal('prayer-modal');
    } catch (error) {
        const message = error.response?.data?.error || 'Failed to submit prayer request';
        showNotification(message, 'error');
    }
}

// Stripe Integration Variables
let stripe = null;
let elements = null;
let cardElement = null;

// Initialize Stripe Elements (only for donate page)
function initializeStripe() {
    console.log('Initializing Stripe...');
    
    if (typeof Stripe === 'undefined') {
        console.error('Stripe library not loaded');
        showNotification('Payment system not available. Please refresh the page.', 'error');
        return;
    }
    
    try {
        // Initialize Stripe with your publishable key
        // FOR TESTING: Use your TEST publishable key (starts with pk_test_...)  
        // FOR PRODUCTION: Use your LIVE publishable key (starts with pk_live_...)
        stripe = Stripe('pk_test_51SApMuCCoEgEJMvXXWlty2Gg1PDtYRU4JcUPZ7b4KTiK4qHKjzTRzUABFIlKZEosJQEUQlEEwvto6dFKPiJdbt5p00EkC9A6jr');
        console.log('Stripe initialized successfully');
        
        // Create Elements instance
        elements = stripe.elements({
            appearance: {
                theme: 'night',
                variables: {
                    colorPrimary: '#00E5FF',
                    colorBackground: '#374151',
                    colorText: '#ffffff',
                    colorDanger: '#df1b41',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '8px',
                }
            }
        });
        console.log('Stripe Elements created');

        // Create card element
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#ffffff',
                    '::placeholder': {
                        color: '#9ca3af',
                    },
                },
            }
        });
        console.log('Card element created');

        // Mount the card element
        const cardElementContainer = document.getElementById('card-element');
        if (cardElementContainer) {
            cardElement.mount('#card-element');
            console.log('Card element mounted successfully');
            
            // Handle real-time validation errors from the card Element
            cardElement.on('change', ({error}) => {
                const displayError = document.getElementById('card-errors');
                if (error) {
                    displayError.textContent = error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        } else {
            console.error('Card element container not found');
            showNotification('Payment form not found. Please refresh the page.', 'error');
        }
    } catch (error) {
        console.error('Error initializing Stripe:', error);
        showNotification('Payment system error. Please refresh the page.', 'error');
    }
}

async function handleDonateForm(event) {
    event.preventDefault();
    
    console.log('Donation form submitted');
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);

    if (!data.amount || parseFloat(data.amount) <= 0) {
        showNotification('Please enter a valid donation amount', 'error');
        return;
    }

    if (!stripe) {
        console.error('Stripe not initialized');
        showNotification('Payment system not ready. Please refresh the page and try again.', 'error');
        return;
    }

    if (!cardElement) {
        console.error('Card element not available');
        showNotification('Payment form not ready. Please refresh the page and try again.', 'error');
        return;
    }

    // Show loading state
    setDonationLoading(true);

    try {
        // Create payment intent on server
        const response = await axios.post('/api/donate/create-payment-intent', {
            amount: parseFloat(data.amount),
            donorName: data.donorName,
            donorEmail: data.donorEmail,
            message: data.message,
            donationType: data.donationType
        });

        const { clientSecret } = response.data;

        // Confirm payment with Stripe
        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: data.donorName || 'Anonymous Donor',
                    email: data.donorEmail || '',
                }
            }
        });

        if (error) {
            console.error('Payment failed:', error);
            showNotification(error.message, 'error');
        } else if (paymentIntent.status === 'succeeded') {
            showNotification('Thank you for your generous donation! Your payment has been processed successfully.');
            
            // Reset form
            event.target.reset();
            cardElement.clear();
            
            // Show success message or redirect
            setTimeout(() => {
                window.location.href = '/donate?success=1';
            }, 2000);
        }
    } catch (error) {
        console.error('Donation error:', error);
        const message = error.response?.data?.error || 'Failed to process donation';
        showNotification(message, 'error');
    } finally {
        setDonationLoading(false);
    }
}

function setDonationLoading(isLoading) {
    const donateButton = document.getElementById('donate-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    
    if (isLoading) {
        donateButton.disabled = true;
        buttonText.classList.add('hidden');
        spinner.classList.remove('hidden');
    } else {
        donateButton.disabled = false;
        buttonText.classList.remove('hidden');
        spinner.classList.add('hidden');
    }
}

// Mobile menu handler
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenu.classList.toggle('hidden');
}

// Smooth scrolling for navigation links
function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
                
                // Close mobile menu if open
                const mobileMenu = document.getElementById('mobile-menu');
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
    });
}

// Navbar scroll effect
function setupNavbarScrollEffect() {
    const navbar = document.querySelector('nav');
    let lastScrollTop = 0;

    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
}

// Initialize intersection observer for animations
function setupScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    // Observe sections
    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });
}

// Page-specific handlers
function handleHomePage() {
    // Load recent episodes for homepage
    loadEpisodesForHome();
    
    // Newsletter signup
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubscription);
    }
}

function handleAboutPage() {
    // Any about page specific functionality
    console.log('About page loaded');
}

function handleEpisodesPage() {
    // Load all episodes
    loadEpisodes();
    
    // Setup episode filters
    setupEpisodeFilters();
    
    // Setup audio player
    setupAudioPlayer();
}

function handleBlogPage() {
    // Load blog posts
    loadBlogPosts();
    
    // Setup blog filters
    setupBlogFilters();
}

function handleDonatePage() {
    console.log('Handling donate page');
    
    // Setup donation tier buttons first
    setupDonationTiers();
    
    // Setup donation form
    const donationForm = document.getElementById('donation-form');
    if (donationForm) {
        donationForm.addEventListener('submit', handleDonateForm);
        console.log('Donation form event listener added');
    }
    
    // Initialize Stripe after a short delay to ensure DOM is ready
    setTimeout(() => {
        initializeStripe();
    }, 500);
    
    // Check for success parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showNotification('🎉 Donation completed successfully! Thank you for supporting our ministry!');
    }
}

function handleContactPage() {
    // Setup contact forms
    const contactForm = document.getElementById('contact-form');
    const prayerForm = document.getElementById('prayer-request-form');
    const newsletterForm = document.getElementById('newsletter-signup-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactForm);
    }
    
    if (prayerForm) {
        prayerForm.addEventListener('submit', handlePrayerForm);
    }
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubscription);
    }
}

// Enhanced episode loading for homepage
async function loadEpisodesForHome() {
    try {
        const response = await axios.get('/api/episodes?limit=3');
        currentEpisodes = response.data.episodes;
        renderFeaturedEpisodes();
    } catch (error) {
        console.error('Error loading featured episodes:', error);
    }
}

function renderFeaturedEpisodes() {
    const featuredGrid = document.getElementById('featured-episodes');
    if (!featuredGrid) return;

    if (currentEpisodes.length === 0) {
        featuredGrid.innerHTML = `
            <div class="col-span-full text-center py-8">
                <p class="text-gray-400">No episodes available yet. Check back soon!</p>
            </div>
        `;
        return;
    }

    featuredGrid.innerHTML = currentEpisodes.slice(0, 3).map(episode => `
        <div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700 hover:border-brand-cyan/50 transition-colors group">
            <div class="relative">
                <img src="${episode.thumbnail_url || '/static/images/episode-placeholder.jpg'}" 
                     alt="${episode.title}" 
                     class="w-full h-48 object-cover">
                <div class="absolute inset-0 bg-black/40 group-hover:bg-black/60 transition-colors flex items-center justify-center">
                    ${episode.youtube_url || episode.video_url ? `
                        <a href="${episode.youtube_url || episode.video_url}" target="_blank" class="bg-brand-cyan text-gray-900 p-4 rounded-full hover:bg-brand-blue transition-colors transform hover:scale-110">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    ` : `
                        <a href="/episodes" class="bg-brand-cyan text-gray-900 p-4 rounded-full hover:bg-brand-blue transition-colors transform hover:scale-110">
                            <i class="fas fa-play text-xl"></i>
                        </a>
                    `}
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-brand-cyan transition-colors">
                    ${episode.title}
                </h3>
                <p class="text-gray-400 mb-3 text-sm leading-relaxed">
                    ${truncateText(episode.description || '', 100)}
                </p>
                ${episode.scripture_reference ? `
                    <p class="text-brand-cyan text-sm font-medium">
                        <i class="fas fa-book mr-1"></i>${episode.scripture_reference}
                    </p>
                ` : ''}
            </div>
        </div>
    `).join('');
}

// Episode page specific functions
function setupEpisodeFilters() {
    const topicFilter = document.getElementById('topic-filter');
    const statusFilter = document.getElementById('status-filter');
    
    if (topicFilter) {
        topicFilter.addEventListener('change', filterEpisodes);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterEpisodes);
    }
}

function setupBlogFilters() {
    const blogFilter = document.getElementById('blog-filter');
    
    if (blogFilter) {
        blogFilter.addEventListener('change', filterBlogPosts);
    }
}

function setupDonationTiers() {
    // Global function for donation tier selection
    window.selectDonationTier = function(amount, tierName) {
        const amountInput = document.querySelector('#donation-form input[name="amount"]');
        if (amountInput) {
            amountInput.value = amount;
        }
        
        // Update UI to show selected tier
        document.querySelectorAll('.donation-tier').forEach(tier => {
            tier.classList.remove('border-brand-cyan', 'border-2');
        });
        
        event.target.closest('.donation-tier')?.classList.add('border-brand-cyan', 'border-2');
    }
}

function setupAudioPlayer() {
    // Audio player functionality for episodes page
    const audioPlayer = document.getElementById('audio-player');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const closePlayer = document.getElementById('close-player');
    
    if (closePlayer) {
        closePlayer.addEventListener('click', () => {
            audioPlayer.classList.add('hidden');
        });
    }
}

async function filterEpisodes() {
    const topicFilter = document.getElementById('topic-filter');
    const statusFilter = document.getElementById('status-filter');
    
    const topic = topicFilter?.value || '';
    const status = statusFilter?.value || 'published';
    
    try {
        let url = `/api/episodes?status=${status}&limit=20`;
        if (topic) url += `&topic=${encodeURIComponent(topic)}`;
        
        const response = await axios.get(url);
        currentEpisodes = response.data.episodes;
        renderEpisodes();
    } catch (error) {
        console.error('Error filtering episodes:', error);
    }
}

async function filterBlogPosts() {
    const blogFilter = document.getElementById('blog-filter');
    const category = blogFilter?.value || '';
    
    try {
        let url = '/api/blog?status=published&limit=20';
        if (category) url += `&category=${encodeURIComponent(category)}`;
        
        const response = await axios.get(url);
        currentBlogPosts = response.data.posts;
        renderBlogPosts();
        
        // Update post count
        const postCount = document.getElementById('post-count');
        if (postCount) {
            postCount.textContent = `${response.data.posts.length} articles found`;
        }
    } catch (error) {
        console.error('Error filtering blog posts:', error);
    }
}

// Initialize the application
function initializeApp() {
    // Setup global event listeners
    setupNavbarScrollEffect();
    
    // Mobile menu
    document.getElementById('mobile-menu-button')?.addEventListener('click', toggleMobileMenu);
    
    // Close modals when clicking outside
    document.addEventListener('click', (event) => {
        const modals = ['contact-modal', 'prayer-modal', 'donate-modal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                closeModal(modalId);
            }
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const modals = ['contact-modal', 'prayer-modal', 'donate-modal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (!modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });
    
    // Initialize page-specific functionality
    const currentPath = window.location.pathname;
    if (pageHandlers[currentPath]) {
        pageHandlers[currentPath]();
    }
}

// Start the application when DOM is ready
document.addEventListener('DOMContentLoaded', initializeApp);

// Expose global functions
window.openContactModal = openContactModal;
window.openPrayerModal = openPrayerModal;
window.openDonateModal = openDonateModal;
window.closeModal = closeModal;
window.setDonationAmount = setDonationAmount;