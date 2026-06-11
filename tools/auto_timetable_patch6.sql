-- =============================================================
-- Auto Timetable Patch 6
-- Fixes:
--   1. activate_controller missing for items inserted in patches 2-5
--      (caused sidebar items to never highlight)
--   2. Reorders submenus by level to match step-by-step workflow
-- Safe to re-run (all statements are idempotent).
-- =============================================================

-- -----------------------------------------------------------
-- 1. Fix activate_controller = 'tt' for all TT submenus
--    that were inserted without it (patches 2-5 omitted it)
-- -----------------------------------------------------------
UPDATE `sidebar_sub_menus`
SET `activate_controller` = 'tt'
WHERE `permission_group_id` = 3000
  AND (`activate_controller` IS NULL OR `activate_controller` = '');

-- -----------------------------------------------------------
-- 2. Set level values to control sidebar display order
--    (ORDER BY level ASC drives the menu order)
--    Steps follow the natural timetable setup workflow.
-- -----------------------------------------------------------
UPDATE `sidebar_sub_menus` SET `level` = 10  WHERE `key` = 'tt_dashboard';
UPDATE `sidebar_sub_menus` SET `level` = 20  WHERE `key` = 'tt_periods';
UPDATE `sidebar_sub_menus` SET `level` = 30  WHERE `key` = 'tt_rooms';
UPDATE `sidebar_sub_menus` SET `level` = 40  WHERE `key` = 'tt_batches';
UPDATE `sidebar_sub_menus` SET `level` = 50  WHERE `key` = 'tt_subject_colors';
UPDATE `sidebar_sub_menus` SET `level` = 60  WHERE `key` = 'tt_class_avail';
UPDATE `sidebar_sub_menus` SET `level` = 70  WHERE `key` = 'tt_room_avail';
UPDATE `sidebar_sub_menus` SET `level` = 80  WHERE `key` = 'tt_subject_avail';
UPDATE `sidebar_sub_menus` SET `level` = 90  WHERE `key` = 'tt_subject_load';
UPDATE `sidebar_sub_menus` SET `level` = 100 WHERE `key` = 'tt_teacher_constr';
UPDATE `sidebar_sub_menus` SET `level` = 110 WHERE `key` = 'tt_teacher_avail';
UPDATE `sidebar_sub_menus` SET `level` = 120 WHERE `key` = 'tt_generate';
UPDATE `sidebar_sub_menus` SET `level` = 130 WHERE `key` = 'tt_lesson_browser';
UPDATE `sidebar_sub_menus` SET `level` = 140 WHERE `key` = 'tt_joint_lessons';
UPDATE `sidebar_sub_menus` SET `level` = 150 WHERE `key` = 'tt_class_grid';
UPDATE `sidebar_sub_menus` SET `level` = 160 WHERE `key` = 'tt_teacher_view';
UPDATE `sidebar_sub_menus` SET `level` = 170 WHERE `key` = 'tt_substitution';
UPDATE `sidebar_sub_menus` SET `level` = 180 WHERE `key` = 'tt_reports';
