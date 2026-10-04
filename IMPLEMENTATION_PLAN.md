# Life is Character Radio - PHP Conversion Implementation Plan

**Project**: Life is Character Radio Website  
**Type**: Static HTML to Dynamic PHP Conversion  
**Database**: MySQL  
**Tech Stack**: PHP, MySQL, HTML5, CSS3, JavaScript/jQuery, Stripe, Zeptomail  
**Start Date**: 2026-07-21  

---

## Project Overview

Converting the Life is Character Radio static website to a dynamic PHP-based system with:
- Admin portal for content management
- User portal for registration and payments
- REST API for frontend consumption
- Stripe payment integration
- Email notifications via Zeptomail
- Clean URL routing via .htaccess

**Project Goals**:
- Fast load/response times
- Premium, sophisticated UI/UX (spa in Switzerland level)
- Clean, well-commented code
- No hardcoded URLs (all from config)
- Standalone project (no dependencies on parent tour_update)

---

## Database Schema

### Tables Overview

#### 1. **roles**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 100, UNIQUE) -- e.g., "Super Admin", "Content Manager", "Event Coordinator"
- description (TEXT, NULL)
- permissions (JSON) -- ["manage_events", "manage_donations", "manage_blog", "manage_episodes", "manage_users", "manage_admins"]
- is_active (TINYINT, DEFAULT 1)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 2. **admins**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- role_id (INT, FOREIGN KEY roles.id)
- username (VARCHAR 100, UNIQUE)
- email (VARCHAR 255, UNIQUE)
- password (VARCHAR 255) -- hashed
- full_name (VARCHAR 255)
- is_active (TINYINT, DEFAULT 1)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 3. **users**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- email (VARCHAR 255, UNIQUE)
- password (VARCHAR 255) -- hashed
- full_name (VARCHAR 255)
- phone (VARCHAR 20, NULL)
- is_active (TINYINT, DEFAULT 0) -- activated via email
- activation_token (VARCHAR 64, NULL)
- activation_expires_at (DATETIME, NULL)
- reset_token (VARCHAR 64, NULL)
- reset_expires_at (DATETIME, NULL)
- created_at (DATETIME)
- updated_at (DATETIME)
- last_login (DATETIME, NULL)
```

#### 4. **events**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR 255)
- description (TEXT)
- featured_image (VARCHAR 255, NULL)
- event_date (DATETIME)
- location (VARCHAR 255)
- cost (DECIMAL 10,2, DEFAULT 0.00) -- 0 for free events
- status (ENUM: 'upcoming', 'past', 'cancelled', DEFAULT 'upcoming')
- max_capacity (INT, NULL) -- optional capacity limit
- current_registrations (INT, DEFAULT 0)
- created_by (INT, FOREIGN KEY admins.id)
- created_at (DATETIME)
- updated_at (DATETIME)
- published_at (DATETIME, NULL)
```

#### 5. **event_registrations**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- event_id (INT, FOREIGN KEY events.id)
- user_id (INT, FOREIGN KEY users.id)
- payment_status (ENUM: 'pending', 'completed', 'failed', 'refunded')
- payment_intent_id (VARCHAR 255, NULL) -- Stripe payment intent ID
- amount_paid (DECIMAL 10,2)
- registered_at (DATETIME)
- payment_completed_at (DATETIME, NULL)
- UNIQUE KEY (event_id, user_id)
```

#### 6. **episode_topics**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 100, UNIQUE) -- e.g., "Character", "Faith", "Scripture", "Life"
- slug (VARCHAR 100, UNIQUE)
- description (TEXT, NULL)
- is_active (TINYINT, DEFAULT 1)
- sort_order (INT, DEFAULT 0)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 7. **blog_categories**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 100, UNIQUE) -- e.g., "Devotional", "Character", "Faith"
- slug (VARCHAR 100, UNIQUE)
- description (TEXT, NULL)
- is_active (TINYINT, DEFAULT 1)
- sort_order (INT, DEFAULT 0)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 8. **donation_types**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 100) -- e.g., "Supporter", "Champion"
- description (TEXT, NULL)
- suggested_amount (DECIMAL 10,2, NULL)
- is_active (TINYINT, DEFAULT 1)
- sort_order (INT, DEFAULT 0)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 9. **donations**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY users.id, NULL) -- can be anonymous
- donation_type_id (INT, FOREIGN KEY donation_types.id, NULL)
- amount (DECIMAL 10,2)
- donation_frequency (ENUM: 'one-time', 'monthly', 'quarterly', 'yearly')
- is_anonymous (TINYINT, DEFAULT 0)
- donor_name (VARCHAR 255, NULL)
- donor_email (VARCHAR 255, NULL)
- message (TEXT, NULL)
- payment_status (ENUM: 'pending', 'completed', 'failed', 'refunded')
- payment_intent_id (VARCHAR 255, NULL)
- stripe_customer_id (VARCHAR 255, NULL) -- for recurring donations
- stripe_subscription_id (VARCHAR 255, NULL) -- for recurring donations
- donated_at (DATETIME)
- payment_completed_at (DATETIME, NULL)
```

