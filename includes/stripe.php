<?php
/**
 * Life is Character Radio - Stripe Helper Functions
 * 
 * Location: /includes/stripe.php
 * 
 * Stripe SDK initialization and helper functions for payments
 */

// Include guard
if (defined('LICR_STRIPE_LOADED')) return;
define('LICR_STRIPE_LOADED', true);

// Ensure config is loaded
if (!defined('STRIPE_SECRET_KEY')) require_once __DIR__ . '/config.php';

// Load Composer autoload for Stripe SDK
require_once ABSPATH . 'vendor/autoload.php';

// Initialize Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if (!function_exists('createPaymentIntent')) {
function createPaymentIntent(int $amount, string $currency = 'usd', array $metadata = []): \Stripe\PaymentIntent {
    return \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => $currency,
        'metadata' => $metadata,
        'automatic_payment_methods' => ['enabled' => true]
    ]);
}
}

if (!function_exists('createStripeCustomer')) {
function createStripeCustomer(string $email, ?string $name = null): \Stripe\Customer {
    $data = ['email' => $email];
    if ($name) $data['name'] = $name;
    
    return \Stripe\Customer::create($data);
}
}

if (!function_exists('createSubscription')) {
function createSubscription(string $customerId, int $amount, string $interval = 'month'): \Stripe\Subscription {
    // Create price
    $price = \Stripe\Price::create([
        'unit_amount' => $amount,
        'currency' => 'usd',
        'recurring' => ['interval' => $interval],
        'product_data' => ['name' => 'Recurring Donation']
    ]);
    
    // Create subscription
    return \Stripe\Subscription::create([
        'customer' => $customerId,
        'items' => [['price' => $price->id]],
        'expand' => ['latest_invoice.payment_intent']
    ]);
}
}

if (!function_exists('verifyStripeWebhook')) {
function verifyStripeWebhook(string $payload, string $signature): \Stripe\Event|false {
    try {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            STRIPE_WEBHOOK_SECRET
        );
    } catch (\Exception $e) {
        error_log("Stripe Webhook Error: " . $e->getMessage());
        return false;
    }
}
}

if (!function_exists('getPaymentIntent')) {
function getPaymentIntent(string $paymentIntentId): ?\Stripe\PaymentIntent {
    try {
        return \Stripe\PaymentIntent::retrieve($paymentIntentId);
    } catch (\Exception $e) {
        error_log("Stripe Error: " . $e->getMessage());
        return null;
    }
}
}

if (!function_exists('cancelSubscription')) {
function cancelSubscription(string $subscriptionId): bool {
    try {
        \Stripe\Subscription::update($subscriptionId, ['cancel_at_period_end' => true]);
        return true;
    } catch (\Exception $e) {
        error_log("Stripe Error: " . $e->getMessage());
        return false;
    }
}
}

if (!function_exists('toStripeCents')) {
function toStripeCents(float $amount): int {
    return (int)round($amount * 100);
}
}

if (!function_exists('fromStripeCents')) {
function fromStripeCents(int $cents): float {
    return $cents / 100;
}
}
