# Payment Integration Testing Guide

## Overview
This document outlines the testing procedures for event registration and donation payment features integrated with Stripe.

## Prerequisites

### 1. Stripe Configuration
- Stripe test keys are already configured in `includes/config.php`
- Publishable Key: `pk_test_51SApMh2IYzzLexMC...`
- Secret Key: `sk_test_51SApMh2IYzzLexMC...`

### 2. Test Card Numbers
Use these Stripe test cards for testing:

**Successful Payment:**
- Card: `4242 4242 4242 4242`
- Expiry: Any future date (e.g., 12/25)
- CVC: Any 3 digits (e.g., 123)
- ZIP: Any 5 digits (e.g., 12345)

**Card Declined:**
- Card: `4000 0000 0000 0002`

**Requires Authentication (3D Secure):**
- Card: `4000 0025 0000 3155`

### 3. Database Setup
Ensure these tables exist:
- `events` - With test events (both free and paid)
- `event_registrations` - For storing registrations
- `donations` - For storing donations
- `transactions` - For tracking payments
- `users` - User must be logged in for event registration

## Testing Scenarios

### A. Event Registration Testing

#### Test 1: Free Event Registration
**Prerequisites:** User must be logged in

1. Navigate to `/events.php`
2. Find a free event (Cost: $0.00)
3. Click "Register Now" button
4. Confirm the registration prompt
5. **Expected Results:**
   - Success notification appears
   - User is registered immediately (no payment)
   - Event capacity counter updates
   - Email confirmation sent to user

#### Test 2: Paid Event Registration
**Prerequisites:** User must be logged in

1. Navigate to `/events.php`
2. Find a paid event (e.g., Cost: $50.00)
3. Click "Register & Pay" button
4. Confirm the registration prompt
5. Payment modal appears with Stripe Elements
6. Enter test card information (4242 4242 4242 4242)
7. Click "Pay & Register"
8. **Expected Results:**
   - Loading spinner shows during processing
   - Payment processes successfully
   - Redirects to `/event-success.php`
   - Database records:
     - `event_registrations`: `payment_status = 'completed'`
     - `transactions`: `payment_status = 'completed'`
   - Email confirmation sent to user
   - Admin notification sent

#### Test 3: Event at Capacity
1. Navigate to event with `max_capacity` reached
2. **Expected Results:**
   - "Event Full" badge shown
   - Register button disabled
   - Cannot proceed with registration

#### Test 4: Already Registered
1. Attempt to register for same event twice
2. **Expected Results:**
   - API returns error: "Already registered for this event"
   - Notification shown to user

#### Test 5: Declined Card
1. Attempt paid event registration
2. Use declined test card: `4000 0000 0000 0002`
3. **Expected Results:**
   - Error notification appears
   - Registration remains pending or cancelled
   - User can retry

### B. Donation Testing

#### Test 6: One-Time Donation (Anonymous)
1. Navigate to `/donate.php`
2. Select quick tier (e.g., $25, $50, $100) OR enter custom amount
3. Select "One-time" frequency
4. Check "Make this donation anonymous"
5. Enter card information
6. Click "Donate Now"
7. **Expected Results:**
   - Payment processes successfully
   - Redirects to `/donation-success.php`
   - Database records:
     - `donations`: `is_anonymous = 1`, `payment_status = 'completed'`
     - `transactions`: `transaction_type = 'donation'`
   - Thank you email sent (if email provided)
   - Admin notification sent

#### Test 7: One-Time Donation (With Details)
1. Navigate to `/donate.php`
2. Enter custom amount
3. Enter donor name and email
4. Optionally add message
5. Complete payment with test card
6. **Expected Results:**
   - Payment successful
   - Donor details saved in database
   - Receipt email sent to donor email
   - Admin notification includes donor details

#### Test 8: Recurring Donation Attempt
1. Select "Monthly", "Quarterly", or "Yearly" frequency
2. **Expected Results:**
   - API returns: "Recurring donations not yet implemented"
   - User notified to use one-time donation

#### Test 9: Invalid Amount
1. Try to donate $0 or negative amount
2. **Expected Results:**
   - Client-side validation prevents submission
   - Error message: "Please enter a donation amount of at least $1"

#### Test 10: Payment Declined
1. Attempt donation with declined card
2. **Expected Results:**
   - Error notification appears
   - Donation record remains pending
   - User can retry with different card

### C. Webhook Testing

#### Test 11: Stripe Webhook Handler
**Prerequisites:** Webhook endpoint configured in Stripe Dashboard

1. Trigger payment in test mode
2. Stripe sends webhook to `/api/webhook/stripe.php`
3. **Expected Results:**
   - Webhook verifies signature
   - Updates `event_registrations` or `donations` table
   - Updates `transactions` table
   - Sends confirmation emails
   - Returns 200 status to Stripe

**Manual Webhook Test:**
```bash
# Using Stripe CLI
stripe listen --forward-to http://localhost/tour_update/life/api/webhook/stripe.php

# Trigger test event
stripe trigger payment_intent.succeeded
```

### D. UI/UX Testing

#### Test 12: Stripe Elements Styling
1. Check payment form on both pages
2. **Verify:**
   - Card element matches brand colors
   - Placeholder text visible
   - Error messages appear in red
   - Accessible keyboard navigation

#### Test 13: Loading States
1. Submit payment and observe
2. **Verify:**
   - Button text changes to "Processing..."
   - Spinner icon appears
   - Button becomes disabled
   - Cannot double-submit

#### Test 14: Error Handling
1. Try various error scenarios
2. **Verify:**
   - Clear error messages shown
   - Notification appears at top-right
   - Auto-dismisses after 5 seconds
   - Button re-enables after error

