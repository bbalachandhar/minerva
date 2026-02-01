-- ============================================================================
-- Incidental Fee Permissions Setup for EC2 Database
-- Created: February 1, 2026
-- Purpose: Set up Incidental Fee permissions for Admin, Accountant, Receptionist
-- ============================================================================

-- Step 1: Assign Permission Group to Incidental Fee Categories
-- This ensures they appear in the roles permission UI under "Fees Collection"
UPDATE permission_category 
SET perm_group_id = 2 
WHERE id IN (283, 284, 285, 286) 
AND perm_group_id IS NULL;

-- Step 2: Enable Permission Checkboxes
-- This enables View, Add, Edit, Delete checkboxes in the permission assignment UI
UPDATE permission_category 
SET enable_view = 1, 
    enable_add = 1, 
    enable_edit = 1, 
    enable_delete = 1 
WHERE id IN (283, 284, 285, 286);

-- Step 3: Assign Permissions to Admin Role (role_id=1)
-- Admin gets full access to all Incidental Fee features
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES 
  (1, 283, 1, 1, 1, 1),
  (1, 284, 1, 1, 1, 1),
  (1, 285, 1, 1, 1, 1),
  (1, 286, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE 
  can_view = 1, 
  can_add = 1, 
  can_edit = 1, 
  can_delete = 1;

-- Step 4: Assign Permissions to Accountant Role (role_id=3)
-- Accountant can manage all features but has view-only access to reports
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES 
  (3, 283, 1, 1, 1, 1),
  (3, 284, 1, 1, 1, 1),
  (3, 285, 1, 1, 1, 1),
  (3, 286, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE 
  can_view = 1, 
  can_add = CASE WHEN perm_cat_id = 286 THEN 0 ELSE 1 END, 
  can_edit = CASE WHEN perm_cat_id = 286 THEN 0 ELSE 1 END, 
  can_delete = CASE WHEN perm_cat_id = 286 THEN 0 ELSE 1 END;

-- Step 5: Assign Permissions to Receptionist Role (role_id=6)
-- Receptionist can only collect fees but cannot edit or delete
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES 
  (6, 285, 1, 1, 0, 0)
ON DUPLICATE KEY UPDATE 
  can_view = 1, 
  can_add = 1, 
  can_edit = 0, 
  can_delete = 0;

-- ============================================================================
-- Verification Queries (Run these to verify the setup)
-- ============================================================================

-- Check if permission groups are set correctly
-- SELECT id, name, perm_group_id FROM permission_category WHERE id IN (283,284,285,286) ORDER BY id;

-- Check if enable flags are set correctly
-- SELECT id, name, enable_view, enable_add, enable_edit, enable_delete FROM permission_category WHERE id IN (283,284,285,286);

-- Check if role permissions are assigned correctly
-- SELECT r.name, pc.name, rp.can_view, rp.can_add, rp.can_edit, rp.can_delete 
-- FROM roles_permissions rp 
-- JOIN roles r ON rp.role_id=r.id 
-- JOIN permission_category pc ON rp.perm_cat_id=pc.id 
-- WHERE pc.id IN (283,284,285,286) 
-- ORDER BY r.name, pc.id;

-- ============================================================================
-- Summary of Changes
-- ============================================================================
-- Permission Group ID 2 = "Fees Collection"
-- 
-- Incidental Fee Permissions (IDs 283-286):
--   283: Incidental Fee Type
--   284: Assign Incidental Fee
--   285: Collect Incidental Fee
--   286: Incidental Fee Report
--
-- Role Assignments:
--   Admin (1)       - Full access (View, Add, Edit, Delete) to all features
--   Accountant (3)  - Full access to types/assignments/collections; view-only reports
--   Receptionist(6) - Can only collect fees (View, Add); cannot edit or delete
-- ============================================================================
