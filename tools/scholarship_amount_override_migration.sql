-- Migration: Add override_amount, override_comment, override_by, override_at
-- columns to scholarship_applications table.
-- Run once on all production instances.

ALTER TABLE `scholarship_applications`
    ADD COLUMN `override_amount`  decimal(10,2) DEFAULT NULL            COMMENT 'Admin-overridden scholarship amount (NULL = use type default)',
    ADD COLUMN `override_comment` text          DEFAULT NULL            COMMENT 'Mandatory reason for the override',
    ADD COLUMN `override_by`      int(11)       DEFAULT NULL            COMMENT 'staff.id who set the override',
    ADD COLUMN `override_at`      datetime      DEFAULT NULL            COMMENT 'When the override was last set';
