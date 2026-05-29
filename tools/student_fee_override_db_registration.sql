-- ============================================================
-- Student Fee Override — DB Registration Script
-- Idempotent: safe to run multiple times on any instance.
-- Run on LOCAL first, then all production DBs when deploying.
-- ============================================================

-- 1. Add permission_category entry only if not already present
INSERT INTO `permission_category`
    (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 2, 'Student Fee Override', 'student_fee_override', 1, 1, 0, 1
WHERE NOT EXISTS (
    SELECT 1 FROM `permission_category` WHERE `short_code` = 'student_fee_override'
);

-- 2. Grant full permission to Admin role (role_id = 1) — resolved by short_code
INSERT INTO `roles_permissions`
    (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, pc.id, 1, 1, 1, 1
FROM `permission_category` pc
WHERE pc.`short_code` = 'student_fee_override'
  AND NOT EXISTS (
    SELECT 1 FROM `roles_permissions` rp
    WHERE rp.`role_id` = 1 AND rp.`perm_cat_id` = pc.id
);

-- 3. Add sidebar menu entry under "Fees Collection" only if not already present
INSERT INTO `sidebar_sub_menus`
    (`sidebar_menu_id`, `menu`, `lang_key`, `url`, `level`, `access_permissions`,
     `activate_controller`, `activate_methods`, `is_active`)
SELECT
    3, 'Student Fee Override', 'student_fee_override',
    'admin/student_fee_override', 1,
    '(''student_fee_override'', ''can_view'')',
    'student_fee_override', 'index,save,delete,bulk_import,exportformat', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `sidebar_sub_menus` WHERE `activate_controller` = 'student_fee_override'
);

-- ============================================================
-- Verify inserts
-- ============================================================
SELECT id, perm_group_id, name, short_code FROM permission_category WHERE short_code = 'student_fee_override';
SELECT id, sidebar_menu_id, menu, url FROM sidebar_sub_menus WHERE activate_controller = 'student_fee_override';
