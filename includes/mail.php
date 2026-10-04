<?php
/**
 * Life is Character Radio - Email Sending Functions
 * 
 * Location: /includes/mail.php
 * 
 * Complete email system with Zeptomail integration and all templates
 * Sends in production, logs locally in development
 */

// Include guard
if (defined('LICR_MAIL_LOADED')) return;
define('LICR_MAIL_LOADED', true);

if (!defined('BASE_URL')) require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Get all active admin emails
 */
function getAllAdminEmails(): array {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT email, full_name FROM admins WHERE is_active = 1");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Send email via Zeptomail or log locally
 */
function sendEmail(string $to, string $subject, string $html, ?string $name = null, ?string $replyTo = null): bool {
    return SEND_EMAILS
        ? sendViaZeptomail($to, $subject, $html, $name, $replyTo)
        : logEmail($to, $subject, $html, $replyTo);
}

/**
 * Send via Zeptomail API
 *
 * Everything sends from ZEPTOMAIL_FROM_EMAIL, since Zeptomail only accepts
 * senders on a domain verified against the connected Agent.
 *
 * $replyTo points replies at a monitored inbox without changing the sender,
 * which carries no such restriction. Zeptomail expects reply_to as an array
 * of objects, unlike from, which is a single object.
 */
function sendViaZeptomail(string $to, string $subject, string $html, ?string $name, ?string $replyTo = null): bool {
    $payload = [
        'from' => ['address' => ZEPTOMAIL_FROM_EMAIL, 'name' => ZEPTOMAIL_FROM_NAME],
        'to' => [['email_address' => ['address' => $to, 'name' => $name ?? $to]]],
        'subject' => $subject,
        'htmlbody' => $html
    ];

    if ($replyTo) {
        $payload['reply_to'] = [['address' => $replyTo, 'name' => ZEPTOMAIL_FROM_NAME]];
    }

    $ch = curl_init(ZEPTOMAIL_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: Zoho-enczapikey ' . ZEPTOMAIL_API_KEY,
            'Content-Type: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code !== 200) error_log("Zeptomail Error: HTTP $code - $response");
    return $code === 200;
}

/**
 * Log email locally
 */
function logEmail(string $to, string $subject, string $html, ?string $replyTo = null): bool {
    $dir = dirname(MAIL_LOG_FILE);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $log = "\n" . str_repeat('=', 80) . "\n" .
           "Date: " . date('Y-m-d H:i:s') . "\nFrom: " . ZEPTOMAIL_FROM_EMAIL .
           ($replyTo ? "\nReply-To: $replyTo" : '') .
           "\nTo: $to\nSubject: $subject\n" .
           str_repeat('-', 80) . "\n" . strip_tags($html) . "\n";
    
    return file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND) !== false;
}

/**
 * Base email template with brand styling
 */
function emailTemplate(string $title, string $content, string $btnText, string $btnUrl, string $footer = ''): string {
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1.0'><style>
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',system-ui,sans-serif;background:#f3f4f6;padding:20px}
        .container{max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1)}
        .header{background:linear-gradient(135deg,#001A2E 0%,#006B8F 100%);padding:40px 30px;text-align:center}
        .header h1{font-family:'Cinzel',Georgia,serif;color:#00E5FF;font-size:28px;margin:0}
        .content{padding:40px 30px}
        .content h2{font-family:'Cinzel',Georgia,serif;color:#001A2E;font-size:24px;margin-bottom:20px}
        .content p{color:#374151;line-height:1.6;margin-bottom:20px}
        .btn{display:inline-block;background:#00E5FF;color:#001A2E!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;margin:10px 0}
        .btn:hover{background:#0099CC}
        .link-box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:15px;margin-top:20px;word-break:break-all}
        .link-box p{margin:0 0 8px 0;font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase}
        .link-box a{color:#0099CC;text-decoration:none;font-size:13px}
        .footer{background:#f9fafb;padding:20px 30px;text-align:center;color:#6b7280;font-size:14px}
        .footer p{margin:8px 0}
    </style></head><body><div class='container'>
        <div class='header'><h1>" . SITE_NAME . "</h1></div>
        <div class='content'><h2>$title</h2><p>$content</p>
        <a href='$btnUrl' class='btn'>$btnText</a>
        <div class='link-box'><p>Or copy this link:</p><a href='$btnUrl'>$btnUrl</a></div>" . ($footer ? "<p style='margin-top:30px;color:#6b7280;font-size:14px'>$footer</p>" : "") . "</div>
        <div class='footer'><p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
        <p>You're receiving this email because you signed up on our website.</p></div>
    </div></body></html>";
}

/**
 * Send activation email
 */
function sendActivationEmail(string $email, string $name, string $token): bool {
    return sendEmail($email, "Activate Your Account", emailTemplate(
        "Welcome to " . SITE_NAME,
        "Hi $name,<br><br>Thank you for registering! Click below to activate your account and start your journey with us.",
        "Activate Account",
        BASE_URL . "account/activate?token=$token",
        "This link expires in 24 hours. If you didn't create this account, please ignore this email."
    ), $name);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail(string $email, string $name, string $token): bool {
    return sendEmail($email, "Reset Your Password", emailTemplate(
        "Password Reset Request",
        "Hi $name,<br><br>We received a request to reset your password. Click below to create a new password.",
        "Reset Password",
        BASE_URL . "account/reset-password?token=$token",
        "This link expires in 1 hour. If you didn't request this, please ignore this email."
    ), $name);
}

/**
 * Send newsletter welcome email
 */
function sendNewsletterWelcomeEmail(string $email, string $name): bool {
    return sendEmail($email, "Welcome to Our Newsletter!", emailTemplate(
        "Thank You for Subscribing!",
        "Hi " . ($name ?: 'Friend') . ",<br><br>You've successfully subscribed to our newsletter. We'll keep you updated with inspiring content, events, and more.",
        "Visit Our Website",
        BASE_URL,
        "You can unsubscribe at any time by clicking the link in our emails."
    ), $name);
}

/**
 * Send the free 7-day devotional to a funnel signup
 *
 * Sent instead of the generic welcome email when a subscriber comes from the
 * devotional block. Sends from the usual no-reply address, with replies
 * directed to the monitored inbox.
 */
function sendDevotionalEmail(string $email, string $name): bool {
    return sendEmail($email, "Your free devotional is here", emailTemplate(
        "Life is a Script: 7 Days to Living with Character",
        "Hi " . ($name ?: 'Friend') . ",<br><br>Thank you for signing up. Your free 7-day devotional is ready to read.<br><br>" .
        "Each day takes about five minutes: one scripture, a short reflection, one action step and a prayer. " .
        "You can read it online or print it to keep.",
        "Read the Devotional",
        BASE_URL . "devotional",
        "Life is a script. Win with character. You can unsubscribe at any time by clicking the link in our emails."
    ), $name, REPLY_TO_EMAIL);
}

/**
 * Send event registration confirmation to user
 */
function sendEventRegistrationEmail(string $email, string $name, array $event, float $amount): bool {
    $details = "Event: <strong>{$event['title']}</strong><br>Date: " . date('F j, Y g:i A', strtotime($event['event_date'])) . 
               "<br>Location: {$event['location']}" . ($amount > 0 ? "<br>Amount Paid: $" . number_format($amount, 2) : "");
    
    // Send confirmation to user
    $userEmail = sendEmail($email, "Event Registration Confirmed", emailTemplate(
        "Registration Successful!",
        "Hi $name,<br><br>Your registration has been confirmed!<br><br>$details",
        "View Event Details",
        BASE_URL . "events",
        "We look forward to seeing you at the event!"
    ), $name);
    
    // Notify all admins
    $adminHtml = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;padding:20px'>
        <h2 style='color:#001A2E'>New Event Registration</h2>
        <p><strong>Attendee:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Event:</strong> {$event['title']}</p>
        <p><strong>Date:</strong> " . date('F j, Y g:i A', strtotime($event['event_date'])) . "</p>
        <p><strong>Location:</strong> {$event['location']}</p>" . 
        ($amount > 0 ? "<p><strong>Amount Paid:</strong> $" . number_format($amount, 2) . "</p>" : "") . "
        <p style='margin-top:20px;color:#6b7280;font-size:14px'>View all registrations in the <a href='" . BASE_URL . "admin/events'>admin panel</a>.</p>
    </body></html>";
    
    $admins = getAllAdminEmails();
    foreach ($admins as $admin) {
        sendEmail($admin['email'], "New Event Registration: {$event['title']}", $adminHtml, $admin['full_name']);
    }
    
    return $userEmail;
}

/**
 * Send donation confirmation to user
 */
function sendDonationConfirmationEmail(string $email, string $name, float $amount, string $frequency): bool {
    $freqText = $frequency === 'one-time' ? 'one-time' : $frequency;
    
    // Send confirmation to user
    $userEmail = sendEmail($email, "Thank You for Your Donation", emailTemplate(
        "Donation Received",
        "Hi $name,<br><br>Thank you for your generous donation of <strong>$" . number_format($amount, 2) . "</strong>. Your support helps us continue our mission.",
        "Visit Our Website",
        BASE_URL,
        "This email serves as your donation receipt for tax purposes. " . TAX_DEDUCTIBLE_NOTICE . " God bless you!"
    ), $name);
    
    // Notify all admins
    $adminHtml = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;padding:20px'>
        <h2 style='color:#001A2E'>New Donation Received</h2>
        <p><strong>Donor:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Amount:</strong> $" . number_format($amount, 2) . "</p>
        <p><strong>Frequency:</strong> " . ucwords(str_replace('-', ' ', $freqText)) . "</p>
        <p style='margin-top:20px;color:#6b7280;font-size:14px'>View all donations in the <a href='" . BASE_URL . "admin/donations'>admin panel</a>.</p>
    </body></html>";
    
    $admins = getAllAdminEmails();
    foreach ($admins as $admin) {
        sendEmail($admin['email'], "New Donation: $" . number_format($amount, 2) . " from $name", $adminHtml, $admin['full_name']);
    }
    
    return $userEmail;
}

/**
 * Send donation confirmation with account activation for new guest donors
 */
if (!function_exists('sendDonationWithAccountEmail')) {
function sendDonationWithAccountEmail(string $email, string $name, float $amount, string $frequency, string $activationToken): bool {
    $freqText = $frequency === 'one-time' ? 'one-time' : $frequency;
    $activationUrl = BASE_URL . "account/set-password?token=$activationToken";
    
    $content = "Hi $name,<br><br>Thank you for your generous donation of <strong>$" . number_format($amount, 2) . "</strong>!<br><br>" .
               "<strong>We've Created an Account for You</strong><br><br>" .
               "To track your donations and access exclusive features, we've created an account with this email address. " .
               "Click the button below to set your password and activate your account.";
    
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1.0'><style>
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',system-ui,sans-serif;background:#f3f4f6;padding:20px}
        .container{max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1)}
        .header{background:linear-gradient(135deg,#001A2E 0%,#006B8F 100%);padding:40px 30px;text-align:center}
        .header h1{font-family:'Cinzel',Georgia,serif;color:#00E5FF;font-size:28px;margin:0}
        .content{padding:40px 30px}
        .content h2{font-family:'Cinzel',Georgia,serif;color:#001A2E;font-size:24px;margin-bottom:20px}
        .content p{color:#374151;line-height:1.6;margin-bottom:20px}
        .btn{display:inline-block;background:#00E5FF;color:#001A2E!important;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;margin:10px 0}
        .btn:hover{background:#0099CC}
        .link-box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:15px;margin-top:20px;word-break:break-all}
        .link-box p{margin:0 0 8px 0;font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase}
        .link-box a{color:#0099CC;text-decoration:none;font-size:13px}
        .info-box{background:#ecfdf5;border-left:4px solid #10b981;padding:15px;margin:20px 0;border-radius:4px}
        .info-box p{margin:0;color:#065f46;font-size:14px}
        .footer{background:#f9fafb;padding:20px 30px;text-align:center;color:#6b7280;font-size:14px}
        .footer p{margin:8px 0}
    </style></head><body><div class='container'>
        <div class='header'><h1>" . SITE_NAME . "</h1></div>
        <div class='content'>
            <h2>Thank You for Your Donation!</h2>
            <p>$content</p>
            <a href='$activationUrl' class='btn'>Set Password & Activate</a>
            <div class='link-box'><p>Or copy this link:</p><a href='$activationUrl'>$activationUrl</a></div>
            <div class='info-box'>
                <p><strong>Your Donation:</strong> $" . number_format($amount, 2) . " (" . ucwords($freqText) . ")</p>
            </div>
            <p style='margin-top:30px;color:#6b7280;font-size:14px'>Click the link above to set your password and activate your account. This link expires in 24 hours. This email serves as your donation receipt for tax purposes. " . TAX_DEDUCTIBLE_NOTICE . " God bless you!</p>
        </div>
        <div class='footer'><p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
        <p>You're receiving this email because you made a donation on our website.</p></div>
    </div></body></html>";
    
    // Send to donor
    $userEmail = sendEmail($email, "Thank You for Your Donation - Set Your Password", $html, $name);
    
    // Notify admins
    $adminHtml = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;padding:20px'>
        <h2 style='color:#001A2E'>New Donation with Account Created</h2>
        <p><strong>Donor:</strong> $name (New Account)</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Amount:</strong> $" . number_format($amount, 2) . "</p>
        <p><strong>Frequency:</strong> " . ucwords(str_replace('-', ' ', $freqText)) . "</p>
        <p style='margin-top:15px;padding:10px;background:#fef3c7;border-left:4px solid #f59e0b;color:#92400e'>📝 Guest account created - password setup email sent</p>
        <p style='margin-top:20px;color:#6b7280;font-size:14px'>View all donations in the <a href='" . BASE_URL . "admin/donations'>admin panel</a>.</p>
    </body></html>";
    
    $admins = getAllAdminEmails();
    foreach ($admins as $admin) {
        sendEmail($admin['email'], "New Donation: $" . number_format($amount, 2) . " from $name (New Account)", $adminHtml, $admin['full_name']);
    }
    
    return $userEmail;
}
}

/**
 * Send admin notification for contact form
 */
function sendContactNotification(array $data): bool {
    $html = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;padding:20px'>
        <h2 style='color:#001A2E'>New Contact Form Submission</h2>
        <p><strong>Name:</strong> {$data['name']}</p>
        <p><strong>Email:</strong> {$data['email']}</p>
        <p><strong>Subject:</strong> {$data['subject']}</p>
        <p><strong>Message:</strong></p>
        <div style='background:#f3f4f6;padding:15px;border-radius:8px'>{$data['message']}</div>
        <p style='margin-top:20px;color:#6b7280;font-size:14px'>View all submissions in the <a href='" . BASE_URL . "admin/contact'>admin panel</a>.</p>
    </body></html>";
    
    $admins = getAllAdminEmails();
    $success = true;
    foreach ($admins as $admin) {
        if (!sendEmail($admin['email'], "New Contact Form Submission", $html, $admin['full_name'])) {
            $success = false;
        }
    }
    return $success;
}

/**
 * Send admin notification for prayer request
 */
function sendPrayerNotification(array $data): bool {
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $from = $name ?: 'Not provided';
    $emailLine = $email ? "<p><strong>Email:</strong> $email</p>" : "";

    $html = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;padding:20px'>
        <h2 style='color:#001A2E'>New Prayer Request</h2>
        <p><strong>From:</strong> $from</p>
        $emailLine
        <p><strong>Request:</strong></p>
        <div style='background:#f3f4f6;padding:15px;border-radius:8px'>{$data['prayer_request']}</div>
        <p style='margin-top:20px;color:#6b7280;font-size:14px'>View all requests in the <a href='" . BASE_URL . "admin/prayers'>admin panel</a>.</p>
    </body></html>";
    
    $admins = getAllAdminEmails();
    $success = true;
    foreach ($admins as $admin) {
        if (!sendEmail($admin['email'], "New Prayer Request", $html, $admin['full_name'])) {
            $success = false;
        }
    }
    return $success;
}