#### 10. **blog_posts**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR 255)
- slug (VARCHAR 255, UNIQUE)
- content (LONGTEXT)
- excerpt (TEXT, NULL)
- featured_image (VARCHAR 255, NULL)
- scripture_reference (VARCHAR 255, NULL)
- tags (JSON, NULL) -- ["faith", "character"] - free-form tags
- author_name (VARCHAR 255, DEFAULT 'Paul Kumeni Agbo')
- status (ENUM: 'draft', 'published', DEFAULT 'draft')
- created_by (INT, FOREIGN KEY admins.id)
- published_at (DATETIME, NULL)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 11. **blog_post_categories**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- blog_post_id (INT, FOREIGN KEY blog_posts.id, ON DELETE CASCADE)
- category_id (INT, FOREIGN KEY blog_categories.id, ON DELETE CASCADE)
- UNIQUE KEY (blog_post_id, category_id)
```

#### 12. **episodes**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR 255)
- description (TEXT)
- youtube_url (VARCHAR 255)
- thumbnail_url (VARCHAR 255, NULL)
- scripture_reference (VARCHAR 255, NULL)
- topic_id (INT, FOREIGN KEY episode_topics.id, NULL)
- episode_number (INT, NULL)
- duration (INT, NULL) -- in seconds
- status (ENUM: 'draft', 'published', 'upcoming', DEFAULT 'draft')
- created_by (INT, FOREIGN KEY admins.id)
- published_at (DATETIME, NULL)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### 13. **contact_submissions**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 255)
- email (VARCHAR 255)
- subject (VARCHAR 255)
- message (TEXT)
- ip_address (VARCHAR 45, NULL)
- status (ENUM: 'new', 'read', 'responded', DEFAULT 'new')
- submitted_at (DATETIME)
- responded_at (DATETIME, NULL)
```

#### 14. **prayer_requests**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 255, NULL)
- email (VARCHAR 255, NULL)
- request (TEXT)
- is_anonymous (TINYINT, DEFAULT 0)
- ip_address (VARCHAR 45, NULL)
- status (ENUM: 'new', 'praying', 'answered', DEFAULT 'new')
- submitted_at (DATETIME)
```

#### 15. **newsletter_subscribers**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 255, NULL)
- email (VARCHAR 255, UNIQUE)
- is_active (TINYINT, DEFAULT 1)
- subscribed_at (DATETIME)
- unsubscribed_at (DATETIME, NULL)
```

#### 16. **transactions**
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY users.id, NULL)
- transaction_type (ENUM: 'event_registration', 'donation')
- reference_id (INT, NULL) -- event_registrations.id or donations.id
- amount (DECIMAL 10,2)
- payment_method (VARCHAR 50, DEFAULT 'stripe')
- payment_intent_id (VARCHAR 255, NULL)
- payment_status (ENUM: 'pending', 'completed', 'failed', 'refunded')
- stripe_webhook_verified (TINYINT, DEFAULT 0)
- created_at (DATETIME)
- completed_at (DATETIME, NULL)
```

---

## Folder Structure

```
life/
├── .htaccess                    # Root URL rewriting
├── index.php                    # Homepage
├── about.php                    # About page
├── episodes.php                 # Episodes page
├── blog.php                     # Blog page
├── contact.php                  # Contact page
├── donate.php                   # Donate page
├── events.php                   # Events page (NEW)
├── includes/
│   ├── config.php              # Configuration file (DB, Stripe, Zeptomail, base URL)
│   ├── header.php              # Common header/navbar
│   ├── footer.php              # Common footer
│   ├── db.php                  # Database connection
│   ├── auth.php                # Authentication helpers
│   ├── mail.php                # Email sending functions (Zeptomail)
│   ├── stripe.php              # Stripe helper functions
│   ├── upload.php              # Image upload handler
│   └── response.php            # JSON response helpers
├── admin/
│   ├── .htaccess               # Admin URL rewriting
│   ├── index.php               # Admin dashboard
│   ├── login.php               # Admin login
│   ├── logout.php              # Admin logout
│   ├── events.php              # Manage events
│   ├── donations.php           # Manage donation types
│   ├── blog.php                # Manage blog posts (includes categories modal)
│   ├── episodes.php            # Manage episodes (includes topics modal)
│   ├── users.php               # Manage users
│   ├── admins.php              # Manage admins
│   ├── roles.php               # Manage roles & permissions
│   ├── contact.php             # View contact submissions
│   ├── prayers.php             # View prayer requests
│   ├── newsletter.php          # View newsletter subscribers
│   ├── transactions.php        # View all transactions
│   ├── profile.php             # Admin profile
│   └── includes/
│       ├── admin_header.php    # Admin header
│       └── admin_footer.php    # Admin footer
├── account/
│   ├── .htaccess               # User account URL rewriting
│   ├── register.php            # User registration
│   ├── login.php               # User login
│   ├── logout.php              # User logout
│   ├── activate.php            # Account activation
│   ├── resend-activation.php   # Resend activation email
│   ├── forgot-password.php     # Forgot password
│   ├── reset-password.php      # Reset password
│   ├── dashboard.php           # User dashboard
│   ├── profile.php             # User profile
│   └── transactions.php        # User transactions history
├── api/
│   ├── .htaccess               # API URL rewriting
│   ├── index.php               # API documentation/health check
│   ├── events/
│   │   ├── index.php           # GET all events
│   │   └── register.php        # POST register for event
│   ├── donations/
│   │   ├── index.php           # GET donation types
│   │   └── create.php          # POST create donation
│   ├── blog/
│   │   ├── index.php           # GET blog posts
│   │   └── categories.php      # GET blog categories
│   ├── episodes/
│   │   ├── index.php           # GET episodes
│   │   └── topics.php          # GET episode topics
│   ├── contact/
│   │   └── submit.php          # POST contact form
│   ├── prayer/
│   │   └── submit.php          # POST prayer request
│   ├── newsletter/
│   │   └── subscribe.php       # POST newsletter signup
│   ├── payments/
│   │   ├── create-intent.php   # POST create Stripe payment intent
│   │   └── webhook.php         # POST Stripe webhook handler
│   └── auth/
│       ├── register.php        # POST user registration
│       ├── login.php           # POST user login
│       ├── activate.php        # POST/GET account activation
│       └── reset-password.php  # POST password reset
├── uploads/
│   ├── events/                 # Event featured images
│   ├── blog/                   # Blog featured images
│   └── .htaccess               # Prevent PHP execution in uploads
├── setup/
│   ├── database.sql            # Complete database schema
│   ├── seed.php                # Seed data for testing
│   └── install.php             # Installation wizard
├── css/
│   └── styles.css              # Custom styles (existing)
├── js/
│   ├── main.js                 # Main JavaScript (existing)
│   └── admin.js                # Admin-specific JavaScript (NEW)
└── static/
    ├── app.js                  # Frontend app (existing, will be updated)
    └── styles.css              # Frontend styles (existing)
