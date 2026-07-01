-- =============================================================
-- Attendance Reports Phase 2 Patch (2026-07-01)
-- Adds: Teacher Marking Coverage report permission + sidebar entry
-- Idempotent — safe to re-run on all 7 instances.
-- =============================================================

-- 1. permission_category — teacher_marking_coverage
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT pc.perm_group_id, 'Teacher Marking Coverage', 'teacher_marking_coverage', 1, 0, 0, 0
FROM `permission_category` pc
WHERE pc.`short_code` = 'student_period_attendance_report'
  AND NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'teacher_marking_coverage')
LIMIT 1;

-- Fallback if student_period_attendance_report doesn't exist
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT pc.perm_group_id, 'Teacher Marking Coverage', 'teacher_marking_coverage', 1, 0, 0, 0
FROM `permission_category` pc
WHERE pc.`short_code` = 'attendance_report'
  AND NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'teacher_marking_coverage')
LIMIT 1;

-- 2. Grant Admin (role_id=1) can_view
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, pc.id, 1, 0, 0, 0
FROM `permission_category` pc
WHERE pc.`short_code` = 'teacher_marking_coverage'
  AND NOT EXISTS (
    SELECT 1 FROM `roles_permissions` rp
    JOIN `permission_category` rpc ON rpc.id = rp.perm_cat_id
    WHERE rp.role_id = 1 AND rpc.`short_code` = 'teacher_marking_coverage'
  );

-- 3. sidebar_sub_menus — Teacher Coverage under Reports menu
INSERT INTO `sidebar_sub_menus`
  (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_controller`, `activate_methods`, `is_active`, `level`, `lang_key`, `access_permissions`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu = 'reports' LIMIT 1),
  'Teacher Coverage', 'teacher_marking_coverage',
  'attendencereports/teachermarkingcoverage',
  (SELECT sidebar_menu_id FROM sidebar_sub_menus WHERE url='attendencereports/attendance' LIMIT 1),
  'attendencereports', 'teachermarkingcoverage', 1, 39,
  'teacher_marking_coverage', "('teacher_marking_coverage','can_view')"
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'teacher_marking_coverage');