#### Test 15: Success Pages
1. Complete successful payment
2. **Verify Event Success Page:**
   - Check circle icon
   - Event details shown
   - Links to dashboard and more events
   - Requires user authentication
3. **Verify Donation Success Page:**
   - Heart icon
   - Thank you message
   - Scripture quote
   - Links work correctly
   - Works for both logged-in and anonymous users

#### Test 16: Payment Cancel Page
1. Navigate to `/payment-cancel.php` manually
2. **Verify:**
   - Appropriate messaging
   - Links to retry (events/donate pages)
   - Contact us option available

## API Endpoint Testing

### Event Registration API
```bash
# Test event registration (requires auth cookie)
curl -X POST http://localhost/tour_update/life/api/events/register/ \
  -H "Content-Type: application/json" \
  -d '{"event_id": 1}'
```

**Expected Response (Free Event):**
```json
{
  "success": true,
  "message": "Successfully registered for event!",
  "data": {"status": "completed"}
}
```

**Expected Response (Paid Event):**
```json
{
  "success": true,
  "message": "Payment required to complete registration",
  "data": {
    "status": "pending",
    "client_secret": "pi_xxx_secret_xxx",
    "amount": 50.00
  }
}
```

### Donation API
```bash
# Test donation creation
curl -X POST http://localhost/tour_update/life/api/donations/create/ \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 25.00,
    "donation_frequency": "one-time",
    "is_anonymous": 0,
    "donor_name": "Test Donor",
    "donor_email": "test@example.com",
    "message": "God bless this ministry"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Payment required to complete donation",
  "data": {
    "client_secret": "pi_xxx_secret_xxx",
    "donation_id": 123
  }
}
```

## Database Validation

### After Successful Event Registration
```sql
-- Check registration record
SELECT * FROM event_registrations 
WHERE event_id = ? AND user_id = ? 
ORDER BY registered_at DESC LIMIT 1;

-- Verify payment_status = 'completed'
-- Verify payment_intent_id is present for paid events
-- Verify amount_paid matches event cost

-- Check transaction
SELECT * FROM transactions 
WHERE transaction_type = 'event_registration' 
AND reference_id = ? -- registration ID
ORDER BY created_at DESC LIMIT 1;

-- Verify event capacity updated
SELECT current_registrations FROM events WHERE id = ?;
```

### After Successful Donation
```sql
-- Check donation record
SELECT * FROM donations 
WHERE payment_intent_id = ? 
ORDER BY donated_at DESC LIMIT 1;

-- Verify payment_status = 'completed'
-- Verify is_anonymous flag
-- Verify donor details (if not anonymous)

-- Check transaction
SELECT * FROM transactions 
WHERE transaction_type = 'donation' 
AND payment_intent_id = ?
ORDER BY created_at DESC LIMIT 1;
```

## Email Testing

### Email Notifications to Test
1. **Event Registration Confirmation** (to user)
2. **Event Registration Notification** (to admins)
3. **Donation Thank You** (to donor, if email provided)
4. **Donation Notification** (to admins)

### Email Testing Configuration
Check `includes/config.php`:
```php
define('SEND_EMAILS', false); // Set to true for actual sending
```

When `SEND_EMAILS = false`, emails are logged to `logs/email.log` instead of being sent.

## Security Testing

### Test 17: Webhook Signature Verification
1. Send invalid webhook signature
2. **Expected:** Request rejected with 400 error

### Test 18: Authentication Requirements
1. Try event registration without login
2. **Expected:** Redirected to login page or API returns 401

### Test 19: SQL Injection Prevention
1. Try malicious input in form fields
2. **Expected:** Prepared statements prevent injection

### Test 20: XSS Prevention
1. Enter script tags in message fields
2. **Expected:** Content sanitized before display

## Performance Testing

### Test 21: Payment Processing Time
- Measure time from submit to redirect
- **Target:** < 3 seconds for successful payment

### Test 22: Concurrent Registrations
- Multiple users register for limited capacity event
- **Expected:** Proper capacity handling, no overselling

## Production Readiness Checklist

Before going live:

- [ ] Replace test Stripe keys with live keys in production
- [ ] Configure Stripe webhook endpoint in production
- [ ] Set `SEND_EMAILS = true` for production
- [ ] Verify HTTPS is enabled (required for Stripe)
- [ ] Test webhook signature verification
- [ ] Review email templates and branding
- [ ] Set up error logging and monitoring
- [ ] Test refund process (if needed)
- [ ] Document customer support procedures
- [ ] Prepare tax receipt template (for donations)
- [ ] Review privacy policy and terms

## Troubleshooting

### Issue: "Payment system not initialized"
**Solution:** Check that Stripe.js script loads in header and STRIPE_PUBLISHABLE_KEY is defined

### Issue: Webhook not receiving events
**Solution:** 
1. Verify webhook URL in Stripe Dashboard
2. Check webhook signing secret matches config
3. Use Stripe CLI for local testing: `stripe listen --forward-to http://localhost/...`

### Issue: Email not sending
**Solution:** Check `SEND_EMAILS` flag and email configuration in config.php

### Issue: Card element not showing
**Solution:** Check browser console for JavaScript errors, verify Stripe.js loaded

## Support Resources

- Stripe Testing Cards: https://stripe.com/docs/testing
- Stripe Webhook Testing: https://stripe.com/docs/webhooks/test
- Stripe Elements Documentation: https://stripe.com/docs/payments/elements
- Stripe PHP SDK: https://github.com/stripe/stripe-php

---

**Testing Date:** ___________  
**Tested By:** ___________  
**Environment:** Local Development / Staging / Production  
**Status:** ⬜ Pass ⬜ Fail ⬜ Needs Review