```

---

## API Endpoints Summary

### Public Endpoints (No Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/events` | Get all published events (with filters) |
| GET | `/api/donations` | Get active donation types |
| GET | `/api/blog` | Get published blog posts (with filters) |
| GET | `/api/blog/categories` | Get active blog categories |
| GET | `/api/episodes` | Get published episodes (with filters) |
| GET | `/api/episodes/topics` | Get active episode topics |
| POST | `/api/contact/submit` | Submit contact form |
| POST | `/api/prayer/submit` | Submit prayer request |
| POST | `/api/newsletter/subscribe` | Subscribe to newsletter |
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | User login |
| GET/POST | `/api/auth/activate` | Activate user account |
| POST | `/api/auth/reset-password` | Request/complete password reset |

### Authenticated User Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/events/register` | Register for paid/free event |
| POST | `/api/donations/create` | Make a donation |
| POST | `/api/payments/create-intent` | Create Stripe payment intent |
| GET | `/api/user/transactions` | Get user transaction history |

### Webhook Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/payments/webhook` | Stripe webhook handler |

---

## Implementation Phases

Each phase is independently testable and does not require future phases to verify functionality.

---

## PHASE 1: Foundation & Infrastructure

**Goal**: Set up the core infrastructure, database, configuration, and reusable components.

**Testable Outcome**: Database is created, config works, header/footer load on test pages, URLs rewrite correctly.

### Tasks:

1. **Database Setup**
   - Create `setup/database.sql` with complete schema
   - Create `setup/seed.php` to populate test data:
     - 3 default roles:
       - Super Admin (all permissions)
       - Content Manager (manage_blog, manage_episodes)
       - Event Coordinator (manage_events, manage_donations)
     - 1 super admin (email: admin@lifeischaracter.com, password: Admin@123)
     - 1 content manager admin
     - 5 sample events (2 free, 3 paid, different statuses)
     - 6 donation types (matching current tiers)
     - 3 sample blog posts
     - 4 sample episodes
     - 2 regular users
   - Create `setup/install.php` - installation wizard

2. **Configuration**
   - Create `includes/config.php`:
     ```php
     - Database credentials
     - Base URL (dynamic detection + manual override)
     - Stripe keys (test/live based on environment)
     - Zeptomail credentials
     - Environment flag (local/production)
     - Session configuration
     - Upload paths
     - Timezone
     ```

3. **Core Includes**
   - `includes/db.php` - PDO database connection with error handling
   - `includes/response.php` - JSON response helpers (success/error)
   - `includes/auth.php` - Session management, authentication checks
   - `includes/mail.php` - Zeptomail integration (only sends in production)
   - `includes/stripe.php` - Stripe SDK initialization
   - `includes/upload.php` - Image upload handler with validation and security

4. **Header & Footer Components**
   - `includes/header.php`:
     - Extract navigation from static HTML
     - Add dynamic base URL references
     - Add active page detection
     - Add **Events** to navigation menu
   - `includes/footer.php`:
     - Extract footer from static HTML
     - Dynamic year, base URL references

5. **URL Rewriting**
   - Root `.htaccess`:
     ```apache
     - Remove .php extensions
     - Route /admin/* to admin folder
     - Route /account/* to account folder
     - Route /api/* to api folder
     - Security headers
     ```
   - `admin/.htaccess` - Admin-specific routing
   - `account/.htaccess` - Account-specific routing
   - `api/.htaccess` - API routing and CORS headers
   - `uploads/.htaccess` - Prevent PHP execution

6. **Convert Static Pages to PHP**
   - Rename `.html` to `.php`:
     - `index.php`
     - `about.php`
     - `episodes.php`
     - `blog.php`
     - `contact.php`
     - `donate.php`
   - Create `events.php` (new page)
   - Replace header/footer sections with `include` statements
   - Replace hardcoded URLs with `BASE_URL` constant
   - All pages should start with: `<?php require_once __DIR__ . '/includes/config.php'; ?>`

### Testing Phase 1:
- [ ] Run `setup/install.php` successfully creates database and tables
- [ ] Run `setup/seed.php` populates test data
- [ ] Access `http://localhost/life` shows homepage with header/footer
- [ ] Navigation links work with clean URLs (no .php)
- [ ] All 7 pages load without errors
- [ ] Events page appears in navigation
- [ ] Base URL is correctly picked from config
- [ ] Config file loads correctly from includes/ folder

---

## PHASE 2: Admin Authentication & Dashboard

**Goal**: Implement admin login system and basic dashboard.

**Testable Outcome**: Admin can log in, log out, see dashboard, sessions persist.

### Tasks:

1. **Admin Login System**
   - `admin/login.php`:
     - Login form (email/password)
     - CSRF protection
     - Remember session
     - Redirect to dashboard on success
   - `admin/logout.php`:
     - Destroy session
     - Redirect to login

