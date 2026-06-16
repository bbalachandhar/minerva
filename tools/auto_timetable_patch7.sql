-- =============================================================
-- Auto Timetable Patch 7
-- Adds: `fixed_slots` column on tt_joint_lessons.
--
-- Lets an admin pin a joint lesson's placement(s) to specific
-- day+period slot(s) instead of letting the generator search the
-- whole week. Stored as a JSON array, one entry per placement
-- that should be pinned:
--   [{"placement":0,"day":"Tuesday","period_ids":[3,4]}, ...]
-- Placements not listed are still placed by the normal full-week
-- search. NULL/empty = fully automatic (existing behavior).
--
-- Safe to re-run (idempotent). Uses information_schema + dynamic
-- SQL instead of `ADD COLUMN IF NOT EXISTS` because that clause is
-- a MariaDB-only extension and errors on real MySQL (production
-- runs MySQL 8.0.44; local dev runs MariaDB 10.4, which is why
-- this gap wasn't caught locally).
-- =============================================================

SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'tt_joint_lessons'
    AND COLUMN_NAME  = 'fixed_slots'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `tt_joint_lessons` ADD COLUMN `fixed_slots` TEXT NULL DEFAULT NULL AFTER `all_teachers_required`',
  'SELECT 1 -- fixed_slots already exists'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;
