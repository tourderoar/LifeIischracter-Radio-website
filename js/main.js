/**
 * Life is Character Radio - Frontend JavaScript
 * API-driven dynamic content loading and interactions
 */

// Global configuration
const API_BASE = window.location.origin + window.location.pathname.match(/.*\//)[0] + 'api/';

// Stripe instance (only set on pages that load Stripe.js, e.g. donate/events)
let stripe;
if (typeof Stripe !== 'undefined' && typeof STRIPE_PUBLISHABLE_KEY !== 'undefined') {
    stripe = Stripe(STRIPE_PUBLISHABLE_KEY);
}

// API Helper Functions
const API = {
    async get(endpoint) {
        const res = await fetch(API_BASE + endpoint);
        if (!res.ok) throw new Error('API request failed');
        return res.json();
    },
    
    async post(endpoint, data) {
        const res = await fetch(API_BASE + endpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Request failed');
        return json;
    }
};

// UI Helper Functions
function showNotification(message, type = 'success') {
    const colors = {success: 'bg-[#00E5FF] text-[#001A2E]', error: 'bg-red-600 text-white'};
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notif = document.createElement('div');
    notif.className = `fixed top-24 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl max-w-sm transition-all duration-300 ${colors[type]}`;
    notif.innerHTML = `<div class="flex items-center font-['Inter']"><i class="fas ${icon} mr-3 text-xl"></i><p class="font-medium">${message}</p></div>`;
    
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 5000);
}

function showLoader(element) {
    element.innerHTML = '<div class="flex justify-center py-12"><div class="animate-spin rounded-full h-12 w-12 border-4 border-[#00E5FF] border-t-transparent"></div></div>';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {style: 'currency', currency: 'USD'}).format(amount);
}

