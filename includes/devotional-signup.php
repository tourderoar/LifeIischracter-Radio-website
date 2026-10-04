<?php
/**
 * Life is Character Radio - Free 7-Day Devotional signup block
 *
 * Location: /includes/devotional-signup.php
 *
 * Shared funnel block used on the homepage, episodes page and about page.
 * Posts to /api/newsletter/subscribe with source "devotional"; the submit
 * handler lives in js/main.js and binds to the .devotional-form class, so the
 * section id stays unique for the #free-devotional anchor on the about page.
 *
 * Layout: copy and form sit side by side from lg up, stacking on smaller
 * screens so the long button label never squeezes the inputs.
 */
?>
<!-- Free 7-Day Devotional Signup -->
<section id="free-devotional" class="py-16 bg-gray-800">
    <div class="max-w-5xl mx-auto px-4">
        <div class="bg-gray-900 border border-brand-cyan/30 rounded-xl p-8 md:p-12">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">

                <!-- Copy -->
                <div>
                    <p class="text-sm font-semibold tracking-widest uppercase text-brand-cyan mb-3">Free 7-day devotional</p>
                    <h2 class="text-3xl md:text-4xl font-bold font-cinematic mb-4 leading-tight">
                        Life is a Script: 7 Days to Living with Character
                    </h2>
                    <p class="text-gray-300 mb-6">
                        Five minutes a day. One scripture, a short reflection, one action step and a prayer, sent straight to your inbox.
                    </p>
                    <ul class="text-gray-300 space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check text-brand-cyan mt-1 mr-3"></i>Read God's Word as the script for your life
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-brand-cyan mt-1 mr-3"></i>Build integrity when no one is watching
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-brand-cyan mt-1 mr-3"></i>Rise again after failure and temptation
                        </li>
                    </ul>
                </div>

                <!-- Signup panel -->
                <div class="bg-gray-800/60 border border-gray-700 rounded-lg p-6 md:p-8">
                    <form class="devotional-form space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2" for="devotional-first-name">First name</label>
                            <input type="text" name="firstName" id="devotional-first-name" placeholder="Your first name"
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-brand-cyan">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2" for="devotional-email">Email</label>
                            <input type="email" name="email" id="devotional-email" placeholder="you@example.com" required
                                   class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-brand-cyan">
                        </div>
                        <button type="submit"
                                class="w-full bg-brand-cyan text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-brand-blue transition-colors">
                            Send Me the Free Devotional
                        </button>
                    </form>
                    <p class="text-gray-400 text-sm mt-4 text-center">We respect your inbox. Unsubscribe anytime.</p>
                </div>

            </div>
        </div>
    </div>
</section>
