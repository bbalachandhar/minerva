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
-- Safe to re-run (idempotent).
-- =============================================================

ALTER TABLE `tt_joint_lessons`
  ADD COLUMN IF NOT EXISTS `fixed_slots` TEXT NULL DEFAULT NULL AFTER `all_teachers_required`;
