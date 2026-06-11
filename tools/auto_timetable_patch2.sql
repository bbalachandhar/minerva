-- ============================================================
-- Auto Timetable Patch 2 — ASC Gap Implementations
-- Run on all databases: mcekknagar + 7 EC2 instances
-- Safe to run multiple times (all statements are idempotent)
-- ============================================================

-- -----------------------------------------------------------
-- 1. subjects table — add tt_color and tt_abbr columns
-- -----------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'tt_color');
SET @sql = IF(@col = 0,
  'ALTER TABLE `subjects` ADD COLUMN `tt_color` VARCHAR(7) DEFAULT NULL',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subjects' AND COLUMN_NAME = 'tt_abbr');
SET @sql = IF(@col = 0,
  'ALTER TABLE `subjects` ADD COLUMN `tt_abbr` VARCHAR(10) DEFAULT NULL',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------
-- 2. tt_rooms — add is_shared flag
-- -----------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_rooms' AND COLUMN_NAME = 'is_shared');
SET @sql = IF(@col = 0,
  'ALTER TABLE `tt_rooms` ADD COLUMN `is_shared` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------
-- 3. tt_teacher_constraints — add max_gap_per_day + preferred_room_id
-- -----------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_teacher_constraints' AND COLUMN_NAME = 'max_gap_per_day');
SET @sql = IF(@col = 0,
  'ALTER TABLE `tt_teacher_constraints` ADD COLUMN `max_gap_per_day` INT DEFAULT NULL',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_teacher_constraints' AND COLUMN_NAME = 'preferred_room_id');
SET @sql = IF(@col = 0,
  'ALTER TABLE `tt_teacher_constraints` ADD COLUMN `preferred_room_id` INT DEFAULT NULL',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------
-- 4. tt_subject_load — add max_per_day + distribute_evenly
-- -----------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_subject_load' AND COLUMN_NAME = 'max_per_day');
SET @sql = IF(@col = 0,
  'ALTER TABLE `tt_subject_load` ADD COLUMN `max_per_day` INT NOT NULL DEFAULT 2',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tt_subject_load' AND COLUMN_NAME = 'distribute_evenly');
SET @sql = IF(@col = 0,
  'ALTER TABLE `tt_subject_load` ADD COLUMN `distribute_evenly` TINYINT(1) NOT NULL DEFAULT 1',
  'SELECT 1 INTO @dummy');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------
-- 5. tt_class_unavail — new table
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tt_class_unavail` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `session_id` INT(11)     NOT NULL,
  `class_id`   INT(11)     NOT NULL,
  `section_id` INT(11)     NOT NULL,
  `day`        VARCHAR(20) NOT NULL,
  `period_id`  INT(11)     NOT NULL,
  `reason`     VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_class_unavail` (`session_id`,`class_id`,`section_id`,`day`,`period_id`),
  KEY `idx_class_unavail` (`session_id`,`class_id`,`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 6. permission_category — add tt_class_avail
-- -----------------------------------------------------------
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Class Availability', 'tt_class_avail', 1, 1, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_class_avail');

-- -----------------------------------------------------------
-- 7. roles_permissions — grant Admin (role_id=1) full access
-- -----------------------------------------------------------
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 1, 0, 0
FROM `permission_category` WHERE `short_code` = 'tt_class_avail'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_class_avail'
);

-- -----------------------------------------------------------
-- 8. sidebar_sub_menus — add Class Availability menu entry
-- -----------------------------------------------------------
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu='tt' LIMIT 1),
  'Class Availability',
  'tt_class_avail',
  'admin/tt/class_unavail',
  3000,
  'class_unavail,save_class_unavail,get_class_unavail',
  1
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_class_avail');

-- -----------------------------------------------------------
-- 9. permission_category — add tt_subject_colors
-- -----------------------------------------------------------
INSERT INTO `permission_category` (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Subject Colors', 'tt_subject_colors', 1, 1, 0, 0
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_subject_colors');

-- 10. roles_permissions — grant Admin (role_id=1) full access to tt_subject_colors
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 1, 0, 0
FROM `permission_category` WHERE `short_code` = 'tt_subject_colors'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_subject_colors'
);

-- 11. sidebar_sub_menus — add Subject Colors menu entry
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu='tt' LIMIT 1),
  'Subject Colors',
  'tt_subject_colors',
  'admin/tt/subject_colors',
  3000,
  'subject_colors,save_subject_colors',
  1
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_subject_colors');