2. **Session Management**
   - Update `includes/auth.php`:
     - `checkAdminAuth()` - verify admin session
     - `checkUserAuth()` - verify user session
     - `hasPermission($permission)` - check admin permissions via role
     - `isSuperAdmin()` - check if admin has super admin role
     - `getSessionUser()` - get current user/admin with role data
     - `getAdminPermissions()` - get all permissions for current admin
     - CSRF token generation/validation

3. **Admin Dashboard**
   - `admin/index.php`:
     - Overview statistics:
       - Total events (upcoming/past)
       - Total users
       - Total donations received
       - Recent transactions
       - Recent contact submissions
     - Quick links to all admin sections
   - `admin/includes/admin_header.php`:
     - Admin navigation sidebar
     - User info with role name, logout link
     - Permission-based menu visibility (check role permissions)
   - `admin/includes/admin_footer.php`

4. **Admin Profile**
   - `admin/profile.php`:
     - View/edit admin profile
     - Change password

### Testing Phase 2:
- [ ] Access `/admin` redirects to login if not authenticated
- [ ] Log in with seeded super admin credentials
- [ ] Dashboard shows correct statistics
- [ ] Session persists across page refreshes
- [ ] Logout destroys session and redirects
- [ ] Profile page loads and can update password
- [ ] Non-super admin cannot access admin management (if tested)

---

## PHASE 3: Admin Content Management (CRUD)

**Goal**: Full CRUD operations for events, donations, blog, episodes, users, and admins.

**Testable Outcome**: Admin can create, read, update, delete all content types.

### Tasks:

1. **Events Management**
   - `admin/events.php`:
     - List all events (table with filters)
     - Create new event form with featured image upload
     - Edit event form with image upload/replace
     - Delete event (soft delete - change status to cancelled)
     - View event registrations
     - View payments for event
     - Export registrations to CSV
     - Image preview and delete functionality
   - CRUD via regular PHP forms (non-AJAX for now)

2. **Donation Types Management**
   - `admin/donations.php`:
     - List all donation types
     - Create/edit/delete donation types
     - Set suggested amounts
     - Activate/deactivate types
     - View all donations made
     - Filter by donation type
     - Export donations to CSV

3. **Blog Management**
   - `admin/blog.php`:
     - List all blog posts (table with search/filter)
     - Filter by category
     - Create new post (rich text editor)
     - Select categories from checkboxes (multi-select, loaded from blog_categories)
     - Featured image upload with preview
     - Edit post with image replace/delete
     - Delete post
     - Publish/unpublish
     - Auto-generate slug from title
     - Manage tags (JSON array input) - free-form tags separate from categories
     - **Manage Categories Modal** (on same page):
       - Button to open "Manage Categories" modal
       - List all categories in modal table
       - Inline add new category form
       - Inline edit category (click to edit)
       - Activate/deactivate toggle
       - Drag-and-drop reordering
       - Show posts count per category
       - Prevent deletion if posts using it
       - Changes save via AJAX

4. **Episodes Management**
   - `admin/episodes.php`:
     - List all episodes with topic filter
     - Create/edit/delete episode
     - Select topic from dropdown (loaded from episode_topics)
     - YouTube URL validation
     - Auto-fetch thumbnail from YouTube (optional)
     - Publish/unpublish
     - Reorder episodes
     - **Manage Topics Modal** (on same page):
       - Button to open "Manage Topics" modal
       - List all topics in modal table
       - Inline add new topic form
       - Inline edit topic (click to edit)
       - Activate/deactivate toggle
       - Drag-and-drop reordering
       - Show episode count per topic
       - Prevent deletion if episodes using it
       - Changes save via AJAX

5. **Episode Topics Management** - REMOVED (Now modal on episodes page)

6. **Blog Categories Management** - REMOVED (Now modal on blog page)

7. **Users Management**
   - `admin/users.php`:
     - List all registered users
     - View user details
     - View user transactions
     - Activate/deactivate user accounts
     - Delete user (soft delete)
     - Search/filter users

6. **Admins & Roles Management**
   - `admin/admins.php`:
     - List all admins (only super admin can access)
     - Create new admin and assign role
     - Edit admin role assignment
     - Deactivate admin
     - Cannot delete self
   - `admin/roles.php` (only super admin can access):
     - List all roles
     - Create new role with permission checkboxes
     - Edit role permissions
     - Activate/deactivate roles
     - Cannot delete/deactivate Super Admin role
     - View admins assigned to each role

9. **View Submissions**
   - `admin/contact.php`:
     - View all contact submissions
     - Mark as read/responded
     - Delete submission
   - `admin/prayers.php`:
     - View prayer requests
     - Mark status (new/praying/answered)
     - Anonymous requests hidden
   - `admin/newsletter.php`:
     - View subscribers
     - Export to CSV
     - Unsubscribe user

10. **Transactions View**
   - `admin/transactions.php`:
     - View all transactions (events + donations)
     - Filter by type, status, date range
     - Export to CSV
     - View transaction details

11. **File Upload System**
   - Create `includes/upload.php`:
     - Image upload handler function
     - Validation (file type, size, dimensions)
     - Resize/optimize images using GD or Imagick
     - Unique filename generation (prevent overwriting)
     - Move to appropriate uploads directory (events/blog)
     - Return file path for database storage
     - Error handling with descriptive messages
   - Integrate with event and blog forms
   - Image preview before upload
   - Delete/replace existing images

