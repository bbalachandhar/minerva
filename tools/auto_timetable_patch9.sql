-- =============================================================
-- Auto Timetable Patch 9
-- Widens tt_gen_log.conflict_details from TEXT (64KB) to MEDIUMTEXT (16MB).
--
-- A generation run with many conflicts (one JSON object per failed
-- placement/On1 warning) can exceed 64KB. MySQL truncates TEXT
-- columns that overflow rather than erroring, leaving invalid,
-- truncated JSON in the column. Any code that JSON.parse()s it then
-- silently fails — this is exactly why the Generate Preview page can
-- show a "no conflicts" success banner while the conflict count
-- stat (a separate plain INT column) correctly shows a large number.
--
-- Safe to re-run (idempotent — MODIFY COLUMN is a no-op if already
-- MEDIUMTEXT).
-- =============================================================

ALTER TABLE `tt_gen_log` MODIFY COLUMN `conflict_details` MEDIUMTEXT DEFAULT NULL;
