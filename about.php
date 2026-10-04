<?php
/**
 * Life is Character Radio - About Us Page
 * 
 * Location: /about.php
 * 
 * Description:
 * About page showcasing our mission, vision, host information, and ministry values.
 * Premium design with brand colors and engaging content layout.
 */

require_once __DIR__ . '/includes/config.php';
$pageTitle = 'About Us - Our Story & Mission';
$currentPage = 'about';
include __DIR__ . '/includes/header.php';
?>

<section class="pt-24 pb-20 bg-gray-800">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-5xl md:text-6xl font-bold font-cinematic mb-6 text-brand-cyan">Our Story</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Building victorious lives through character and God's Word
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
            <div class="space-y-6">
                <h2 class="text-3xl font-bold text-white">Vision & Mission</h2>
                <p class="text-gray-300 leading-relaxed text-lg">
                    Life is Character Radio is a dynamic, faith-based broadcast program that empowers listeners 
                    to live victorious, character-driven lives. We use the powerful metaphor of "life as a script" 
                    to help people understand their roles and find victory through God's Word.
                </p>
                <p class="text-gray-300 leading-relaxed text-lg">
                    Our program discusses life's real challenges - failure, conflict, temptation, loss, and uncertainty - 
                    and demonstrates how people can win through character, faith, and spirit-filled living.
                </p>
                <p class="text-brand-cyan text-xl font-semibold italic">
                    "Life is a Script. Win with Character."
                </p>
            </div>
            
            <div class="bg-gradient-to-br from-brand-cyan/10 to-brand-blue/10 p-8 rounded-lg border border-brand-cyan/30">
                <h3 class="text-2xl font-bold text-brand-cyan mb-6">Host: Paul Kumeni Agbo</h3>
                <p class="text-gray-300 leading-relaxed mb-6">
                    Passionate about empowering people to discover their God-given potential and live 
                    lives of purpose, character, and victory through faith. Paul brings years of ministry 
                    experience and a heart for transformation to every broadcast.
                </p>
                <p class="text-gray-300 leading-relaxed">
                    Through Life is Character Radio, Paul shares practical wisdom and biblical insights 
                    that help listeners navigate life's challenges with confidence, character, and unwavering faith.
                </p>
            </div>
        </div>

        <!-- Value Propositions -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-12 text-brand-cyan">Why Choose Life is Character Radio?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20 hover:border-brand-cyan transition-colors">
                    <div class="bg-brand-cyan text-gray-900 p-4 rounded-full w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-book-open text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Scripture-Rooted Wisdom</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Practical strategies anchored in God's Word for tackling real-life challenges with biblical principles.
                    </p>
                </div>
                
                <div class="text-center bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20 hover:border-brand-cyan transition-colors">
                    <div class="bg-brand-cyan text-gray-900 p-4 rounded-full w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-trophy text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Character-Driven Victory</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Empowering listeners to live victorious lives through character development and spiritual growth.
                    </p>
                </div>
                
                <div class="text-center bg-gray-700/50 p-8 rounded-lg border border-brand-cyan/20 hover:border-brand-cyan transition-colors">
                    <div class="bg-brand-cyan text-gray-900 p-4 rounded-full w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Hope & Encouragement</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Bringing hope, encouragement, and practical guidance for every stage of life's journey.
                    </p>
                </div>
            </div>

            <!-- Donate funnel -->
            <div class="mt-12 bg-gradient-to-r from-brand-cyan/10 to-brand-blue/10 p-8 rounded-lg border border-brand-cyan/30 text-center">
                <h3 class="text-2xl font-bold text-white mb-3">Help Keep This Message on the Air</h3>
                <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
                    Life is Character Radio is listener-supported. Your gift funds the broadcasts, episodes
                    and resources that carry this message to the people who need it most.
                </p>
                <a href="<?php echo BASE_URL; ?>donate" class="inline-block bg-brand-cyan text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                    <i class="fas fa-heart mr-2"></i>Support Our Ministry
                </a>
            </div>
        </div>

        <!-- Target Audience -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-12 text-brand-cyan">Who We Serve</h2>
            <div class="bg-gray-700/30 p-8 rounded-lg border border-brand-cyan/20 text-center">
                <p class="text-xl text-gray-300 leading-relaxed mb-6">
                    We serve young adults to middle-aged adults (18-45) who are seeking spiritual growth, 
                    empowerment, and practical answers to life's challenges.
                </p>
                <p class="text-lg text-gray-400 leading-relaxed">
                    Our audience is socially engaged, mobile-first, and values authentic, 
                    visually compelling content that speaks to real-life experiences with biblical truth.
                </p>
            </div>
        </div>

        <?php require __DIR__ . '/includes/devotional-signup.php'; ?>

        <!-- Contact Information -->
        <div class="bg-gradient-to-r from-brand-cyan/10 to-brand-blue/10 p-8 rounded-lg border border-brand-cyan/30 text-center">
            <h2 class="text-2xl font-bold text-brand-cyan mb-6">Get In Touch</h2>
            <div class="grid md:grid-cols-3 gap-6 text-center mb-8">
                <div>
                    <i class="fas fa-map-marker-alt text-3xl text-brand-cyan mb-3"></i>
                    <h3 class="text-lg font-semibold text-white mb-2">Location</h3>
                    <p class="text-gray-300">
                        2860 South State Hwy 161<br>
                        STE160-211<br>
                        Grand Prairie, TX 75052
                    </p>
                </div>
                <div>
                    <i class="fas fa-phone text-3xl text-brand-cyan mb-3"></i>
                    <h3 class="text-lg font-semibold text-white mb-2">Phone</h3>
                    <p class="text-gray-300">
                        <a href="tel:6827013557" class="hover:text-brand-cyan transition-colors">
                            (682) 701-3557
                        </a>
                    </p>
                </div>
                <div>
                    <i class="fas fa-envelope text-3xl text-brand-cyan mb-3"></i>
                    <h3 class="text-lg font-semibold text-white mb-2">Email</h3>
                    <p class="text-gray-300">
                        <a href="mailto:contact@lifeischaracterradio.com" class="hover:text-brand-cyan transition-colors">
                            contact@lifeischaracterradio.com
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="flex justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>contact" class="bg-brand-cyan text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                    Contact Us
                </a>
                <a href="<?php echo BASE_URL; ?>episodes" class="border-2 border-brand-cyan text-brand-cyan px-6 py-3 rounded-lg font-semibold hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                    Listen Now
                </a>
                <a href="#free-devotional" class="border-2 border-brand-cyan text-brand-cyan px-6 py-3 rounded-lg font-semibold hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                    Get the Free Devotional
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