10. **Upload Security**
   - Update `uploads/.htaccess`:
     - Prevent PHP execution in uploads directory
     - Deny access to certain file types (.php, .exe, etc.)
   - Server-side validation:
     - Check MIME type, not just extension
     - Verify image integrity
     - Limit file size (5MB default)
   - Only allow specific image formats (JPEG, PNG, GIF, WebP)

### Testing Phase 3:
- [ ] Create a new event successfully with featured image
- [ ] Upload event image (JPEG, PNG, GIF, WebP)
- [ ] Edit event and replace image
- [ ] Delete event image
- [ ] View event registrations (empty for now)
- [ ] Create/edit donation types
- [ ] Open "Manage Topics" modal on episodes page
- [ ] Create new episode topic via modal
- [ ] Edit episode topic via modal
- [ ] Reorder episode topics via drag-and-drop in modal
- [ ] Cannot delete topic with episodes using it (via modal)
- [ ] Close modal and see topic in episode creation dropdown
- [ ] Open "Manage Categories" modal on blog page
- [ ] Create new blog category via modal
- [ ] Edit blog category via modal
- [ ] Reorder blog categories via drag-and-drop in modal
- [ ] Cannot delete category with posts using it (via modal)
- [ ] Close modal and see category in blog creation checkboxes
- [ ] Create blog post with featured image and select multiple categories
- [ ] Upload blog image successfully
- [ ] Edit blog post and replace image
- [ ] Publish/unpublish blog post with image
- [ ] Create episode and select topic from dropdown
- [ ] Edit episode and change topic
- [ ] Publish/unpublish episode
- [ ] View users list
- [ ] Create new role with limited permissions
- [ ] Create new admin assigned to limited role
- [ ] Log in as limited admin and verify menu restrictions based on role permissions
- [ ] Edit role permissions and verify changes reflect immediately
- [ ] View contact/prayer submissions (manual DB insert for testing)
- [ ] View transactions (empty for now)
- [ ] Cannot upload PHP files to /uploads directory
- [ ] Cannot upload files larger than 5MB
- [ ] Uploaded images display correctly on frontend
- [ ] Image filenames are unique (no overwrites)

---

## PHASE 4: User Authentication & Registration

**Goal**: Complete user registration, activation, login, password reset flow.

**Testable Outcome**: Users can register, receive activation email (logged locally), activate account, log in, reset password.

### Tasks:

1. **User Registration**
   - `account/register.php`:
     - Registration form (email, password, full name, phone)
     - Password strength validation
     - Email uniqueness check
     - Generate activation token (64-char random)
     - Token expires in 24 hours
     - Send activation email (via Zeptomail in production, log locally)

2. **Account Activation**
   - `account/activate.php`:
     - Accept activation token from email link
     - Validate token and expiry
     - Activate user account
     - Auto-login after activation
     - Success/error messages

3. **Resend Activation**
   - `account/resend-activation.php`:
     - Form to resend activation email
     - Check if user exists and not already active
     - Generate new token
     - Send new activation email

4. **User Login**
   - `account/login.php`:
     - Login form (email/password)
     - Verify account is active
     - Update last_login timestamp
     - Session management
     - Redirect to dashboard

5. **User Logout**
   - `account/logout.php`:
     - Destroy session
     - Redirect to homepage

6. **Forgot Password**
   - `account/forgot-password.php`:
     - Email submission form
     - Generate reset token (64-char random)
     - Token expires in 1 hour
     - Send reset email with link

7. **Reset Password**
   - `account/reset-password.php`:
     - Accept reset token from email link
     - Validate token and expiry
     - New password form
     - Update password
     - Clear reset token
     - Auto-login after reset

8. **User Dashboard**
   - `account/dashboard.php`:
     - Welcome message
     - Quick stats:
       - Upcoming events registered
       - Total donations made
       - Recent transactions
     - Quick action buttons

9. **User Profile**
   - `account/profile.php`:
     - View/edit profile (name, phone)
     - Change password
     - Change email (requires re-verification)

10. **User Transactions**
    - `account/transactions.php`:
      - List all user transactions
      - Filter by type (event/donation)
      - View transaction details
      - Download receipt (future phase)

### Testing Phase 4:
- [ ] Register new user
- [ ] Activation email is logged (check logs or email service)
- [ ] Activate account via link
- [ ] Log in successfully
- [ ] Request password reset
- [ ] Reset password via email link
- [ ] Resend activation email works
- [ ] Dashboard shows correct user info
- [ ] Edit profile successfully
- [ ] Logout works
- [ ] Transactions page loads (empty for now)

---

## PHASE 5: Public Website & API Endpoints

**Goal**: Connect frontend to backend via API endpoints, make website dynamic.

**Testable Outcome**: Homepage, episodes, blog, events pages load dynamic data from database.

### Tasks:

1. **API Infrastructure**
   - `api/index.php`:
     - API documentation/health check
     - List all available endpoints
   - Update `includes/response.php`:
     - Standardized JSON responses
     - Error codes
     - CORS headers for API

2. **Events API**
   - `api/events/index.php`:
     - GET all published events
     - Query params: `status`, `limit`, `offset`
     - Return: id, title, description, featured_image, event_date, location, cost, status, current_registrations
     - Sort by event_date ASC

3. **Donations API**
   - `api/donations/index.php`:
     - GET active donation types
     - Return: id, name, description, suggested_amount
     - Sort by sort_order

4. **Blog API**
   - `api/blog/index.php`:
     - GET published blog posts
     - Query params: `category_id`, `limit`, `offset`
     - Return: id, title, slug, excerpt, featured_image, scripture_reference, categories (array), tags, author_name, published_at
     - JOIN with blog_post_categories and blog_categories
     - Sort by published_at DESC
   - `api/blog/categories.php`:
     - GET all active blog categories
     - Return: id, name, slug, description, post_count
     - Sort by sort_order ASC

