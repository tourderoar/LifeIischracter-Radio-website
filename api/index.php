<?php
/**
 * Life is Character Radio - API Documentation & Health Check
 * Location: /api/index.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/response.php';

header('Content-Type: application/json');

jsonSuccess([
    'name' => 'Life is Character Radio API',
    'version' => '1.0.0',
    'status' => 'operational',
    'endpoints' => [
        'Events' => [
            'GET /api/events' => 'Get all published events',
            'POST /api/events/register' => 'Register for event (auth required)'
        ],
        'Donations' => [
            'GET /api/donations' => 'Get active donation types',
            'POST /api/donations/create' => 'Create donation'
        ],
        'Blog' => [
            'GET /api/blog' => 'Get published blog posts',
            'GET /api/blog/categories' => 'Get active blog categories'
        ],
        'Episodes' => [
            'GET /api/episodes' => 'Get published episodes',
            'GET /api/episodes/topics' => 'Get active episode topics'
        ],
        'Forms' => [
            'POST /api/contact/submit' => 'Submit contact form',
            'POST /api/prayer/submit' => 'Submit prayer request',
            'POST /api/newsletter/subscribe' => 'Subscribe to newsletter'
        ],
        'Payments' => [
            'POST /api/payments/create-intent' => 'Create payment intent (auth required)',
            'POST /api/webhook/stripe' => 'Stripe webhook handler'
        ]
    ]
], 'API is running');
