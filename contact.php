<?php
/**
 * Life is Character Radio - Contact Page
 * 
 * Location: /contact.php
 * 
 * Description:
 * Contact page with multiple forms: general contact, prayer requests, and newsletter signup.
 * Forms submit via AJAX to API endpoints.
 */

require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Contact Us - Connect With Our Ministry';
$currentPage = 'contact';
include __DIR__ . '/includes/header.php';
?>

<section class="pt-24 pb-20 bg-gray-800">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold font-cinematic mb-6 text-brand-cyan">Connect With Us</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                We'd love to hear from you. Reach out for prayer requests, questions, or to join our community
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-12">
            <!-- Contact Forms -->
            <div class="space-y-8">
                <!-- General Contact Form -->
                <div class="bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20">
                    <h2 class="text-2xl font-bold text-brand-cyan mb-6">Send Us a Message</h2>
                    <form id="contact-form" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Full Name *</label>
                                <input type="text" name="name" required
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                       placeholder="Your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Email Address *</label>
                                <input type="email" name="email" required
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                       placeholder="your@email.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
                            <select name="subject" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors">
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Episode Feedback">Episode Feedback</option>
                                <option value="Speaking Request">Speaking Request</option>
                                <option value="Partnership">Partnership Opportunity</option>
                                <option value="Technical Issue">Technical Issue</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Message *</label>
                            <textarea name="message" rows="5" required
                                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                      placeholder="Share your message with us..."></textarea>
                        </div>
                        
                        <div id="contact-message" class="hidden p-4 rounded-lg"></div>
                        
                        <button type="submit" class="w-full bg-brand-cyan text-gray-900 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>Send Message
                        </button>
                    </form>
                </div>

                <!-- Prayer Request Form -->
                <div class="bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20">
                    <h2 class="text-2xl font-bold text-brand-cyan mb-6">Prayer Request</h2>
                    <form id="prayer-request-form" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Your Name</label>
                                <input type="text" name="name"
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                       placeholder="Your name (optional)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                                <input type="email" name="email"
                                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                       placeholder="your@email.com (optional)">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Prayer Request *</label>
                            <textarea name="request" rows="4" required
                                      class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                      placeholder="Share your prayer request with us..."></textarea>
                        </div>
                        
                        <div id="prayer-message" class="hidden p-4 rounded-lg"></div>
                        
                        <button type="submit" class="w-full bg-brand-cyan text-gray-900 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                            <i class="fas fa-praying-hands mr-2"></i>Submit Prayer Request
                        </button>
                    </form>
                    
                    <p class="text-gray-400 text-sm text-center mt-4">
                        <i class="fas fa-lock mr-1"></i>Your prayer requests are treated with confidentiality and care.
                    </p>
                </div>

                <!-- Newsletter Signup -->
                <div class="bg-gradient-to-br from-brand-cyan/10 to-brand-blue/10 p-8 rounded-lg border border-brand-cyan/30">
                    <h2 class="text-2xl font-bold text-brand-cyan mb-4">Stay Connected</h2>
                    <p class="text-gray-300 mb-6">Subscribe to receive episode updates, devotionals, and character-building resources</p>
                    <form id="newsletter-signup-form" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <input type="text" name="name" 
                                   class="px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                   placeholder="Your name (optional)">
                            <input type="email" name="email" required
                                   class="px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-brand-cyan transition-colors"
                                   placeholder="Your email *">
                        </div>
                        
                        <div id="newsletter-message" class="hidden p-4 rounded-lg"></div>
                        
                        <button type="submit" class="w-full bg-brand-cyan text-gray-900 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                            <i class="fas fa-envelope mr-2"></i>Subscribe Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Information & Map -->
            <div class="space-y-8">
                <!-- Contact Details -->
                <div class="bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20">
                    <h2 class="text-2xl font-bold text-brand-cyan mb-6">Get In Touch</h2>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-lg flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Our Location</h3>
                                <p class="text-gray-300">
                                    2860 South State Hwy 161<br>
                                    STE160-211<br>
                                    Grand Prairie, TX 75052
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-lg flex-shrink-0">
                                <i class="fas fa-phone text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Phone</h3>
                                <p class="text-gray-300">
                                    <a href="tel:6827013557" class="hover:text-brand-cyan transition-colors">
                                        (682) 701-3557
                                    </a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-lg flex-shrink-0">
                                <i class="fas fa-envelope text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Email</h3>
                                <p class="text-gray-300">
                                    <a href="mailto:contact@lifeischaracterradio.com" class="hover:text-brand-cyan transition-colors break-all">
                                        contact@lifeischaracterradio.com
                                    </a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="bg-brand-cyan text-gray-900 p-3 rounded-lg flex-shrink-0">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Response Time</h3>
                                <p class="text-gray-300">
                                    We typically respond within 24-48 hours
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="bg-gradient-to-br from-brand-cyan/10 to-brand-blue/10 p-8 rounded-lg border border-brand-cyan/30">
                    <h3 class="text-xl font-bold text-brand-cyan mb-6">Connect on Social Media</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <a href="https://www.youtube.com/@lifeischaracterradio959" target="_blank" 
                           class="bg-gray-700 hover:bg-brand-cyan text-brand-cyan hover:text-gray-900 p-4 rounded-lg transition-all text-center group">
                            <i class="fab fa-youtube text-3xl mb-2"></i>
                            <p class="text-xs">YouTube</p>
                        </a>
                        <a href="https://www.facebook.com/share/16gqTB1Eag/?mibextid=wwXIfr" target="_blank" 
                           class="bg-gray-700 hover:bg-brand-cyan text-brand-cyan hover:text-gray-900 p-4 rounded-lg transition-all text-center group">
                            <i class="fab fa-facebook text-3xl mb-2"></i>
                            <p class="text-xs">Facebook</p>
                        </a>
                        <a href="https://www.instagram.com/lifeischaracterradio" target="_blank" 
                           class="bg-gray-700 hover:bg-brand-cyan text-brand-cyan hover:text-gray-900 p-4 rounded-lg transition-all text-center group">
                            <i class="fab fa-instagram text-3xl mb-2"></i>
                            <p class="text-xs">Instagram</p>
                        </a>
                        <a href="https://x.com/Lifeischaracter" target="_blank" 
                           class="bg-gray-700 hover:bg-brand-cyan text-brand-cyan hover:text-gray-900 p-4 rounded-lg transition-all text-center group">
                            <i class="fab fa-twitter text-3xl mb-2"></i>
                            <p class="text-xs">Twitter</p>
                        </a>
                        <a href="https://www.tiktok.com/@lifeischaracter" target="_blank" 
                           class="bg-gray-700 hover:bg-brand-cyan text-brand-cyan hover:text-gray-900 p-4 rounded-lg transition-all text-center group">
                            <i class="fab fa-tiktok text-3xl mb-2"></i>
                            <p class="text-xs">TikTok</p>
                        </a>
                        <a href="https://www.linkedin.com/in/kumeni-agbo-233401359" target="_blank" 
                           class="bg-gray-700 hover:bg-brand-cyan text-brand-cyan hover:text-gray-900 p-4 rounded-lg transition-all text-center group">
                            <i class="fab fa-linkedin text-3xl mb-2"></i>
                            <p class="text-xs">LinkedIn</p>
                        </a>
                    </div>
                </div>

                <!-- CTA Card -->
                <div class="bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20 text-center">
                    <i class="fas fa-hands text-4xl text-brand-cyan mb-4"></i>
                    <h3 class="text-xl font-bold text-white mb-3">Support Our Ministry</h3>
                    <p class="text-gray-300 mb-6">Help us reach more lives with the transformative message of character and faith</p>
                    <a href="<?php echo BASE_URL; ?>donate" class="inline-block bg-brand-cyan text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                        <i class="fas fa-heart mr-2"></i>Donate Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Contact page form handlers
