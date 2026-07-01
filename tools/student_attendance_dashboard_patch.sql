-- =============================================================
-- Student Attendance Dashboard Patch
-- Run on all 7 instances. Idempotent — safe to re-run.
-- =============================================================

-- 1. permission_category
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT pc.perm_group_id, 'Student Attendance Dashboard', 'student_attendance_dashboard', 1, 0, 0, 0
FROM `permission_category` pc
WHERE pc.`short_code` = 'attendance_report'
  AND NOT EXISTS (SELECT 1 FROM `permission_category` x WHERE x.`short_code` = 'student_attendance_dashboard')
LIMIT 1;

-- Fallback if attendance_report doesn't exist yet
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT
  COALESCE(
    (SELECT pg.id FROM `permission_group` pg WHERE pg.`short_code` = 'attendance' LIMIT 1),
    1
  ),
  'Student Attendance Dashboard', 'student_attendance_dashboard', 1, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'student_attendance_dashboard');

-- 2. Grant Admin role (role_id=1) can_view
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, pc.id, 1, 0, 0, 0
FROM `permission_category` pc
WHERE pc.`short_code` = 'student_attendance_dashboard'
  AND NOT EXISTS (
    SELECT 1 FROM `roles_permissions` rp
    JOIN `permission_category` rpc ON rpc.id = rp.perm_cat_id
    WHERE rp.role_id = 1 AND rpc.`short_code` = 'student_attendance_dashboard'
  );

-- 3. sidebar_sub_menus — Dashboard (Student Attn)
INSERT INTO `sidebar_sub_menus`
  (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_controller`, `activate_methods`, `is_active`, `level`, `lang_key`)
SELECT
  (SELECT sm.id FROM sidebar_menus sm WHERE sm.activate_menu = 'attendance' LIMIT 1),
  'Dashboard (Student Attn)',
  'student_attendance_dashboard',
  'admin/attendancedashboard/index',
  COALESCE(
    (SELECT ssm.permission_group_id FROM sidebar_sub_menus ssm WHERE ssm.url LIKE '%stuattendence%' LIMIT 1),
    (SELECT ssm2.permission_group_id FROM sidebar_sub_menus ssm2 WHERE ssm2.url LIKE '%subjectattendence%' LIMIT 1)
  ),
  'attendancedashboard',
  'index',
  1,
  1,
  'student_attendance_dashboard'
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'student_attendance_dashboard');

-- 4. Bump all other Attendance sub-menus down so dashboard stays first
UPDATE `sidebar_sub_menus` ssm
JOIN sidebar_menus sm ON sm.id = ssm.sidebar_menu_id AND sm.activate_menu = 'attendance'
SET ssm.`level` = ssm.`level` + 10
WHERE ssm.`key` != 'student_attendance_dashboard'
  AND ssm.`level` < 100;

-- 5. Ensure dashboard is always at level 1
UPDATE `sidebar_sub_menus` SET `level` = 1 WHERE `key` = 'student_attendance_dashboard';
