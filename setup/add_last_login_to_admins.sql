-- Migration: Add last_login column to admins table
-- Run this if your database was created before this column was added

USE `life_character_radio`;

-- Add last_login column to admins table
ALTER TABLE `admins` 
ADD COLUMN `last_login` datetime DEFAULT NULL 
AFTER `is_active`;

-- Add index for last_login (optional, for performance)
-- ALTER TABLE `admins` ADD INDEX `last_login` (`last_login`);

SELECT 'Migration completed: last_login column added to admins table' as status;