5. **Episodes API**
   - `api/episodes/index.php`:
     - GET published episodes
     - Query params: `topic_id`, `status`, `limit`, `offset`
     - Return: id, title, description, youtube_url, thumbnail_url, scripture_reference, topic (object with id, name, slug), episode_number, duration, published_at
     - JOIN with episode_topics
     - Sort by episode_number DESC or published_at DESC
   - `api/episodes/topics.php`:
     - GET all active episode topics
     - Return: id, name, slug, description, episode_count
     - Sort by sort_order ASC

6. **Contact Form API**
   - `api/contact/submit.php`:
     - POST contact form submission
     - Validate inputs
     - Save to database
     - Queue email to admins (sent in Phase 7)
     - Return success message

7. **Prayer Request API**
   - `api/prayer/submit.php`:
     - POST prayer request submission
     - Handle anonymous submissions
     - Save to database
     - Queue email to admins (sent in Phase 7)
     - Return success message

8. **Newsletter API**
   - `api/newsletter/subscribe.php`:
     - POST newsletter subscription
     - Check for duplicate email
     - Save to database
     - Queue welcome email (sent in Phase 7)
     - Return success message

9. **Update Frontend JavaScript**
   - Update `js/main.js`:
     - Use correct API endpoints
     - Update base URL from config
     - Handle API responses
     - Display errors appropriately

10. **Update Website Pages**
    - `index.php`:
      - Load featured episodes via API
      - Newsletter signup via AJAX
    - `episodes.php`:
      - Load all episodes via API
      - Filter functionality
    - `blog.php`:
      - Load blog posts via API
      - Filter by category
    - `events.php`:
      - Load events via API
      - Filter by status
      - Show registration button (disabled for now)
    - `contact.php`:
      - Submit forms via AJAX
      - Show success/error messages
    - `donate.php`:
      - Load donation types via API
      - Donation form (payment in Phase 6)

### Testing Phase 5:
- [ ] `/api/events` returns seeded events as JSON
- [ ] `/api/donations` returns donation types
- [ ] `/api/blog` returns blog posts with categories array
- [ ] `/api/blog/categories` returns active categories
- [ ] `/api/episodes` returns episodes with topic object
- [ ] `/api/episodes/topics` returns active topics
- [ ] Homepage displays 3 featured episodes
- [ ] Episodes page shows all episodes with filters
- [ ] Blog page shows posts with filters
- [ ] Events page shows events with filters
- [ ] Contact form submits and saves to DB
- [ ] Prayer request submits and saves to DB
- [ ] Newsletter signup saves to DB
- [ ] All pages use dynamic base URLs

---

## PHASE 6: Payment Integration (Stripe)

**Goal**: Complete payment flow for event registrations and donations.

**Testable Outcome**: Users can register for paid events, make donations, payments process via Stripe.

### Tasks:

1. **Stripe Setup**
   - Update `includes/stripe.php`:
     - Initialize Stripe SDK with API key from config
     - Helper functions for payment intents
     - Helper for webhook verification

2. **Payment Intent API**
   - `api/payments/create-intent.php`:
     - POST create payment intent
     - Requires authentication
     - Accept: amount, type (event/donation), reference_id
     - Create Stripe PaymentIntent
     - Save to transactions table
     - Return client_secret

3. **Event Registration with Payment**
   - `api/events/register.php`:
     - POST register for event
     - Requires authentication
     - Check if user already registered
     - Check event capacity
     - If free: auto-complete registration
     - If paid: create payment intent, return client_secret
     - Save to event_registrations table

4. **Donation with Payment**
   - `api/donations/create.php`:
     - POST create donation
     - Can be authenticated or anonymous
     - If recurring: create Stripe Customer and Subscription
     - If one-time: create PaymentIntent
     - Save to donations table
     - Return client_secret

5. **Stripe Webhook Handler**
   - `api/webhook/stripe.php`:
     - Verify Stripe signature
     - Handle events:
       - `payment_intent.succeeded`
       - `payment_intent.payment_failed`
       - `customer.subscription.created`
       - `customer.subscription.deleted`
     - Update payment status in database
     - Update transaction status
     - Mark webhook as verified
     - Queue success emails (sent in Phase 7)

6. **Payment Success/Cancel Pages**
   - `donate-success.php`:
     - Display donation confirmation
     - Show transaction details
     - Thank you message
     - Link to user dashboard
   - `donate-cancel.php`:
     - Display cancellation message
     - Option to retry donation
     - Link back to donate page
   - `events/checkout-success.php`:
     - Display event registration confirmation
     - Show event details
     - Registration confirmation number
     - Link to user dashboard
   - `events/checkout-cancel.php`:
     - Display cancellation message
     - Option to retry registration
     - Link back to events page

7. **Frontend Payment Integration**
   - Update `js/main.js`:
     - Initialize Stripe.js with publishable key
     - Event registration payment flow
     - Donation payment flow
     - Handle payment confirmation
     - Redirect to success/cancel pages
   - Update `donate.php`:
     - Wire up Stripe Elements
     - Payment form submission
     - Success/failure handling
   - Update `events.php`:
     - Add registration button
     - Payment modal for paid events
     - Success confirmation

8. **User Transaction History**
   - `account/transactions.php`:
     - Load user transactions via API
     - Show event registrations and donations
     - Payment status indicators
     - Amount and date

9. **Admin Payment Views**
   - Update `admin/events.php`:
     - Show payment status for each registration
     - Total revenue per event
   - Update `admin/donations.php`:
     - Show payment status
     - Total donations received
   - Update `admin/transactions.php`:
     - Complete transaction log
     - Webhook verification status

