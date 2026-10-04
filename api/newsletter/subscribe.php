<?php
/**
 * Life is Character Radio - Newsletter Subscription API
 * Location: /api/newsletter/subscribe.php
 * Method: POST
 *
 * Serves two forms:
 *   - the contact page signup, which sends { name, email }
 *   - the devotional funnel block, which sends
 *     { firstName, email, source: "devotional", tags: ["devotional-funnel"] }
 *
 * "Already subscribed" means different things to each. With tags supplied, it
 * is only a duplicate if the subscriber already carries one of those tags, so
 * an existing newsletter subscriber can still request the devotional. Without
 * tags, any active subscription counts, preserving the contact page behaviour.
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/mail.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// The devotional form sends firstName; the contact page sends name.
$name   = trim($input['firstName'] ?? $input['name'] ?? '');
$email  = trim($input['email'] ?? '');
$source = trim($input['source'] ?? '');

// Normalise tags to a clean list of strings
$tags = $input['tags'] ?? [];
if (!is_array($tags)) $tags = [];
$tags = array_values(array_unique(array_filter(array_map('strval', $tags), 'strlen')));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonError('Valid email is required', 400);

$isDevotional = ($source === 'devotional');

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT id, is_active, tags FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $existingTags = json_decode($existing['tags'] ?? '[]', true);
        if (!is_array($existingTags)) $existingTags = [];

        if ($tags) {
            // Duplicate only when they already carry one of the incoming tags
            if (array_intersect($tags, $existingTags)) {
                jsonError('This email is already subscribed', 409);
            }
        } elseif ($existing['is_active']) {
            jsonError('This email is already subscribed', 409);
        }

        // Merge so a subscriber keeps tags picked up from other funnels
        $mergedTags = array_values(array_unique(array_merge($existingTags, $tags)));
        $reactivating = !$existing['is_active'];

        $sql = "UPDATE newsletter_subscribers
                   SET is_active = 1,
                       name = COALESCE(NULLIF(?, ''), name),
                       source = COALESCE(NULLIF(?, ''), source),
                       tags = ?,
                       unsubscribed_at = NULL"
             . ($reactivating ? ", subscribed_at = NOW()" : "") .
             " WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $source, $mergedTags ? json_encode($mergedTags) : null, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (name, email, source, tags, subscribed_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name ?: null, strtolower($email), $source ?: null, $tags ? json_encode($tags) : null]);
    }

    if ($isDevotional) {
        sendDevotionalEmail($email, $name);
        jsonSuccess(null, 'Your devotional is on its way! Check your inbox.');
    }

    sendNewsletterWelcomeEmail($email, $name);
    jsonSuccess(null, 'Thank you for subscribing! Check your email for confirmation.');
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    jsonError('Failed to subscribe. Please try again.', 500);
}
