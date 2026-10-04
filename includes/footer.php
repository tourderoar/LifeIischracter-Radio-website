<?php
/**
 * Life is Character Radio - Footer Component
 * 
 * Location: /includes/footer.php
 * 
 * Common footer for all public pages
 */

// Ensure config is loaded
if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';
?>
    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Contact Info -->
                <div>
                    <div class="flex items-center mb-6">
                        <img src="<?= BASE_URL ?>logo.png" alt="<?= SITE_NAME ?>" class="h-10 w-10 mr-3">
                        <span class="font-bold text-xl text-brand-cyan"><?= SITE_NAME ?></span>
                    </div>
                    <div class="space-y-3 text-gray-400">
                        <p><i class="fas fa-map-marker-alt mr-3 text-brand-cyan"></i>2860 South State Hwy 161<br>Ste 160 211, Grand Prairie, TX 75052</p>
                        <p><i class="fas fa-phone mr-3 text-brand-cyan"></i>(682) 701-3557</p>
                        <p><i class="fas fa-envelope mr-3 text-brand-cyan"></i><a href="mailto:contact@lifeischaracterradio.com" class="hover:text-brand-cyan">contact@lifeischaracterradio.com</a></p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?= BASE_URL ?>" class="hover:text-brand-cyan transition-colors">Home</a></li>
                        <li><a href="<?= BASE_URL ?>about" class="hover:text-brand-cyan transition-colors">About Us</a></li>
                        <li><a href="<?= BASE_URL ?>episodes" class="hover:text-brand-cyan transition-colors">Episodes</a></li>
                        <li><a href="<?= BASE_URL ?>blog" class="hover:text-brand-cyan transition-colors">Blog</a></li>
                        <li><a href="<?= BASE_URL ?>events" class="hover:text-brand-cyan transition-colors">Events</a></li>
                        <li><a href="<?= BASE_URL ?>donate" class="hover:text-brand-cyan transition-colors">Donate</a></li>
                        <li><a href="<?= BASE_URL ?>contact" class="hover:text-brand-cyan transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4">Connect With Us</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="https://www.tiktok.com/@lifeischaracter" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-tiktok text-xl"></i>
                        </a>
                        <a href="https://www.instagram.com/lifeischaracterradio" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="https://www.facebook.com/share/16gqTB1Eag" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="https://www.linkedin.com/in/kumeni-agbo-233401359" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="https://pin.it/3w8zparRv" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-pinterest text-xl"></i>
                        </a>
                        <a href="https://www.youtube.com/@lifeischaracterradio959" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                        <a href="https://x.com/Lifeischaracter" target="_blank" class="bg-gray-800 text-brand-cyan p-3 rounded-lg hover:bg-brand-cyan hover:text-gray-900 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    </div>
                    
                    <div class="mt-6">
                        <p class="text-gray-400 mb-2">Listen & Watch:</p>
                        <div class="flex gap-2">
                            <span class="bg-gray-800 text-brand-cyan px-3 py-1 rounded text-sm">Radio</span>
                            <span class="bg-gray-800 text-brand-cyan px-3 py-1 rounded text-sm">Television</span>
                            <span class="bg-gray-800 text-brand-cyan px-3 py-1 rounded text-sm">Podcast</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                <p class="mt-2 text-brand-cyan font-semibold"><?= SITE_TAGLINE ?></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js"></script>
    <script src="<?= BASE_URL ?>js/main.js?v=<?= filemtime(ABSPATH . 'js/main.js') ?>"></script>

</body>
</html>