### Testing Phase 6:
- [ ] Register for free event (auto-completes)
- [ ] Register for paid event (Stripe payment modal appears)
- [ ] Complete test payment for event (use Stripe test card)
- [ ] Make one-time test donation
- [ ] Make recurring test donation
- [ ] Webhook receives Stripe events
- [ ] Payment status updates in database
- [ ] User transaction history shows payments
- [ ] Admin sees payment data in events/donations
- [ ] Transaction log shows completed payments

---

## PHASE 7: Email Integration & Notifications

**Goal**: Implement all email notifications via Zeptomail.

**Testable Outcome**: All emails send in production environment, logged locally in development.

### Tasks:

1. **Email Service Setup**
   - Update `includes/mail.php`:
     - Zeptomail API integration
     - Template rendering
     - Environment check (only send in production)
     - Local logging for development
     - Email queue system (optional)

2. **Email Templates**
   - Create email templates (inline HTML):
     - User activation email
     - Password reset email
     - Newsletter welcome email
     - Event registration confirmation (user)
     - Event registration notification (admins)
     - Donation confirmation (user)
     - Donation notification (admins)
     - Contact form notification (admins)
     - Prayer request notification (admins)
     - Payment success confirmation

3. **User Registration Emails**
   - Update `account/register.php`:
     - Send activation email after registration
     - Include activation link
   - Update `account/resend-activation.php`:
     - Resend activation email

4. **Password Reset Emails**
   - Update `account/forgot-password.php`:
     - Send password reset email
     - Include reset link with token

5. **Payment Success Emails**
   - Update `api/payments/webhook.php`:
     - On payment success:
       - Send confirmation to user
       - Send notification to all active admins
     - Include transaction details
     - Include receipt information

6. **Form Submission Notifications**
   - Update `api/contact/submit.php`:
     - Send email to all admins
     - Include submission details
   - Update `api/prayer/submit.php`:
     - Send email to all admins
     - Respect anonymous flag
   - Update `api/newsletter/subscribe.php`:
     - Send welcome email to subscriber

7. **Admin Email Settings**
   - Add admin settings page (optional):
     - Configure notification preferences
     - Email templates customization

### Testing Phase 7:
- [ ] Register user and check activation email (logged locally)
- [ ] Request password reset and check email
- [ ] Subscribe to newsletter and check welcome email
- [ ] Submit contact form and check admin notification
- [ ] Submit prayer request and check admin notification
- [ ] Complete payment and check user + admin emails
- [ ] In production environment, emails actually send
- [ ] Email templates render correctly
- [ ] All dynamic data appears in emails

---

## PHASE 8: Final Polish & Optimization

**Goal**: Final UI/UX refinements, performance optimization, and deployment preparation.

**Testable Outcome**: Complete, polished system ready for production deployment.

### Tasks:

1. **UI/UX Refinements**
   - Premium design polish:
     - Consistent spacing and typography
     - Smooth transitions and animations
     - Loading states for AJAX operations
     - Success/error toast notifications
     - Form validation feedback
     - Mobile responsiveness
     - Accessibility improvements

2. **Admin UI Enhancements**
   - DataTables for all admin tables
   - Rich text editor for blog content (TinyMCE or similar)
   - Date/time pickers for events
   - Drag-drop image upload
   - Confirm dialogs for delete actions
   - Bulk actions for tables

3. **Performance Optimization**
   - Database query optimization
   - Add database indexes
   - Image lazy loading
   - CSS/JS minification
   - Browser caching headers
   - Gzip compression

4. **Security Hardening**
   - SQL injection prevention (prepared statements)
   - XSS protection (htmlspecialchars)
   - CSRF token validation on all forms
   - Rate limiting on login/registration
   - Password hashing (bcrypt/argon2)
   - Secure session configuration
   - Input sanitization

5. **Error Handling**
   - Custom error pages (404, 500)
   - Graceful error messages
   - Error logging
   - Development vs production error display

6. **Testing & Bug Fixes**
    - End-to-end testing of all features
    - Cross-browser testing
    - Mobile device testing
    - Payment flow testing
    - Email testing
    - Security testing
    - Performance testing

7. **Documentation**
    - Admin user guide
    - Installation instructions
    - Configuration guide
    - API documentation
    - Deployment checklist

### Testing Phase 8:
- [ ] All forms have proper validation
- [ ] Admin tables are sortable/searchable
- [ ] Mobile layout works on all pages
- [ ] No console errors on any page
- [ ] All links work correctly
- [ ] Payment flow works end-to-end
- [ ] Emails send correctly in production
- [ ] Page load times are under 2 seconds
- [ ] Security measures are in place
- [ ] Ready for production deployment

---

## Configuration Requirements

### includes/config.php Structure