// Homepage Featured Episodes
async function loadFeaturedEpisodes() {
    const container = document.getElementById('featured-episodes');
    if (!container) return;
    
    showLoader(container);
    
    try {
        const data = await API.get('episodes/?limit=6');
        
        if (!data.data?.length) {
            container.innerHTML = '<div class="col-span-full text-center text-gray-400 py-12 font-[\'Inter\']">No episodes available yet. Check back soon!</div>';
            return;
        }
        
        container.innerHTML = data.data.map(ep => `
            <div class="bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="relative pb-[56.25%]">
                    <iframe class="absolute inset-0 w-full h-full" src="${ep.youtube_url.replace('watch?v=', 'embed/')}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-6">
                    <h3 class="font-['Cinzel'] text-xl text-[#00E5FF] mb-2 line-clamp-2">${ep.title}</h3>
                    ${ep.topic ? `<span class="inline-block bg-[#00E5FF]/10 text-[#00E5FF] px-3 py-1 rounded-full text-xs font-['Inter'] mb-3">${ep.topic.name}</span>` : ''}
                    <p class="text-gray-300 font-['Inter'] text-sm mb-3 line-clamp-3">${ep.description}</p>
                    ${ep.scripture_reference ? `<p class="text-[#FFD700] font-['Cinzel'] text-sm italic">📖 ${ep.scripture_reference}</p>` : ''}
                </div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<div class="col-span-full text-center text-red-400 py-12 font-[\'Inter\']">Failed to load episodes. Please try again later.</div>';
    }
}

// Episodes Page
async function loadEpisodes(topicId = null, status = null) {
    const container = document.getElementById('episodes-list') || document.getElementById('episodes-grid');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    
    if (!container) return;
    
    // Show loading, hide others
    if (loadingState) loadingState.classList.remove('hidden');
    container.classList.add('hidden');
    if (emptyState) emptyState.classList.add('hidden');
    
    try {
        const params = new URLSearchParams();
        if (topicId) params.append('topic_id', topicId);
        if (status) params.append('status', status);
        params.append('limit', '50');
        
        const data = await API.get(`episodes/?${params}`);
        
        // Hide loading
        if (loadingState) loadingState.classList.add('hidden');
        
        if (!data.data?.length) {
            if (emptyState) {
                emptyState.classList.remove('hidden');
            } else {
                container.innerHTML = '<div class="col-span-full text-center text-gray-400 py-12 font-[\'Inter\']">No episodes available yet.</div>';
                container.classList.remove('hidden');
            }
            return;
        }
        
        container.innerHTML = data.data.map(ep => `
            <div class="bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="relative pb-[56.25%]">
                    <iframe class="absolute inset-0 w-full h-full" src="${ep.youtube_url.replace('watch?v=', 'embed/')}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-6">
                    <h3 class="font-['Cinzel'] text-xl text-[#00E5FF] mb-2">${ep.title}</h3>
                    ${ep.topic ? `<span class="inline-block bg-[#00E5FF]/10 text-[#00E5FF] px-3 py-1 rounded-full text-xs font-['Inter'] mb-3">${ep.topic.name}</span>` : ''}
                    <p class="text-gray-300 font-['Inter'] text-sm mb-3">${ep.description}</p>
                    ${ep.scripture_reference ? `<p class="text-[#FFD700] font-['Cinzel'] text-sm italic">📖 ${ep.scripture_reference}</p>` : ''}
                </div>
            </div>
        `).join('');
        container.classList.remove('hidden');
    } catch (error) {
        if (loadingState) loadingState.classList.add('hidden');
        if (emptyState) {
            emptyState.classList.remove('hidden');
        } else {
            container.innerHTML = '<div class="col-span-full text-center text-red-400 py-12 font-[\'Inter\']">Failed to load episodes. Please try again later.</div>';
            container.classList.remove('hidden');
        }
    }
}

async function loadEpisodeTopics() {
    const topicFilter = document.getElementById('topic-filter');
    const statusFilter = document.getElementById('status-filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    if (!topicFilter) return;
    
    try {
        const data = await API.get('episodes/topics');
        
        // Keep "All Topics" option and add dynamic topics
        const topicsHTML = data.data.map(t => 
            `<option value="${t.id}">${t.name} (${t.episode_count})</option>`
        ).join('');
        
        topicFilter.innerHTML = `<option value="">All Topics</option>${topicsHTML}`;
        
        // Filter function that gets current values
        const filterEpisodes = () => {
            const topicId = topicFilter.value ? parseInt(topicFilter.value) : null;
            const status = statusFilter?.value || null;
            loadEpisodes(topicId, status);
        };
        
        // Add change event listeners
        topicFilter.addEventListener('change', filterEpisodes);
        if (statusFilter) {
            statusFilter.addEventListener('change', filterEpisodes);
        }
        
        // Clear filters button
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                topicFilter.value = '';
                if (statusFilter) statusFilter.value = 'published';
                loadEpisodes(null, 'published');
            });
        }
    } catch (error) {
        console.error('Failed to load topics:', error);
    }
}

// Blog Page
async function loadBlogPosts(categoryId = null) {
    const container = document.getElementById('blog-posts');
    if (!container) return;
    
    showLoader(container);
    
    try {
        const params = new URLSearchParams();
        if (categoryId) params.append('category_id', categoryId);
        params.append('limit', '20');
        
        const data = await API.get(`blog/?${params}`);
        
        if (!data.data?.length) {
            container.innerHTML = '<p class="text-center text-gray-400 py-12">No blog posts available yet.</p>';
            return;
        }
        
        container.innerHTML = data.data.map(post => `
            <article class="bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300">
                ${post.featured_image ? `<img src="${post.featured_image}" alt="${post.title}" class="w-full h-48 object-cover">` : `<div class="w-full h-48 bg-gradient-to-br from-[#006B8F] to-[#001A2E] flex items-center justify-center"><i class="fas fa-book-open text-[#00E5FF]/30 text-6xl"></i></div>`}
                <div class="p-6">
                    <h2 class="font-['Cinzel'] text-2xl text-[#00E5FF] mb-3">${post.title}</h2>
                    <div class="flex flex-wrap gap-2 mb-3">
                        ${post.categories.map(c => `<span class="bg-[#00E5FF]/10 text-[#00E5FF] px-3 py-1 rounded-full text-xs font-['Inter']">${c.name}</span>`).join('')}
                    </div>
                    <p class="text-gray-300 font-['Inter'] mb-4 line-clamp-3">${post.excerpt || ''}</p>
                    ${post.scripture_reference ? `<p class="text-[#FFD700] font-['Cinzel'] italic mb-4">📖 ${post.scripture_reference}</p>` : ''}
                    <div class="flex justify-between items-center text-sm text-gray-400 font-['Inter'] mb-4">
                        <span>${post.author_name}</span>
                        <span>${formatDate(post.published_at)}</span>
                    </div>
                    <a href="${window.location.origin + window.location.pathname.match(/.*\//)[0]}blog/${post.slug}" class="inline-block bg-[#00E5FF] text-[#001A2E] px-6 py-2 rounded-lg font-['Inter'] font-bold hover:bg-[#0099CC] transition-colors">
                        Read More <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </article>
        `).join('');
    } catch {
        container.innerHTML = '<p class="text-center text-red-400 py-12">Failed to load posts</p>';
    }
}

async function loadBlogCategories() {
    const container = document.getElementById('blog-categories');
    if (!container) return;
    
    try {
        const data = await API.get('blog/categories');
        container.innerHTML = `
            <button onclick="loadBlogPosts()" class="px-4 py-2 rounded-lg bg-[#001A2E] text-[#00E5FF] hover:bg-[#00E5FF] hover:text-[#001A2E] transition-colors font-['Inter']">All</button>
            ${data.data.map(c => `
                <button onclick="loadBlogPosts(${c.id})" class="px-4 py-2 rounded-lg bg-[#001A2E] text-[#00E5FF] hover:bg-[#00E5FF] hover:text-[#001A2E] transition-colors font-['Inter']">${c.name} (${c.post_count})</button>
            `).join('')}
        `;
    } catch {}
}

// Events Page
async function loadEvents(status = 'upcoming') {
    const container = document.getElementById('events-list');
    if (!container) return;
    
    showLoader(container);
    
    try {
        const data = await API.get(`events/?status=${status}`);
        
        if (!data.data?.length) {
            container.innerHTML = '<p class="text-center text-gray-400 py-12">No events available.</p>';
            return;
        }
        
        container.innerHTML = data.data.map(event => `
            <div class="bg-gradient-to-br from-[#001A2E] to-[#006B8F] rounded-xl overflow-hidden shadow-lg">
                ${event.featured_image ? `<img src="${event.featured_image}" alt="${event.title}" class="w-full h-48 object-cover">` : ''}
                <div class="p-6">
                    <h3 class="font-['Cinzel'] text-2xl text-[#00E5FF] mb-3">${event.title}</h3>
                    <p class="text-gray-300 font-['Inter'] mb-4">${event.description}</p>
                    <div class="space-y-2 text-sm font-['Inter'] mb-4">
                        <p class="text-gray-300"><i class="fas fa-calendar text-[#00E5FF] mr-2"></i>${formatDate(event.event_date)}</p>
                        <p class="text-gray-300"><i class="fas fa-map-marker-alt text-[#00E5FF] mr-2"></i>${event.location}</p>
                        <p class="text-[#FFD700] font-semibold text-lg">${event.cost > 0 ? formatCurrency(event.cost) : 'FREE'}</p>
                        ${event.max_capacity ? `<p class="text-gray-400">${event.spots_left} spots left</p>` : ''}
                    </div>
                    ${!event.is_full ? `
                        <button onclick="registerForEvent(${event.id}, ${event.cost})" 
                                class="w-full bg-[#00E5FF] text-[#001A2E] font-['Inter'] font-bold py-3 rounded-lg hover:bg-[#0099CC] transition-colors">
                            ${event.cost > 0 ? 'Register & Pay' : 'Register Free'}
                        </button>
                    ` : `<p class="text-center text-red-400 font-['Inter'] font-bold">Event Full</p>`}
                </div>
            </div>
        `).join('');
    } catch {
        container.innerHTML = '<p class="text-center text-red-400 py-12">Failed to load events</p>';
    }
}

async function registerForEvent(eventId, cost) {
    if (!confirm('Register for this event?')) return;
    
    try {
        const result = await API.post('events/register', {event_id: eventId});
        
        if (result.data.status === 'completed') {
            showNotification('Successfully registered!');
            loadEvents();
        } else if (result.data.client_secret) {
            await processPayment(result.data.client_secret, cost, 'event');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Donation Page
async function loadDonationTypes() {
    const container = document.getElementById('donation-types');
    if (!container) return;
    
    try {
        const data = await API.get('donations/');
        container.innerHTML = data.data.map(type => `
            <div class="bg-gradient-to-br from-[#001A2E] to-[#006B8F] p-6 rounded-xl text-center hover:scale-105 transition-transform cursor-pointer"
                 onclick="setDonationAmount(${type.suggested_amount || 0})">
                <h3 class="font-['Cinzel'] text-xl text-[#00E5FF] mb-2">${type.name}</h3>
                <p class="text-gray-300 font-['Inter'] text-sm mb-3">${type.description || ''}</p>
                ${type.suggested_amount ? `<p class="text-[#FFD700] font-bold text-2xl">${formatCurrency(type.suggested_amount)}</p>` : ''}
            </div>
        `).join('');
    } catch {}
}

function setDonationAmount(amount) {
    const input = document.getElementById('donation-amount');
    if (input) input.value = amount;
}

async function submitDonation(formData) {
    try {
        const result = await API.post('donations/create', formData);
        
        if (result.data.client_secret) {
            await processPayment(result.data.client_secret, formData.amount, 'donation');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Payment Processing
async function processPayment(clientSecret, amount, type) {
    if (!stripe) {
        showNotification('Payment system not initialized', 'error');
        return;
    }
    
    const {error} = await stripe.confirmPayment({
        clientSecret,
        confirmParams: {
            return_url: window.location.origin + window.location.pathname.match(/.*\//)[0] + (type === 'event' ? 'event-success' : 'donation-success')
        }
    });
    
    if (error) {
        showNotification(error.message, 'error');
    }
}

// Contact Form
async function submitContactForm(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        name: form.name.value,
        email: form.email.value,
        subject: form.subject.value,
        message: form.message.value
    };
    
    try {
        await API.post('contact/submit', data);
        showNotification('Message sent! We\'ll respond soon.');
        form.reset();
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Prayer Request
async function submitPrayerRequest(e) {
    e.preventDefault();
    const form = e.target;
    const data = {
        name: form.name.value,
        email: form.email.value,
        prayer_request: form.prayer_request.value,
        is_anonymous: form.is_anonymous?.checked || false
    };
    
    try {
        await API.post('prayer/submit', data);
        showNotification('Prayer request submitted. We are praying for you!');
        form.reset();
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Free 7-day devotional signup (homepage, episodes and about pages)
async function requestDevotional(e) {
    e.preventDefault();
    const form = e.target;
    const button = form.querySelector('button[type="submit"]');
    const original = button?.textContent;

    const data = {
        firstName: form.querySelector('input[name="firstName"]')?.value || '',
        email: form.querySelector('input[name="email"]').value,
        source: 'devotional',
        tags: ['devotional-funnel']
    };

    if (button) {
        button.disabled = true;
        button.textContent = 'Sending...';
    }

    try {
        const result = await API.post('newsletter/subscribe', data);
        showNotification(result.message || 'Your devotional is on its way! Check your inbox.');
        form.reset();
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = original;
        }
    }
}

// Page Initialization
document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;
    
    if (path.includes('episodes')) {
        loadEpisodeTopics();
        loadEpisodes(null, 'published'); // Load with default status
        
        // Close player button
        const closePlayer = document.getElementById('close-player');
        if (closePlayer) {
            closePlayer.addEventListener('click', () => {
                document.getElementById('audio-player')?.classList.add('hidden');
            });
        }
    } else if (path.includes('blog')) {
        loadBlogCategories();
        loadBlogPosts();
    } else if (path.includes('events')) {
        loadEvents();
    } else if (path.includes('donate')) {
        loadDonationTypes();
    } else if (path.endsWith('/') || path.includes('index')) {
        loadFeaturedEpisodes(); // Homepage featured episodes
    }
    
    // Form Event Listeners
    document.querySelectorAll('.devotional-form').forEach(form => {
        form.addEventListener('submit', requestDevotional);
    });

    const prayerForm = document.getElementById('prayer-form');
    if (prayerForm) {
        prayerForm.addEventListener('submit', submitPrayerRequest);
    }
});

// Play Episode Function (Global - used by episodes page)
window.playEpisode = function(youtubeUrl, title, scripture) {
    const player = document.getElementById('audio-player');
    if (player) {
        document.getElementById('player-title').textContent = title;
        document.getElementById('player-subtitle').textContent = scripture || 'Listen now';
        player.classList.remove('hidden');
    }
};
