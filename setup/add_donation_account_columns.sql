-- Add columns to donations table for guest account creation
-- Run this migration to add support for automatic account creation on donations

ALTER TABLE donations
ADD COLUMN IF NOT EXISTS account_created TINYINT(1) DEFAULT 0 COMMENT 'Whether an account was created with this donation',
ADD COLUMN IF NOT EXISTS activation_token VARCHAR(64) DEFAULT NULL COMMENT 'Activation token if account was created';

-- Add index for activation token lookups
CREATE INDEX IF NOT EXISTS idx_donations_activation_token ON donations(activation_token);
