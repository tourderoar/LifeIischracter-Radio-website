-- Life is Character Radio - Database Schema
-- Location: /setup/database.sql
-- 
-- Complete database schema with 13 tables
-- Run this file to create all necessary tables

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `life_character_radio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `life_character_radio`;

-- ============================================================
-- TABLE 1: roles
-- ============================================================
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` json NOT NULL COMMENT 'Array of permissions: ["manage_events", "manage_donations", "manage_blog", "manage_episodes", "manage_users", "manage_admins"]',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2: admins
-- ============================================================
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed',
  `full_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `admins_role_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3: users
-- ============================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed',
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Activated via email',
  `activation_token` varchar(64) DEFAULT NULL,
  `activation_expires_at` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `activation_token` (`activation_token`),
  KEY `reset_token` (`reset_token`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 4: events
-- ============================================================
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '0 for free events',
  `status` enum('upcoming','past','cancelled') NOT NULL DEFAULT 'upcoming',
  `max_capacity` int(11) DEFAULT NULL COMMENT 'Optional capacity limit',
  `current_registrations` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `event_date` (`event_date`),
  KEY `created_by` (`created_by`),
  KEY `published_at` (`published_at`),
  CONSTRAINT `events_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 5: event_registrations
-- ============================================================
CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL COMMENT 'Stripe payment intent ID',
  `amount_paid` decimal(10,2) NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_user` (`event_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `payment_status` (`payment_status`),
  KEY `payment_intent_id` (`payment_intent_id`),
  CONSTRAINT `event_registrations_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_registrations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 6: episode_topics
-- ============================================================
CREATE TABLE `episode_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 7: blog_categories
-- ============================================================
CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 8: donation_types
-- ============================================================
CREATE TABLE `donation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `suggested_amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 9: donations
-- ============================================================
CREATE TABLE `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for anonymous donations',
  `donation_type_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_frequency` enum('one-time','monthly','quarterly','yearly') NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL,
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL COMMENT 'For recurring donations',
  `stripe_subscription_id` varchar(255) DEFAULT NULL COMMENT 'For recurring donations',
  `donated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `donation_type_id` (`donation_type_id`),
  KEY `payment_status` (`payment_status`),
  KEY `payment_intent_id` (`payment_intent_id`),
  KEY `stripe_customer_id` (`stripe_customer_id`),
  KEY `stripe_subscription_id` (`stripe_subscription_id`),
  CONSTRAINT `donations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `donations_type_fk` FOREIGN KEY (`donation_type_id`) REFERENCES `donation_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 10: blog_posts
-- ============================================================
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `scripture_reference` varchar(255) DEFAULT NULL,
  `tags` json DEFAULT NULL COMMENT 'Free-form tags: ["faith", "victory"]',
  `author_name` varchar(255) NOT NULL DEFAULT 'Paul Kumeni Agbo',
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  KEY `published_at` (`published_at`),
  CONSTRAINT `blog_posts_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 11: blog_post_categories
-- ============================================================
CREATE TABLE `blog_post_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_category` (`blog_post_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `blog_post_categories_post_fk` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blog_post_categories_category_fk` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 12: episodes
-- ============================================================
CREATE TABLE `episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `youtube_url` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `scripture_reference` varchar(255) DEFAULT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `episode_number` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in seconds',
  `status` enum('draft','published','upcoming') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `topic_id` (`topic_id`),
  KEY `episode_number` (`episode_number`),
  KEY `created_by` (`created_by`),
  KEY `published_at` (`published_at`),
  CONSTRAINT `episodes_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `episodes_topic_fk` FOREIGN KEY (`topic_id`) REFERENCES `episode_topics` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 13: contact_submissions
-- ============================================================
CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('new','read','responded') NOT NULL DEFAULT 'new',
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 14: prayer_requests
-- ============================================================
CREATE TABLE `prayer_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `request` text NOT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('new','praying','answered') NOT NULL DEFAULT 'new',
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 15: newsletter_subscribers
-- ============================================================
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 16: transactions
-- ============================================================
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `transaction_type` enum('event_registration','donation') NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'event_registrations.id or donations.id',
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'stripe',
  `payment_intent_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL,
  `stripe_webhook_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `payment_status` (`payment_status`),
  KEY `payment_intent_id` (`payment_intent_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `transactions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
