-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2026 at 11:58 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `life_character_radio`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Bcrypt hashed',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `role_id`, `username`, `email`, `password`, `full_name`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 1, 'superadmin', 'admin@lifeischaracter.com', '$2y$10$U.Fh3y187OTmfGZ9McGGvO8fUWL3ibosob0zroua3wrGBLfuYRbxu', 'System Administrator', 1, '2026-07-24 10:05:06', '2026-07-21 12:10:12', '2026-07-24 10:05:06');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Devotional', 'devotional', 'Daily devotions and spiritual reflections', 1, 1, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(2, 'Character', 'character', 'Character development and growth', 1, 2, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(3, 'Faith', 'faith', 'Faith-building messages and testimonies', 1, 3, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(4, 'Scripture', 'scripture', 'Biblical teaching and exposition', 1, 4, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(5, 'Testimony', 'testimony', 'Personal testimonies of God\'s faithfulness', 1, 5, '2026-07-21 12:10:12', '2026-07-21 12:10:12');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scripture_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Free-form tags: ["faith", "victory"]' CHECK (json_valid(`tags`)),
  `author_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Paul Kumeni Agbo',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_categories`
--

CREATE TABLE `blog_post_categories` (
  `id` int(11) NOT NULL,
  `blog_post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_submissions`
--

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','read','responded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for anonymous donations',
  `donation_type_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_frequency` enum('one-time','monthly','quarterly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `donor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `donor_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For recurring donations',
  `stripe_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For recurring donations',
  `donated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `payment_completed_at` datetime DEFAULT NULL,
  `account_created` tinyint(1) DEFAULT 0 COMMENT 'Whether an account was created with this donation',
  `activation_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Activation token if account was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donation_types`
--

CREATE TABLE `donation_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suggested_amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_types`
--

INSERT INTO `donation_types` (`id`, `name`, `description`, `suggested_amount`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Supporter', 'Support our ministry with a meaningful contribution', '25.00', 1, 7, '2026-07-21 12:10:12', '2026-07-21 18:25:35'),
(2, 'Advocate', 'Become an advocate for character-driven living', '50.00', 1, 2, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(3, 'Champion', 'Champion the cause of faith and character', '100.00', 1, 3, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(4, 'Victory Partner', 'Partner with us for victory and transformation', '250.00', 1, 4, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(5, 'Character Builder', 'Help build character in lives across the nation', '500.00', 1, 5, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(6, 'Legacy Maker', 'Create a lasting legacy of faith and character', '1000.00', 1, 6, '2026-07-21 12:10:12', '2026-07-21 12:10:12');

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

CREATE TABLE `episodes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `youtube_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scripture_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `episode_number` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in seconds',
  `status` enum('draft','published','upcoming') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `episode_topics`
--

CREATE TABLE `episode_topics` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `episode_topics`
--

INSERT INTO `episode_topics` (`id`, `name`, `slug`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Character', 'character', 'Building godly character in daily life', 1, 1, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(2, 'Faith', 'faith', 'Strengthening faith and trust in God', 1, 2, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(3, 'Scripture', 'scripture', 'Understanding and applying God\'s Word', 1, 3, '2026-07-21 12:10:12', '2026-07-21 12:10:12'),
(4, 'Life', 'life', 'Practical life lessons and wisdom', 1, 4, '2026-07-21 12:10:12', '2026-07-21 12:10:12');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '0 for free events',
  `status` enum('upcoming','past','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming',
  `max_capacity` int(11) DEFAULT NULL COMMENT 'Optional capacity limit',
  `current_registrations` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `published_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Stripe payment intent ID',
  `amount_paid` decimal(10,2) NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `payment_completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prayer_requests`
--

CREATE TABLE `prayer_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','praying','answered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of permissions: ["manage_events", "manage_donations", "manage_blog", "manage_episodes", "manage_users", "manage_admins"]' CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `permissions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Full system access with all permissions', '[\"manage_events\",\"manage_donations\",\"manage_blog\",\"manage_episodes\",\"manage_users\",\"manage_admins\",\"manage_roles\",\"view_reports\",\"manage_settings\"]', 1, '2026-07-21 12:10:12', '2026-07-21 12:10:12');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_type` enum('event_registration','donation') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'event_registrations.id or donations.id',
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stripe',
  `payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_webhook_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Bcrypt hashed',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Activated via email',
  `activation_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_expires_at` datetime DEFAULT NULL,
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `phone`, `is_active`, `activation_token`, `activation_expires_at`, `reset_token`, `reset_expires_at`, `created_at`, `updated_at`, `last_login`) VALUES
(6, 'ayenisamuel1@gmail.com', '$2y$10$8uwbxmaKWq/eGQ0ouYg54eOJqo1eRC4P.hM4nvGKnz9/ipPyphByq', 'AYENI AKOLADE', '07034388257', 1, NULL, NULL, '0ad79d4ad78f30e9d48f67a4ef96360283b41e17867efbdb89afd433a0b904a9', '2026-07-24 04:20:42', '2026-07-23 15:29:31', '2026-07-24 10:24:52', '2026-07-24 06:18:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `sort_order` (`sort_order`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `published_at` (`published_at`);

--
-- Indexes for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_category` (`blog_post_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `submitted_at` (`submitted_at`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `donation_type_id` (`donation_type_id`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `payment_intent_id` (`payment_intent_id`),
  ADD KEY `stripe_customer_id` (`stripe_customer_id`),
  ADD KEY `stripe_subscription_id` (`stripe_subscription_id`),
  ADD KEY `idx_donations_activation_token` (`activation_token`);

--
-- Indexes for table `donation_types`
--
ALTER TABLE `donation_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `sort_order` (`sort_order`);

--
-- Indexes for table `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `episode_number` (`episode_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `published_at` (`published_at`);

--
-- Indexes for table `episode_topics`
--
ALTER TABLE `episode_topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `sort_order` (`sort_order`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `published_at` (`published_at`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_user` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `payment_intent_id` (`payment_intent_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `submitted_at` (`submitted_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `transaction_type` (`transaction_type`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `payment_intent_id` (`payment_intent_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `activation_token` (`activation_token`),
  ADD KEY `reset_token` (`reset_token`),
  ADD KEY `is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `contact_submissions`
--
ALTER TABLE `contact_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `donation_types`
--
ALTER TABLE `donation_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `episodes`
--
ALTER TABLE `episodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `episode_topics`
--
ALTER TABLE `episode_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `blog_post_categories`
--
ALTER TABLE `blog_post_categories`
  ADD CONSTRAINT `blog_post_categories_category_fk` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_post_categories_post_fk` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_type_fk` FOREIGN KEY (`donation_type_id`) REFERENCES `donation_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `donations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `episodes_topic_fk` FOREIGN KEY (`topic_id`) REFERENCES `episode_topics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `event_registrations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