```php
<?php
/**
 * Life is Character Radio - Configuration File
 * 
 * Location: /includes/config.php
 * 
 * This file contains all configuration settings for the application.
 * Adjust settings based on your environment (local/production).
 * 
 * All pages should include this file first:
 * require_once __DIR__ . '/includes/config.php';
 */

// Environment
define('ENVIRONMENT', 'local'); // 'local' or 'production'
define('DEBUG_MODE', ENVIRONMENT === 'local');

// Base URL Configuration
// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . '://' . $host . rtrim(dirname($scriptName), '/') . '/';

define('BASE_URL', $baseUrl);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'life_character_radio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Stripe Configuration
if (ENVIRONMENT === 'production') {
    define('STRIPE_SECRET_KEY', 'sk_live_YOUR_LIVE_KEY');
    define('STRIPE_PUBLISHABLE_KEY', 'pk_live_YOUR_LIVE_KEY');
    define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET');
} else {
    define('STRIPE_SECRET_KEY', 'sk_test_YOUR_TEST_KEY');
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_TEST_KEY');
    define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_TEST_WEBHOOK_SECRET');
}

// Zeptomail Configuration (only used in production)
define('ZEPTOMAIL_API_KEY', 'YOUR_ZEPTOMAIL_API_KEY');
define('ZEPTOMAIL_FROM_EMAIL', 'noreply@lifeischaracterradio.com');
define('ZEPTOMAIL_FROM_NAME', 'Life is Character Radio');

// Email Settings
define('ADMIN_EMAIL', 'admin@lifeischaracterradio.com');
define('SEND_EMAILS', ENVIRONMENT === 'production');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('SESSION_NAME', 'LICR_SESSION');

// File Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Timezone
date_default_timezone_set('America/Chicago'); // Central Time (Texas)

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
```

---

## Brand Guidelines

### Colors (from existing site)
- **Primary Cyan**: `#00E5FF`
- **Blue**: `#0099CC`
- **Dark Blue**: `#006B8F`
- **Navy**: `#001A2E`
- **Gold**: `#FFD700`
- **Silver**: `#C0C0C0`
- **Background Dark**: `#111827`
- **Gray 900**: `#1F2937`
- **Gray 800**: `#374151`
- **Gray 700**: `#4B5563`

### Typography
- **Cinematic Font**: 'Cinzel', Georgia, Times New Roman, serif (headings)
- **Modern Font**: 'Inter', system-ui, sans-serif (body text)

### Design Principles
- Premium, sophisticated aesthetic
- Smooth animations and transitions
- Generous white space
- Clear visual hierarchy
- Consistent iconography (Font Awesome)
- Mobile-first responsive design

---

## Code Standards

### File Header Comments Template

```php
<?php
/**
 * Life is Character Radio - [File Purpose]
 * 
 * Location: /[folder]/[filename].php
 * 
 * Description:
 * [Clear description of what this file does]
 * 
 * [Additional notes if needed]
 */
```

### Function Comment Template

```php
/**
 * [Function description]
 * 
 * @param type $param Description
 * @return type Description
 */
function functionName($param) {
    // Implementation
}
```

### Security Practices
- Always use prepared statements for database queries
- Validate and sanitize all user inputs
- Use `htmlspecialchars()` for output
- Implement CSRF protection on all forms
- Use password_hash() and password_verify()
- Secure session configuration
- Prevent directory traversal attacks

### Performance Practices
- Minimize database queries
- Use indexes on frequently queried columns
- Lazy load images
- Cache static assets
- Minimize HTTP requests
- Use async/defer for JavaScript

---

## Deployment Checklist

- [ ] Update includes/config.php with production credentials
- [ ] Set ENVIRONMENT to 'production'
- [ ] Update Stripe keys to live keys
- [ ] Configure Zeptomail credentials
- [ ] Set up database on production server
- [ ] Run database migrations
- [ ] Upload all files via FTP/SFTP
- [ ] Set folder permissions (uploads: 755)
- [ ] Configure SSL certificate
- [ ] Set up Stripe webhooks pointing to production URL
- [ ] Test email sending
- [ ] Test payment flow with real card
- [ ] Verify all forms work
- [ ] Check mobile responsiveness
- [ ] Set up database backups
- [ ] Configure error logging
- [ ] Monitor performance
- [ ] Create super admin account
- [ ] Disable debug mode

---

## Success Criteria

### Functionality
✓ All static pages converted to dynamic PHP  
✓ Clean URLs working via .htaccess  
✓ Admin can manage all content types  
✓ Users can register, activate, and log in  
✓ Payment processing works for events and donations  
✓ Emails send correctly in production  
✓ File uploads work securely  
✓ API endpoints return correct data  
✓ Stripe webhooks verify payments  
✓ All forms submit and validate  

### Performance
✓ Page load time < 2 seconds  
✓ Database queries optimized  
✓ Images optimized and compressed  
✓ No N+1 query problems  

### Security
✓ No SQL injection vulnerabilities  
✓ No XSS vulnerabilities  
✓ CSRF protection on all forms  
✓ Secure password storage  
✓ Secure session management  
✓ File upload validation  

### UI/UX
✓ Premium, sophisticated design  
✓ Mobile responsive  
✓ Accessible (WCAG 2.1 AA)  
✓ Consistent branding  
✓ Smooth animations  
✓ Clear error messages  
✓ Loading states  

### Code Quality
✓ Well-commented code  
✓ Consistent formatting  
✓ Reusable functions  
✓ No code duplication  
✓ Clear file organization  
✓ Follows PSR standards  

---

## Timeline Estimate

- **Phase 1**: 2-3 days
- **Phase 2**: 1-2 days
- **Phase 3**: 4-5 days
- **Phase 4**: 2-3 days
- **Phase 5**: 3-4 days
- **Phase 6**: 3-4 days
- **Phase 7**: 2-3 days
- **Phase 8**: 3-4 days

**Total**: Approximately 20-28 days (4-6 weeks)

---

## Notes

- Each phase builds upon the previous but is independently testable
- Seed data ensures immediate testing capability
- Base URLs never hardcoded, always from config
- Header/footer in separate files to avoid code repetition
- Environment flag controls email sending and error display
- Stripe test mode for local development
- Super admin created during seed process
- All database operations use prepared statements
- Session-based authentication for both admin and users
- Comprehensive error handling and logging
- Mobile-first responsive design throughout

---

**End of Implementation Plan**
