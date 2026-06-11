-- =============================================================
-- Auto Timetable Patch 4
-- Run after patch1, patch2, patch3.
-- All statements are idempotent (safe to re-run).
-- =============================================================

-- -----------------------------------------------------------
-- 1. permission_category — tt_dashboard
-- -----------------------------------------------------------
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Dashboard', 'tt_dashboard', 1, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_dashboard');

-- -----------------------------------------------------------
-- 2. roles_permissions — Admin (role_id=1) can_view tt_dashboard
-- -----------------------------------------------------------
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 0, 0, 0
FROM `permission_category` WHERE `short_code` = 'tt_dashboard'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_dashboard'
);

-- -----------------------------------------------------------
-- 3. sidebar_sub_menus — TT Dashboard (show first)
-- -----------------------------------------------------------
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu='tt' LIMIT 1),
  'TT Dashboard',
  'tt_dashboard',
  'admin/tt/dashboard',
  3000,
  'dashboard',
  1
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_dashboard');

-- -----------------------------------------------------------
-- 4. permission_category — tt_lesson_browser
-- -----------------------------------------------------------
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Lesson Browser', 'tt_lesson_browser', 1, 0, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_lesson_browser');

-- -----------------------------------------------------------
-- 5. roles_permissions — Admin can_view tt_lesson_browser
-- -----------------------------------------------------------
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 0, 0, 0
FROM `permission_category` WHERE `short_code` = 'tt_lesson_browser'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_lesson_browser'
);

-- -----------------------------------------------------------
-- 6. sidebar_sub_menus — Lesson Browser
-- -----------------------------------------------------------
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu='tt' LIMIT 1),
  'Lesson Browser',
  'tt_lesson_browser',
  'admin/tt/lesson_browser',
  3000,
  'lesson_browser,get_lesson_browser_data,get_all_subjects',
  1
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_lesson_browser');
