-- =============================================================
-- Auto Timetable Patch 10
-- Adds: `is_free_period` / `free_period_label` on tt_draft_entries,
-- mirroring the columns tt_entries already has for manually-marked
-- Free Period cells in Class Grid.
--
-- Lets the generator's optional gap-fill pass (fill_free_periods
-- setting) mark a backfilled empty cell as a generic Free Period
-- placeholder when no configured subject's teacher is free there,
-- using the exact same convention the Class Grid / Teacher View /
-- Print Grid / student timetable already render.
--
-- Safe to re-run (idempotent).
-- =============================================================

SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_draft_entries' AND COLUMN_NAME = 'is_free_period'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `tt_draft_entries` ADD COLUMN `is_free_period` TINYINT(1) NOT NULL DEFAULT 0 AFTER `batch_id`',
  'SELECT 1 -- is_free_period already exists'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_draft_entries' AND COLUMN_NAME = 'free_period_label'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `tt_draft_entries` ADD COLUMN `free_period_label` VARCHAR(50) DEFAULT NULL AFTER `is_free_period`',
  'SELECT 1 -- free_period_label already exists'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;
