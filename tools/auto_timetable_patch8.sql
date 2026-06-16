-- =============================================================
-- Auto Timetable Patch 8
-- Adds: "Teacher Workload" sidebar submenu link.
--
-- The Workload Dashboard previously was only reachable from inside
-- the Auto Generate screen's "Workload Dashboard" button — not
-- sufficient for checking load status outside the generation flow.
-- This surfaces it as its own sidebar item, right after Subject
-- Load (level 90) and before Teacher Constraints (level 100).
--
-- Safe to re-run (idempotent).
-- =============================================================

SET @tt_menu_id = (SELECT id FROM sidebar_menus WHERE activate_menu = 'tt' LIMIT 1);

INSERT INTO `sidebar_sub_menus`
  (`sidebar_menu_id`, `menu`, `key`, `lang_key`, `url`, `level`, `access_permissions`, `permission_group_id`, `activate_controller`, `activate_methods`, `is_active`)
SELECT @tt_menu_id, 'Teacher Workload', 'tt_workload_dashboard', 'tt_workload_dashboard',
       'admin/tt/teacher_workload_dashboard', 95, "('tt_subject_load','can_view')", 3000, 'tt',
       'teacher_workload_dashboard,get_pregeneration_workload,reassign_subject_teacher', 1
WHERE @tt_menu_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM sidebar_sub_menus WHERE sidebar_menu_id = @tt_menu_id AND `key` = 'tt_workload_dashboard'
  );
