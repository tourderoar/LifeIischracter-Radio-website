# Quick Testing Guide - Phase 4 & 7

## Testing Order (Recommended)

### 1. Registration Flow
```
1. Visit: http://localhost/life/account/register
2. Fill form with test data
3. Click "Create Account"
4. Check logs/emails.log for activation email
5. Copy activation token from email
6. Visit: http://localhost/life/account/activate?token=YOUR_TOKEN
7. Should auto-login and redirect to dashboard
```

### 2. Login/Logout
```
1. Logout from dashboard
2. Visit: http://localhost/life/account/login
3. Login with test credentials
4. Check dashboard loads correctly
5. Click logout from user menu
6. Verify redirected to homepage
```

### 3. Password Reset
```
1. Visit: http://localhost/life/account/forgot-password
2. Enter registered email
3. Check logs/emails.log for reset link
4. Copy reset token
5. Visit: http://localhost/life/account/reset-password?token=YOUR_TOKEN
6. Enter new password
7. Should auto-login
```

### 4. Profile Management
```
1. Login and go to profile
2. Update name and phone
3. Click "Update Profile"
4. Change password
5. Verify changes saved
```

### 5. Navigation Testing
```
1. When logged out: Should see Login + Register buttons
2. When logged in: Should see user dropdown with name
3. Desktop: Hover over name to see dropdown menu
4. Mobile: Click hamburger to see mobile menu
5. All links should work correctly
```

## Common Test Data

**Test User 1:**
```
Email: test@example.com
Password: TestPass123
Name: John Doe
Phone: (555) 123-4567
```

**Test User 2:**
```
Email: jane@example.com
Password: SecurePass456
Name: Jane Smith
Phone: (555) 987-6543
```

## Email Log Location
```
c:\xampp\htdocs\tour_update\life\logs\emails.log
```

## Expected Results

✅ All pages load without errors  
✅ Forms validate correctly  
✅ Emails logged to file  
✅ Tokens work and expire properly  
✅ Sessions maintained  
✅ Navigation shows correct state  
✅ Brand colors display (cyan/navy)  
✅ Responsive on mobile  

## Troubleshooting

**Issue:** Can't find email log  
**Fix:** Check if logs/ folder exists and is writable

**Issue:** Navigation not changing after login  
**Fix:** Clear browser cache and refresh

**Issue:** Token not working  
**Fix:** Check token hasn't expired (24h for activation, 1h for reset)

**Issue:** Can't login after registration  
**Fix:** Account must be activated first

---

**Ready to test!** Start with registration and work through the flow.
