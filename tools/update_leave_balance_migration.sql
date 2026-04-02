-- Migration: Add Update Leave Balance feature
-- Date: 2026-04-02
-- Run this on EC2 (production DB: mcekknagar)

-- 1. Permission category (HR group id=18)
INSERT IGNORE INTO permission_category (perm_group_id, name, short_code, enable_view, enable_add, enable_edit, enable_delete)
VALUES (18, 'update_leave_balance', 'update_leave_balance', 1, 0, 1, 0);

-- 2. Grant to Super Admin (role_id=7) and Admin (role_id=1)
INSERT IGNORE INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete)
SELECT 7, id, 1, 0, 1, 0 FROM permission_category WHERE name='update_leave_balance';

INSERT IGNORE INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete)
SELECT 1, id, 1, 0, 1, 0 FROM permission_category WHERE name='update_leave_balance';

-- 3. Sidebar sub-menu under Human Resource (sidebar_menu_id=15)
INSERT IGNORE INTO sidebar_sub_menus
  (sidebar_menu_id, menu, lang_key, url, level, access_permissions, activate_controller, activate_methods, is_active)
VALUES
  (15, 'Update Leave Balance', 'update_leave_balance', 'admin/update_leave_balance/index',
   1, "('update_leave_balance', 'can_view')", 'Update_leave_balance', 'index', 1);
