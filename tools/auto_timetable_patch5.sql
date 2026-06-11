-- =============================================================
-- Auto Timetable Patch 5 — Joint / Cross-Class Lessons
-- Run after patches 1-4.  All statements are idempotent.
-- =============================================================

-- -----------------------------------------------------------
-- 1. tt_joint_lessons — master record for a combined lesson
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tt_joint_lessons` (
  `id`                   INT          NOT NULL AUTO_INCREMENT,
  `session_id`           INT          NOT NULL,
  `name`                 VARCHAR(100) NOT NULL COMMENT 'e.g. PE Combined 3A+3B',
  `subject_id`           INT          NOT NULL,
  `staff_id`             INT          NULL,
  `alt_staff_id`         INT          NULL,
  `room_id`              INT          NULL,
  `periods_per_week`     INT          NOT NULL DEFAULT 1,
  `consecutive_periods`  INT          NOT NULL DEFAULT 1,
  `max_per_day`          INT          NOT NULL DEFAULT 1,
  `distribute_evenly`    TINYINT(1)   NOT NULL DEFAULT 1,
  `priority`             INT          NOT NULL DEFAULT 5,
  `notes`                VARCHAR(255) NULL,
  `created_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jl_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 2. tt_joint_lesson_classes — participating class-sections
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tt_joint_lesson_classes` (
  `id`               INT NOT NULL AUTO_INCREMENT,
  `joint_lesson_id`  INT NOT NULL,
  `class_id`         INT NOT NULL,
  `section_id`       INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_jlc_joint` (`joint_lesson_id`),
  KEY `idx_jlc_class` (`class_id`, `section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- 3. permission_category — tt_joint_lessons
-- -----------------------------------------------------------
INSERT INTO `permission_category`
  (`perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT 3000, 'TT Joint Lessons', 'tt_joint_lessons', 1, 1, 1, 1
WHERE NOT EXISTS (
  SELECT 1 FROM `permission_category` WHERE `short_code` = 'tt_joint_lessons'
);

-- -----------------------------------------------------------
-- 4. roles_permissions — Admin full access to tt_joint_lessons
-- -----------------------------------------------------------
INSERT INTO `roles_permissions`
  (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 1, id, 1, 1, 1, 1
FROM `permission_category` WHERE `short_code` = 'tt_joint_lessons'
AND NOT EXISTS (
  SELECT 1 FROM `roles_permissions` rp
  JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
  WHERE rp.role_id = 1 AND pc.short_code = 'tt_joint_lessons'
);

-- -----------------------------------------------------------
-- 5. sidebar_sub_menus — Joint Lessons
-- -----------------------------------------------------------
INSERT INTO `sidebar_sub_menus`
  (`sidebar_menu_id`, `menu`, `key`, `url`, `permission_group_id`, `activate_methods`, `is_active`)
SELECT
  (SELECT id FROM sidebar_menus WHERE activate_menu = 'tt' LIMIT 1),
  'Joint Lessons',
  'tt_joint_lessons',
  'admin/tt/joint_lessons',
  3000,
  'joint_lessons,save_joint_lesson,delete_joint_lesson,get_joint_lesson,get_joint_classes',
  1
WHERE NOT EXISTS (
  SELECT 1 FROM `sidebar_sub_menus` WHERE `key` = 'tt_joint_lessons'
);