document.addEventListener('DOMContentLoaded', () => {
    // Contact form submission
    document.getElementById('contact-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageDiv = document.getElementById('contact-message');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Sending...';
        
        try {
            const formData = new FormData(form);
            const response = await fetch('<?php echo BASE_URL; ?>api/contact/submit', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            messageDiv.className = `p-4 rounded-lg ${data.success ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}`;
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            
            if (data.success) form.reset();
        } catch (error) {
            messageDiv.className = 'p-4 rounded-lg bg-red-500/20 text-red-400';
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Message';
        }
    });

    // Prayer request form submission
    document.getElementById('prayer-request-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageDiv = document.getElementById('prayer-message');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Submitting...';
        
        try {
            const formData = new FormData(form);
            const response = await fetch('<?php echo BASE_URL; ?>api/prayer/submit', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            messageDiv.className = `p-4 rounded-lg ${data.success ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}`;
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            
            if (data.success) form.reset();
        } catch (error) {
            messageDiv.className = 'p-4 rounded-lg bg-red-500/20 text-red-400';
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-praying-hands mr-2"></i>Submit Prayer Request';
        }
    });

    // Newsletter signup form submission
    document.getElementById('newsletter-signup-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const messageDiv = document.getElementById('newsletter-message');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Subscribing...';
        
        try {
            const formData = new FormData(form);
            const response = await fetch('<?php echo BASE_URL; ?>api/newsletter/subscribe', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            messageDiv.className = `p-4 rounded-lg ${data.success ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}`;
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            
            if (data.success) form.reset();
        } catch (error) {
            messageDiv.className = 'p-4 rounded-lg bg-red-500/20 text-red-400';
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-envelope mr-2"></i>Subscribe Now';
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
