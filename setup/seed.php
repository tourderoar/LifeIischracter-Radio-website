<?php
/**
 * Life is Character Radio - Database Seeding Script
 * 
 * Location: /setup/seed.php
 * 
 * Description:
 * Populates the database with test data for development and testing.
 * Should be run after database.sql to create sample data.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Set execution time limit
set_time_limit(300);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Seeding - Life is Character Radio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #111827; color: #fff; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #00E5FF; margin-bottom: 30px; font-size: 2rem; }
        .success { background: #10B981; color: white; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #EF4444; color: white; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #3B82F6; color: white; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .section { background: #1F2937; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #00E5FF; }
        pre { background: #0F172A; padding: 10px; border-radius: 4px; overflow-x: auto; margin: 10px 0; }
        a { color: #00E5FF; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🌱 Database Seeding</h1>";

try {
    $pdo = getDB();
    
    echo "<div class='section'>";
    echo "<h2 style='color: #00E5FF; margin-bottom: 15px;'>Starting Database Seed...</h2>";
    
    // 1. Seed Roles
    echo "<div class='info'><strong>Step 1:</strong> Creating default roles...</div>";
    
    $roles = [
        [
            'name' => 'Super Admin',
            'description' => 'Full system access with all permissions',
            'permissions' => json_encode(['manage_events', 'manage_donations', 'manage_blog', 'manage_episodes', 'manage_users', 'manage_admins', 'manage_roles', 'view_reports', 'manage_settings'])
        ],
        [
            'name' => 'Content Manager',
            'description' => 'Manage blog posts and episodes',
            'permissions' => json_encode(['manage_blog', 'manage_episodes'])
        ],
        [
            'name' => 'Event Coordinator',
            'description' => 'Manage events and donations',
            'permissions' => json_encode(['manage_events', 'manage_donations', 'view_reports'])
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO roles (name, description, permissions, is_active, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
    
    foreach ($roles as $role) {
        $stmt->execute([$role['name'], $role['description'], $role['permissions']]);
    }
    
    echo "<div class='success'>✓ Created " . count($roles) . " roles</div>";
    
    // Get role IDs
    $superAdminRole = $pdo->query("SELECT id FROM roles WHERE name = 'Super Admin'")->fetchColumn();
    $contentManagerRole = $pdo->query("SELECT id FROM roles WHERE name = 'Content Manager'")->fetchColumn();
    
    // 2. Seed Admins
    echo "<div class='info'><strong>Step 2:</strong> Creating admin accounts...</div>";
    
    $admins = [
        [
            'role_id' => $superAdminRole,
            'username' => 'superadmin',
            'email' => 'admin@lifeischaracter.com',
            'password' => password_hash('Admin@123', PASSWORD_DEFAULT),
            'full_name' => 'System Administrator'
        ],
        [
            'role_id' => $contentManagerRole,
            'username' => 'contentmgr',
            'email' => 'content@lifeischaracter.com',
            'password' => password_hash('Content@123', PASSWORD_DEFAULT),
            'full_name' => 'Content Manager'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO admins (role_id, username, email, password, full_name, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())");
    
    foreach ($admins as $admin) {
        $stmt->execute([$admin['role_id'], $admin['username'], $admin['email'], $admin['password'], $admin['full_name']]);
    }
    
    echo "<div class='success'>✓ Created " . count($admins) . " admin accounts</div>";
    echo "<pre><strong>Super Admin Login:</strong>\nEmail: admin@lifeischaracter.com\nPassword: Admin@123\n\n<strong>Content Manager Login:</strong>\nEmail: content@lifeischaracter.com\nPassword: Content@123</pre>";
    
    $adminId = $pdo->lastInsertId();
    
    // 3. Seed Users
    echo "<div class='info'><strong>Step 3:</strong> Creating test user accounts...</div>";
    
    $users = [
        [
            'email' => 'john.doe@example.com',
            'password' => password_hash('User@123', PASSWORD_DEFAULT),
            'full_name' => 'John Doe',
            'phone' => '(555) 123-4567',
            'is_active' => 1
        ],
        [
            'email' => 'jane.smith@example.com',
            'password' => password_hash('User@123', PASSWORD_DEFAULT),
            'full_name' => 'Jane Smith',
            'phone' => '(555) 987-6543',
            'is_active' => 1
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, phone, is_active, created_at, updated_at, last_login) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW())");
    
    foreach ($users as $user) {
        $stmt->execute([$user['email'], $user['password'], $user['full_name'], $user['phone'], $user['is_active']]);
    }
    
    $userId = $pdo->lastInsertId();
    
    echo "<div class='success'>✓ Created " . count($users) . " user accounts</div>";
    
    // 4. Seed Donation Types
    echo "<div class='info'><strong>Step 4:</strong> Creating donation types...</div>";
    
    $donationTypes = [
        ['name' => 'Supporter', 'description' => 'Support our ministry with a meaningful contribution', 'suggested_amount' => 25.00, 'sort_order' => 1],
        ['name' => 'Advocate', 'description' => 'Become an advocate for character-driven living', 'suggested_amount' => 50.00, 'sort_order' => 2],
        ['name' => 'Champion', 'description' => 'Champion the cause of faith and character', 'suggested_amount' => 100.00, 'sort_order' => 3],
        ['name' => 'Victory Partner', 'description' => 'Partner with us for victory and transformation', 'suggested_amount' => 250.00, 'sort_order' => 4],
        ['name' => 'Character Builder', 'description' => 'Help build character in lives across the nation', 'suggested_amount' => 500.00, 'sort_order' => 5],
        ['name' => 'Legacy Maker', 'description' => 'Create a lasting legacy of faith and character', 'suggested_amount' => 1000.00, 'sort_order' => 6]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO donation_types (name, description, suggested_amount, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, 1, ?, NOW(), NOW())");
    
    foreach ($donationTypes as $type) {
        $stmt->execute([$type['name'], $type['description'], $type['suggested_amount'], $type['sort_order']]);
    }
    
    echo "<div class='success'>✓ Created " . count($donationTypes) . " donation types</div>";
    
    // 5. Seed Episode Topics
    echo "<div class='info'><strong>Step 5:</strong> Creating episode topics...</div>";
    
    $topics = [
        ['name' => 'Character', 'slug' => 'character', 'description' => 'Building godly character in daily life', 'sort_order' => 1],
        ['name' => 'Faith', 'slug' => 'faith', 'description' => 'Strengthening faith and trust in God', 'sort_order' => 2],
        ['name' => 'Scripture', 'slug' => 'scripture', 'description' => 'Understanding and applying God\'s Word', 'sort_order' => 3],
        ['name' => 'Life', 'slug' => 'life', 'description' => 'Practical life lessons and wisdom', 'sort_order' => 4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO episode_topics (name, slug, description, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, 1, ?, NOW(), NOW())");
    
    foreach ($topics as $topic) {
        $stmt->execute([$topic['name'], $topic['slug'], $topic['description'], $topic['sort_order']]);
    }
    
    echo "<div class='success'>✓ Created " . count($topics) . " episode topics</div>";
    
    // Get topic IDs
    $characterTopic = $pdo->query("SELECT id FROM episode_topics WHERE slug = 'character'")->fetchColumn();
    $faithTopic = $pdo->query("SELECT id FROM episode_topics WHERE slug = 'faith'")->fetchColumn();
    $scriptureTopic = $pdo->query("SELECT id FROM episode_topics WHERE slug = 'scripture'")->fetchColumn();
    
    // 6. Seed Blog Categories
    echo "<div class='info'><strong>Step 6:</strong> Creating blog categories...</div>";
    
    $categories = [
        ['name' => 'Devotional', 'slug' => 'devotional', 'description' => 'Daily devotions and spiritual reflections', 'sort_order' => 1],
        ['name' => 'Character', 'slug' => 'character', 'description' => 'Character development and growth', 'sort_order' => 2],
        ['name' => 'Faith', 'slug' => 'faith', 'description' => 'Faith-building messages and testimonies', 'sort_order' => 3],
        ['name' => 'Scripture', 'slug' => 'scripture', 'description' => 'Biblical teaching and exposition', 'sort_order' => 4],
        ['name' => 'Testimony', 'slug' => 'testimony', 'description' => 'Personal testimonies of God\'s faithfulness', 'sort_order' => 5]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO blog_categories (name, slug, description, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, 1, ?, NOW(), NOW())");
    
    foreach ($categories as $category) {
        $stmt->execute([$category['name'], $category['slug'], $category['description'], $category['sort_order']]);
    }
    
    echo "<div class='success'>✓ Created " . count($categories) . " blog categories</div>";
    
    // Get category IDs
    $devotionalCat = $pdo->query("SELECT id FROM blog_categories WHERE slug = 'devotional'")->fetchColumn();
    $characterCat = $pdo->query("SELECT id FROM blog_categories WHERE slug = 'character'")->fetchColumn();
    $faithCat = $pdo->query("SELECT id FROM blog_categories WHERE slug = 'faith'")->fetchColumn();
    $scriptureCat = $pdo->query("SELECT id FROM blog_categories WHERE slug = 'scripture'")->fetchColumn();
    
    // 7. Seed Events
    echo "<div class='info'><strong>Step 7:</strong> Creating sample events...</div>";
    
    $events = [
        [
            'title' => 'Character Building Workshop',
            'description' => 'Join us for an intensive workshop on building character through God\'s Word. Learn practical strategies for living a victorious life.',
            'event_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'location' => '2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052',
            'cost' => 0.00,
            'status' => 'upcoming',
            'max_capacity' => 100
        ],
        [
            'title' => 'Faith & Victory Conference 2026',
            'description' => 'A powerful 3-day conference focusing on faith, character, and victorious Christian living. Featuring workshops, keynote messages, and fellowship.',
            'event_date' => date('Y-m-d H:i:s', strtotime('+60 days')),
            'location' => 'Dallas Convention Center, Dallas, TX',
            'cost' => 75.00,
            'status' => 'upcoming',
            'max_capacity' => 500
        ],
        [
            'title' => 'Youth Character Summit',
            'description' => 'Empowering the next generation with character-driven principles and biblical wisdom.',
            'event_date' => date('Y-m-d H:i:s', strtotime('+45 days')),
            'location' => 'Community Center, Grand Prairie, TX',
            'cost' => 25.00,
            'status' => 'upcoming',
            'max_capacity' => 200
        ],
        [
            'title' => 'Marriage Enrichment Weekend',
            'description' => 'Strengthen your marriage with biblical principles and character development strategies.',
            'event_date' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'location' => '2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052',
            'cost' => 150.00,
            'status' => 'past',
            'max_capacity' => 50
        ],
        [
            'title' => 'Leadership Training Intensive',
            'description' => 'Develop character-driven leadership skills for ministry and business.',
            'event_date' => date('Y-m-d H:i:s', strtotime('+90 days')),
            'location' => 'Online (Zoom)',
            'cost' => 50.00,
            'status' => 'upcoming',
            'max_capacity' => null
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, cost, status, max_capacity, current_registrations, created_by, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW(), NOW(), NOW())");
    
    foreach ($events as $event) {
        $stmt->execute([
            $event['title'],
            $event['description'],
            $event['event_date'],
            $event['location'],
            $event['cost'],
            $event['status'],
            $event['max_capacity'],
            $adminId
        ]);
    }
    
    echo "<div class='success'>✓ Created " . count($events) . " events</div>";
    
    // 8. Seed Blog Posts
    echo "<div class='info'><strong>Step 8:</strong> Creating blog posts...</div>";
    
    $blogPosts = [
        [
            'title' => 'Understanding Your Role in God\'s Script',
            'slug' => 'understanding-your-role-in-gods-script',
            'content' => '<p>Life is indeed a script, and each of us has been given a unique role to play. Understanding this divine assignment is the first step toward living a character-driven, victorious life.</p><p>When we recognize that God is the ultimate author of our story, we can embrace our part with confidence and purpose. Every challenge becomes an opportunity for character development, and every victory becomes a testimony to God\'s faithfulness.</p><p>The question is not whether we will face trials, but how we will respond to them. Will we play our part with excellence, or will we shrink back in fear?</p>',
            'excerpt' => 'Discover how understanding your divine role transforms challenges into opportunities for character development.',
            'scripture_reference' => 'Jeremiah 29:11',
            'tags' => json_encode(['character', 'faith', 'purpose']),
            'status' => 'published'
        ],
        [
            'title' => 'The Power of Character in Times of Trial',
            'slug' => 'the-power-of-character-in-times-of-trial',
            'content' => '<p>Character is not built in comfort zones; it is forged in the fires of adversity. When trials come—and they will—your character determines whether you emerge stronger or broken.</p><p>The Bible is filled with examples of men and women who faced impossible situations but overcame through character and faith. Joseph in prison, Daniel in the lion\'s den, David facing Goliath—each demonstrated that character combined with faith produces victory.</p><p>Your current trial is not meant to destroy you but to develop you. Embrace it, learn from it, and let it shape you into the person God designed you to be.</p>',
            'excerpt' => 'Character is forged in adversity. Learn how to emerge victorious from life\'s trials.',
            'scripture_reference' => 'James 1:2-4',
            'tags' => json_encode(['character', 'trials', 'victory']),
            'status' => 'published'
        ],
        [
            'title' => 'Living Victoriously: Daily Practices for Character Development',
            'slug' => 'living-victoriously-daily-practices',
            'content' => '<p>Victory is not an event; it\'s a lifestyle. Living victoriously requires daily commitment to character development and spiritual growth.</p><p>Start each day with prayer and scripture reading. This grounds you in God\'s truth and prepares you for whatever challenges may come. Make character-building decisions throughout the day, choosing integrity over convenience.</p><p>Surround yourself with people who challenge and encourage you to grow. Remember, you become like those you spend time with. Choose wisely.</p><p>End each day with reflection. What did you learn today? How did you grow? What could you do better tomorrow? This practice of self-examination keeps you on the path of continuous improvement.</p>',
            'excerpt' => 'Victory is a lifestyle. Discover daily practices that build character and lead to victorious living.',
            'scripture_reference' => '1 Corinthians 15:57',
            'tags' => json_encode(['victory', 'character', 'devotional', 'scripture']),
            'status' => 'published'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, scripture_reference, tags, author_name, status, created_by, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'Paul Kumeni Agbo', ?, ?, NOW(), NOW(), NOW())");
    
    foreach ($blogPosts as $post) {
        $stmt->execute([
            $post['title'],
            $post['slug'],
            $post['content'],
            $post['excerpt'],
            $post['scripture_reference'],
            $post['tags'],
            $post['status'],
            $adminId
        ]);
    }
    
    echo "<div class='success'>✓ Created " . count($blogPosts) . " blog posts</div>";
    
    // 9. Assign Categories to Blog Posts
    echo "<div class='info'><strong>Step 9:</strong> Assigning categories to blog posts...</div>";
    
    // Get blog post IDs
    $post1Id = $pdo->query("SELECT id FROM blog_posts WHERE slug = 'understanding-your-role-in-gods-script'")->fetchColumn();
    $post2Id = $pdo->query("SELECT id FROM blog_posts WHERE slug = 'the-power-of-character-in-times-of-trial'")->fetchColumn();
    $post3Id = $pdo->query("SELECT id FROM blog_posts WHERE slug = 'living-victoriously-daily-practices'")->fetchColumn();
    
    // Assign categories
    $postCategories = [
        ['post_id' => $post1Id, 'category_ids' => [$characterCat, $faithCat]], // Character + Faith
        ['post_id' => $post2Id, 'category_ids' => [$characterCat]], // Character
        ['post_id' => $post3Id, 'category_ids' => [$devotionalCat, $characterCat, $scriptureCat]] // Devotional + Character + Scripture
    ];
    
    $stmt = $pdo->prepare("INSERT INTO blog_post_categories (blog_post_id, category_id) VALUES (?, ?)");
    $categoryCount = 0;
    
    foreach ($postCategories as $assignment) {
        foreach ($assignment['category_ids'] as $categoryId) {
            $stmt->execute([$assignment['post_id'], $categoryId]);
            $categoryCount++;
        }
    }
    
    echo "<div class='success'>✓ Assigned {$categoryCount} categories to blog posts</div>";
    
    // 10. Seed Episodes
    echo "<div class='info'><strong>Step 10:</strong> Creating sample episodes...</div>";
    
    $episodes = [
        [
            'title' => 'Life is a Script - Introduction to Character-Driven Living',
            'description' => 'Welcome to Life is Character Radio! In this premiere episode, we explore the powerful metaphor of life as a script and how understanding your role leads to victorious living.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'scripture_reference' => 'Psalm 139:16',
            'topic_id' => $characterTopic,
            'episode_number' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'Faith That Moves Mountains',
            'description' => 'Discover how mountain-moving faith combined with solid character creates unstoppable momentum in your spiritual journey.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'scripture_reference' => 'Matthew 17:20',
            'topic_id' => $faithTopic,
            'episode_number' => 2,
            'status' => 'published'
        ],
        [
            'title' => 'The Character of Victory',
            'description' => 'What does it mean to be victorious? Learn the character traits that distinguish winners from survivors.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'scripture_reference' => '1 Corinthians 15:57',
            'topic_id' => $characterTopic,
            'episode_number' => 3,
            'status' => 'published'
        ],
        [
            'title' => 'Scripture-Based Decision Making',
            'description' => 'How to use God\'s Word as your guide for making character-driven decisions in every area of life.',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'scripture_reference' => 'Psalm 119:105',
            'topic_id' => $scriptureTopic,
            'episode_number' => 4,
            'status' => 'published'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO episodes (title, description, youtube_url, scripture_reference, topic_id, episode_number, duration, status, created_by, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1800, ?, ?, NOW(), NOW(), NOW())");
    
    foreach ($episodes as $episode) {
        $stmt->execute([
            $episode['title'],
            $episode['description'],
            $episode['youtube_url'],
            $episode['scripture_reference'],
            $episode['topic_id'],
            $episode['episode_number'],
            $episode['status'],
            $adminId
        ]);
    }
    
    echo "<div class='success'>✓ Created " . count($episodes) . " episodes</div>";
    
    echo "</div>"; // End section
    
    // Summary
    echo "<div class='section' style='border-left-color: #10B981;'>";
    echo "<h2 style='color: #10B981; margin-bottom: 15px;'>✓ Seeding Completed Successfully!</h2>";
    echo "<p style='margin: 10px 0;'><strong>Database has been populated with test data:</strong></p>";
    echo "<ul style='margin: 10px 0 10px 20px; line-height: 1.8;'>";
    echo "<li>3 Roles (Super Admin, Content Manager, Event Coordinator)</li>";
    echo "<li>2 Admin accounts</li>";
    echo "<li>2 User accounts</li>";
    echo "<li>6 Donation types</li>";
    echo "<li>4 Episode topics (Character, Faith, Scripture, Life)</li>";
    echo "<li>5 Blog categories (Devotional, Character, Faith, Scripture, Testimony)</li>";
    echo "<li>5 Events (3 upcoming, 1 past, 1 online)</li>";
    echo "<li>3 Blog posts with categories assigned</li>";
    echo "<li>4 Episodes with topics assigned</li>";
    echo "</ul>";
    echo "<p style='margin: 20px 0 10px;'><strong>Next Steps:</strong></p>";
    echo "<ul style='margin: 10px 0 10px 20px; line-height: 1.8;'>";
    echo "<li><a href='" . BASE_URL . "'>Visit Homepage</a> - Test the public website</li>";
    echo "<li><a href='" . BASE_URL . "admin/login'>Admin Login</a> - Test admin functionality</li>";
    echo "<li><a href='" . BASE_URL . "account/login'>User Login</a> - Test user functionality</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'><strong>Database Error:</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<div class='error'><strong>Error:</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
