# Phase 5 & 6 Testing Guide

## Quick Test Results ✅

All API endpoints tested and working:

```json
✅ API Health Check: http://localhost/tour_update/life/api/
✅ Events API: http://localhost/tour_update/life/api/events
✅ Donations API: http://localhost/tour_update/life/api/donations
✅ Episodes API: http://localhost/tour_update/life/api/episodes
```

---

## Complete Testing Checklist

### 1. API Endpoints Testing

**Events API**
```powershell
# Get upcoming events
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/events?status=upcoming" -UseBasicParsing

# Get past events  
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/events?status=past" -UseBasicParsing
```

**Donations API**
```powershell
# Get donation types
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/donations" -UseBasicParsing
```

**Blog API**
```powershell
# Get all blog posts
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/blog" -UseBasicParsing

# Get blog categories
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/blog/categories" -UseBasicParsing

# Filter by category
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/blog?category_id=1" -UseBasicParsing
```

**Episodes API**
```powershell
# Get all episodes
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/episodes" -UseBasicParsing

# Get episode topics
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/episodes/topics" -UseBasicParsing

# Filter by topic
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/episodes?topic_id=1" -UseBasicParsing
```

**Form Submission APIs**
```powershell
# Contact form
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/contact/submit" -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body '{"name":"Test User","email":"test@example.com","subject":"Test","message":"Testing contact form"}' `
    -UseBasicParsing

# Newsletter subscription
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/newsletter/subscribe" -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body '{"name":"Test User","email":"test@example.com"}' `
    -UseBasicParsing

# Prayer request
Invoke-WebRequest -Uri "http://localhost/tour_update/life/api/prayer/submit" -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body '{"name":"Test User","email":"test@example.com","prayer_request":"Please pray for me","is_anonymous":false}' `
    -UseBasicParsing
```

### 2. Frontend Pages Testing

**Open in Browser:**
- http://localhost/tour_update/life/ (Homepage)
- http://localhost/tour_update/life/episodes (Episodes page - should load dynamically)
- http://localhost/tour_update/life/blog (Blog page - should load dynamically)
- http://localhost/tour_update/life/events (Events page - should load dynamically)
- http://localhost/tour_update/life/donate (Donate page - should load donation types)
- http://localhost/tour_update/life/contact (Contact page)

**What to Check:**
- ✅ Content loads from API (not hardcoded)
- ✅ Filters work (topics, categories)
- ✅ Brand colors are visible (#00E5FF cyan, #001A2E navy, #FFD700 gold)
- ✅ Fonts are Cinzel for headings, Inter for body text
- ✅ Gradients appear (from-[#001A2E] to-[#006B8F])
- ✅ Hover effects work smoothly
- ✅ Loading spinners appear briefly

### 3. Payment Flow Testing (Requires Stripe Setup)

**Prerequisites:**
1. Add Stripe.js to header:
```html
<script src="https://js.stripe.com/v3/"></script>
```

2. Update `js/main.js` with your test key:
```javascript
const STRIPE_PUBLISHABLE_KEY = 'pk_test_YOUR_ACTUAL_TEST_KEY';
```

3. Update `includes/config.php` with your Stripe keys

**Test Free Event Registration:**
1. Navigate to http://localhost/tour_update/life/events
2. Log in as a user
3. Click "Register Free" on Character Building Workshop
4. Should auto-complete and show success message
5. Check database - event_registrations should have new row

**Test Paid Event Registration:**
1. Navigate to events page
2. Click "Register & Pay" on Youth Character Summit
3. Stripe payment modal should appear
4. Use test card: 4242 4242 4242 4242
5. Complete payment
6. Should redirect to event-success.php
7. Check database:
   - event_registrations status = 'completed'
   - transactions status = 'completed'
   - events current_registrations incremented

**Test Donation:**
1. Navigate to http://localhost/tour_update/life/donate
2. Click on a donation type
3. Fill in details
4. Use test card: 4242 4242 4242 4242
5. Complete payment
6. Should redirect to donation-success.php
7. Check database:
   - donations status = 'completed'
   - transactions created

### 4. Email Testing

**Check Mail Log:**
```powershell
Get-Content c:\xampp\htdocs\tour_update\life\logs\mail.log -Tail 50
```

**What Should Be Logged:**
- Contact form submissions → Admin notification
- Prayer requests → Admin notification
- Newsletter subscriptions → Welcome email
- Event registrations (after payment) → User confirmation + Admin notification
- Donations (after payment) → User confirmation + Admin notification

### 5. Database Verification

**Check Events:**
```sql
SELECT * FROM events ORDER BY event_date;
```

**Check Event Registrations:**
```sql
SELECT er.*, e.title, u.email 
FROM event_registrations er
JOIN events e ON er.event_id = e.id
JOIN users u ON er.user_id = u.id;
```

**Check Donations:**
```sql
SELECT * FROM donations ORDER BY donated_at DESC;
```

**Check Transactions:**
```sql
SELECT * FROM transactions ORDER BY created_at DESC;
```

### 6. Webhook Testing (Advanced)

**Local Testing with Stripe CLI:**
```bash
stripe listen --forward-to http://localhost/tour_update/life/api/webhook/stripe

# In another terminal:
stripe trigger payment_intent.succeeded
```

**What Should Happen:**
1. Webhook receives event
2. Signature verified
3. Transaction updated to 'completed'
4. Registration/donation updated
5. Email sent
6. Response 200 returned

---

## Expected Results Summary

### ✅ All APIs Return JSON
```json
{
  "success": true,
  "message": "Success",
  "data": [...]
}
```

### ✅ Frontend Uses Brand Colors
- Backgrounds: #001A2E, #006B8F gradients
- Accents: #00E5FF (cyan)
- Highlights: #FFD700 (gold)
- Fonts: Cinzel + Inter

### ✅ Payment Flow Complete
1. User action → API call
2. Payment intent created
3. Stripe modal → User pays
4. Webhook receives event
5. Database updated
6. Email sent
7. Success page displayed

### ✅ All Forms Submit
- Contact: Saves + emails admin
- Prayer: Saves + emails admin
- Newsletter: Saves + emails user

---

## Common Issues & Solutions

**API returns 404:**
- Check .htaccess exists in /api folder
- Verify mod_rewrite enabled in Apache

**Stripe payment doesn't work:**
- Add Stripe.js script to header
- Update STRIPE_PUBLISHABLE_KEY in main.js
- Check Stripe keys in config.php

**Frontend doesn't load data:**
- Check browser console for errors
- Verify API_BASE path in main.js
- Check database has seeded data

**Webhooks fail:**
- Verify STRIPE_WEBHOOK_SECRET in config
- Check webhook endpoint in Stripe dashboard
- Ensure signature verification working

**Emails not logging:**
- Check logs folder exists and is writable
- Verify SEND_EMAILS = false in config for local
- Check mail.php functions are called

---

## Next Steps

1. ✅ **Test all API endpoints** - Use PowerShell commands above
2. ✅ **Test frontend pages** - Open in browser, check data loads
3. ⏳ **Setup Stripe** - Add keys, test payment flow
4. ⏳ **Test webhooks** - Use Stripe CLI or trigger test events
5. ⏳ **Verify emails** - Check mail log after actions

**When ready for production:**
- Set ENVIRONMENT = 'production' in config
- Update Stripe keys to live keys
- Set SEND_EMAILS = true
- Configure Stripe webhook URL
- SSL certificate required

---

**Status**: ✅ Phase 5 & 6 Complete - Ready for Testing!
