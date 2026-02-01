-- Assign Incidental Fee Permissions to Roles
-- Admin (role_id=1): all permissions with full access (can_view, can_add, can_edit, can_delete)
-- Accountant (role_id=3): all permissions with add, edit, delete, but limited view
-- Receptionist (role_id=6): collect and view only

-- Incidental Fee Type (283)
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (1, 283, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (3, 283, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

-- Assign Incidental Fee (284)
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (1, 284, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (3, 284, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

-- Collect Incidental Fee (285)
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (1, 285, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (3, 285, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (6, 285, 1, 1, 0, 0) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=0, can_delete=0;

-- Incidental Fee Report (286)
INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (1, 286, 1, 1, 1, 1) ON DUPLICATE KEY UPDATE can_view=1, can_add=1, can_edit=1, can_delete=1;

INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete) 
VALUES (3, 286, 1, 0, 0, 0) ON DUPLICATE KEY UPDATE can_view=1, can_add=0, can_edit=0, can_delete=0;
