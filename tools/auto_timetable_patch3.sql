-- =============================================================
-- Auto Timetable Patch 3
-- Run after patch1 and patch2.
-- All statements are idempotent (safe to re-run).
-- =============================================================

-- -----------------------------------------------------------
-- 1. CREATE TABLE tt_room_unavail
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tt_room_unavail` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `session_id` INT          NOT NULL,
  `room_id`    INT          NOT NULL,
  `day`        VARCHAR(20)  NOT NULL,
  `period_id`  INT          NOT NULL,
  `reason`     VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ru_session_room` (`session_id`, `room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 2. CREATE TABLE tt_subject_unavail
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tt_subject_unavail` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `session_id` INT          NOT NULL,
  `subject_id` INT          NOT NULL,
  `day`        VARCHAR(20)  NOT NULL,
  `period_id`  INT          NOT NULL,
  `reason`     VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_su_session_subject` (`session_id`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 3. ALTER tt_subject_load — add min_per_day (On1 flag)
--    Uses PREPARE/EXECUTE for MySQL 5.7 compatibility (no IF NOT EXISTS on ALTER)
-- -----------------------------------------------------------
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'tt_subject_load'
    AND COLUMN_NAME  = 'min_per_day'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `tt_subject_load` ADD COLUMN `min_per_day` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1 -- min_per_day already exists'
);
PREPARE _stmt FROM @sql;
EXECUTE _stmt;
DEALLOCATE PREPARE _stmt;

-- -----------------------------------------------------------
-- 4. permission_category — add tt_room_avail
-- -----------------------------------------------------------
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Room Availability', 'tt_room_avail', 1, 1, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_room_avail');

-- -----------------------------------------------------------
-- 5. roles_permissions — grant Admin (role_id=1) access to tt_room_avail
-- -----------------------------------------------------------
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 1, 0, 0
FROM `permission_category` WHERE `short_code` = 'tt_room_avail'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_room_avail'
);

-- -----------------------------------------------------------
-- 6. sidebar_sub_menus — add Room Availability menu entry
-- -----------------------------------------------------------
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu='tt' LIMIT 1),
  'Room Availability',
  'tt_room_avail',
  'admin/tt/room_unavail',
  3000,
  'room_unavail,save_room_unavail,get_room_unavail',
  1
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_room_avail');

-- -----------------------------------------------------------
-- 7. permission_category — add tt_subject_avail
-- -----------------------------------------------------------
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Subject Time Off', 'tt_subject_avail', 1, 1, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_subject_avail');

-- -----------------------------------------------------------
-- 8. roles_permissions — grant Admin (role_id=1) access to tt_subject_avail
-- -----------------------------------------------------------
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 1, 0, 0
FROM `permission_category` WHERE `short_code` = 'tt_subject_avail'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_subject_avail'
);

-- -----------------------------------------------------------
-- 9. sidebar_sub_menus — add Subject Time Off menu entry
-- -----------------------------------------------------------
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu='tt' LIMIT 1),
  'Subject Time Off',
  'tt_subject_avail',
  'admin/tt/subject_unavail',
  3000,
  'subject_unavail,save_subject_unavail,get_subject_unavail',
  1
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_subject_avail');
