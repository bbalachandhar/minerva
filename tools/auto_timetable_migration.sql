-- =============================================================================
-- AUTO TIMETABLE MODULE MIGRATION
-- Module: Auto Timetable (tt_*)
-- Run this script once on your Minerva database.
-- Safe to re-run: all CREATE TABLE uses IF NOT EXISTS, INSERTs use ON DUPLICATE KEY UPDATE.
-- =============================================================================

-- =============================================================================
-- SECTION 1: CORE TABLES
-- =============================================================================

-- 1. Period Slots (time structure master)
CREATE TABLE IF NOT EXISTS `tt_periods` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `session_id`  INT(11)      NOT NULL,
  `name`        VARCHAR(50)  NOT NULL COMMENT 'e.g. Period 1, Break, Lunch',
  `start_time`  TIME         NOT NULL,
  `end_time`    TIME         NOT NULL,
  `is_break`    TINYINT(1)   NOT NULL DEFAULT 0,
  `break_label` VARCHAR(50)           DEFAULT NULL COMMENT 'Short Break / Lunch Break',
  `sort_order`  INT(11)      NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tt_periods_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Rooms Master
CREATE TABLE IF NOT EXISTS `tt_rooms` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100) NOT NULL,
  `room_number`   VARCHAR(20)           DEFAULT NULL,
  `capacity`      INT(11)      NOT NULL DEFAULT 60,
  `room_type`     ENUM('classroom','lab','seminar','hall','other') NOT NULL DEFAULT 'classroom',
  `department_id` INT(11)               DEFAULT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tt_rooms_type` (`room_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Batches (lab/practical split groups)
CREATE TABLE IF NOT EXISTS `tt_batches` (
  `id`            INT(11)     NOT NULL AUTO_INCREMENT,
  `session_id`    INT(11)     NOT NULL,
  `class_id`      INT(11)     NOT NULL,
  `section_id`    INT(11)     NOT NULL,
  `batch_name`    VARCHAR(10) NOT NULL COMMENT 'A, B, C',
  `student_count` INT(11)     NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tt_batches` (`session_id`,`class_id`,`section_id`,`batch_name`),
  KEY `idx_tt_batches_class` (`class_id`,`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Subject Load Configuration (the "cards" — what to schedule)
CREATE TABLE IF NOT EXISTS `tt_subject_load` (
  `id`                       INT(11)  NOT NULL AUTO_INCREMENT,
  `session_id`               INT(11)  NOT NULL,
  `class_id`                 INT(11)  NOT NULL,
  `section_id`               INT(11)  NOT NULL,
  `subject_group_id`         INT(11)  NOT NULL,
  `subject_group_subject_id` INT(11)  NOT NULL,
  `staff_id`                 INT(11)  NOT NULL,
  `alt_staff_id`             INT(11)           DEFAULT NULL COMMENT 'Backup teacher',
  `periods_per_week`         INT(11)  NOT NULL DEFAULT 1,
  `consecutive_periods`      INT(11)  NOT NULL DEFAULT 1 COMMENT '1=normal,2=double,3=triple(lab)',
  `preferred_room_type`      ENUM('any','classroom','lab','seminar','hall') NOT NULL DEFAULT 'any',
  `preferred_room_id`        INT(11)           DEFAULT NULL,
  `batch_id`                 INT(11)           DEFAULT NULL COMMENT 'NULL=full class',
  `priority`                 INT(11)  NOT NULL DEFAULT 5 COMMENT '1-10, higher scheduled first',
  `created_at`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tt_subject_load` (`session_id`,`class_id`,`section_id`,`subject_group_subject_id`,`batch_id`),
  KEY `idx_tt_sl_class` (`class_id`,`section_id`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Teacher Constraints (per-teacher scheduling preferences)
CREATE TABLE IF NOT EXISTS `tt_teacher_constraints` (
  `id`                    INT(11)  NOT NULL AUTO_INCREMENT,
  `session_id`            INT(11)  NOT NULL,
  `staff_id`              INT(11)  NOT NULL,
  `max_periods_per_day`   INT(11)  NOT NULL DEFAULT 6,
  `max_periods_per_week`  INT(11)  NOT NULL DEFAULT 30,
  `min_free_per_day`      INT(11)  NOT NULL DEFAULT 0,
  `preferred_start_time`  TIME              DEFAULT NULL COMMENT 'Prefer not before this time',
  `preferred_end_time`    TIME              DEFAULT NULL COMMENT 'Prefer not after this time',
  `avoid_first_period`    TINYINT(1) NOT NULL DEFAULT 0,
  `avoid_last_period`     TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tt_tc_staff_session` (`staff_id`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Teacher Unavailability (specific blocked day+period)
CREATE TABLE IF NOT EXISTS `tt_teacher_unavail` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `session_id` INT(11)      NOT NULL,
  `staff_id`   INT(11)      NOT NULL,
  `day`        VARCHAR(20)  NOT NULL COMMENT 'Monday..Saturday',
  `period_id`  INT(11)      NOT NULL,
  `reason`     VARCHAR(200)          DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tt_unavail` (`session_id`,`staff_id`,`day`,`period_id`),
  KEY `idx_tt_unavail_staff` (`staff_id`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Live Timetable Entries (confirmed timetable)
CREATE TABLE IF NOT EXISTS `tt_entries` (
  `id`                       INT(11)      NOT NULL AUTO_INCREMENT,
  `session_id`               INT(11)      NOT NULL,
  `class_id`                 INT(11)      NOT NULL,
  `section_id`               INT(11)      NOT NULL,
  `subject_group_id`         INT(11)               DEFAULT NULL,
  `subject_group_subject_id` INT(11)               DEFAULT NULL,
  `staff_id`                 INT(11)               DEFAULT NULL,
  `period_id`                INT(11)      NOT NULL,
  `day`                      VARCHAR(20)  NOT NULL,
  `room_id`                  INT(11)               DEFAULT NULL,
  `batch_id`                 INT(11)               DEFAULT NULL COMMENT 'NULL=full class',
  `is_locked`                TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Locked entries survive re-generation',
  `is_free_period`           TINYINT(1)   NOT NULL DEFAULT 0,
  `free_period_label`        VARCHAR(50)           DEFAULT NULL COMMENT 'PT, Library, Free, Assembly',
  `entry_type`               ENUM('manual','auto') NOT NULL DEFAULT 'manual',
  `created_at`               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tt_entries_slot` (`session_id`,`class_id`,`section_id`,`day`,`period_id`,`batch_id`),
  KEY `idx_tt_entries_class` (`class_id`,`section_id`,`session_id`),
  KEY `idx_tt_entries_staff` (`staff_id`,`session_id`),
  KEY `idx_tt_entries_room`  (`room_id`,`day`,`period_id`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Draft Entries (auto-gen preview before confirming)
CREATE TABLE IF NOT EXISTS `tt_draft_entries` (
  `id`                       INT(11)     NOT NULL AUTO_INCREMENT,
  `gen_log_id`               INT(11)     NOT NULL,
  `session_id`               INT(11)     NOT NULL,
  `class_id`                 INT(11)     NOT NULL,
  `section_id`               INT(11)     NOT NULL,
  `subject_group_id`         INT(11)              DEFAULT NULL,
  `subject_group_subject_id` INT(11)              DEFAULT NULL,
  `staff_id`                 INT(11)              DEFAULT NULL,
  `period_id`                INT(11)     NOT NULL,
  `day`                      VARCHAR(20) NOT NULL,
  `room_id`                  INT(11)              DEFAULT NULL,
  `batch_id`                 INT(11)              DEFAULT NULL,
  `created_at`               TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tt_draft_log`   (`gen_log_id`),
  KEY `idx_tt_draft_class` (`class_id`,`section_id`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Generation Run Log
CREATE TABLE IF NOT EXISTS `tt_gen_log` (
  `id`               INT(11)         NOT NULL AUTO_INCREMENT,
  `session_id`       INT(11)         NOT NULL,
  `generated_by`     INT(11)         NOT NULL COMMENT 'staff_id',
  `generated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `class_scope`      TEXT                     DEFAULT NULL COMMENT 'JSON: class+section ids included',
  `status`           ENUM('running','completed','failed') NOT NULL DEFAULT 'running',
  `total_required`   INT(11)         NOT NULL DEFAULT 0,
  `total_placed`     INT(11)         NOT NULL DEFAULT 0,
  `total_conflicts`  INT(11)         NOT NULL DEFAULT 0,
  `quality_score`    DECIMAL(5,2)    NOT NULL DEFAULT 0.00 COMMENT 'placed/required * 100',
  `conflict_details` TEXT                     DEFAULT NULL COMMENT 'JSON: unplaced subject details',
  `settings_json`    TEXT                     DEFAULT NULL COMMENT 'Snapshot of constraints used',
  `confirmed_at`     TIMESTAMP                NULL DEFAULT NULL,
  `confirmed_by`     INT(11)                  DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tt_gen_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Substitution / Rescheduling Log
CREATE TABLE IF NOT EXISTS `tt_substitutions` (
  `id`                       INT(11)      NOT NULL AUTO_INCREMENT,
  `session_id`               INT(11)      NOT NULL,
  `absent_staff_id`          INT(11)      NOT NULL,
  `substitute_staff_id`      INT(11)               DEFAULT NULL,
  `tt_entry_id`              INT(11)      NOT NULL COMMENT 'original tt_entries.id',
  `date`                     DATE         NOT NULL COMMENT 'specific date of absence',
  `day`                      VARCHAR(20)  NOT NULL,
  `period_id`                INT(11)      NOT NULL,
  `class_id`                 INT(11)      NOT NULL,
  `section_id`               INT(11)      NOT NULL,
  `subject_group_subject_id` INT(11)               DEFAULT NULL,
  `room_id`                  INT(11)               DEFAULT NULL,
  `substitution_type`        ENUM('manual','auto_suggested') NOT NULL DEFAULT 'manual',
  `status`                   ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `note`                     VARCHAR(300)          DEFAULT NULL,
  `created_by`               INT(11)      NOT NULL,
  `created_at`               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tt_sub_absent`  (`absent_staff_id`,`date`),
  KEY `idx_tt_sub_entry`   (`tt_entry_id`),
  KEY `idx_tt_sub_date`    (`date`,`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 2: RBAC PERMISSION GROUP & CATEGORIES
-- =============================================================================

INSERT INTO `permission_group` (`id`, `name`, `short_code`, `is_active`, `system`)
VALUES (3000, 'Auto Timetable', 'auto_timetable', 1, 0)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `is_active` = 1;

INSERT INTO `permission_category` (`id`, `perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`) VALUES
(3001, 3000, 'TT Period Setup',         'tt_periods',       1, 1, 1, 1),
(3002, 3000, 'TT Room Setup',           'tt_rooms',         1, 1, 1, 1),
(3003, 3000, 'TT Batch Setup',          'tt_batches',       1, 1, 1, 1),
(3004, 3000, 'TT Subject Load',         'tt_subject_load',  1, 1, 1, 1),
(3005, 3000, 'TT Teacher Constraints',  'tt_teacher_constr',1, 1, 1, 1),
(3006, 3000, 'TT Teacher Availability', 'tt_teacher_avail', 1, 1, 1, 1),
(3007, 3000, 'TT Auto Generate',        'tt_generate',      1, 1, 0, 0),
(3008, 3000, 'TT Class Timetable',      'tt_class_grid',    1, 1, 1, 1),
(3009, 3000, 'TT Teacher Timetable',    'tt_teacher_view',  1, 0, 0, 0),
(3010, 3000, 'TT Substitution',         'tt_substitution',  1, 1, 1, 1),
(3011, 3000, 'TT Reports',              'tt_reports',       1, 0, 0, 0)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `perm_group_id` = VALUES(`perm_group_id`);

-- =============================================================================
-- SECTION 3: SIDEBAR MENU
-- =============================================================================

INSERT INTO `sidebar_menus` (`product_name`, `permission_group_id`, `icon`, `menu`, `activate_menu`, `lang_key`, `system_level`, `level`, `sidebar_display`, `access_permissions`, `is_active`)
VALUES ('minerva', 3000, 'fa fa-calendar-check-o', 'Auto Timetable', 'tt', 'auto_timetable', 0, 1, 1,
  "('tt_periods','can_view')||('tt_rooms','can_view')||('tt_subject_load','can_view')||('tt_generate','can_view')||('tt_class_grid','can_view')||('tt_substitution','can_view')||('tt_reports','can_view')",
  1)
ON DUPLICATE KEY UPDATE `is_active` = 1, `id` = LAST_INSERT_ID(`id`);

SET @tt_menu_id = LAST_INSERT_ID();

INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `lang_key`, `url`, `level`, `access_permissions`, `permission_group_id`, `activate_controller`, `is_active`) VALUES
(@tt_menu_id, 'Period Setup',       'tt_periods',        'tt_period_setup',       'admin/tt/periods',              1, "('tt_periods','can_view')",       3000, 'tt', 1),
(@tt_menu_id, 'Rooms',              'tt_rooms',          'tt_rooms',              'admin/tt/rooms',                1, "('tt_rooms','can_view')",         3000, 'tt', 1),
(@tt_menu_id, 'Batches',            'tt_batches',        'tt_batches',            'admin/tt/batches',              1, "('tt_batches','can_view')",       3000, 'tt', 1),
(@tt_menu_id, 'Subject Load',       'tt_subject_load',   'tt_subject_load',       'admin/tt/subject_load',         1, "('tt_subject_load','can_view')",  3000, 'tt', 1),
(@tt_menu_id, 'Teacher Constraints','tt_teacher_constr', 'tt_teacher_constraints','admin/tt/teacher_constraints',  1, "('tt_teacher_constr','can_view')",3000, 'tt', 1),
(@tt_menu_id, 'Teacher Availability','tt_teacher_avail', 'tt_teacher_availability','admin/tt/teacher_unavail',     1, "('tt_teacher_avail','can_view')", 3000, 'tt', 1),
(@tt_menu_id, 'Auto Generate',      'tt_generate',       'tt_auto_generate',      'admin/tt/generate',             1, "('tt_generate','can_view')",      3000, 'tt', 1),
(@tt_menu_id, 'Class Timetable',    'tt_class_grid',     'tt_class_timetable',    'admin/tt/class_grid',           1, "('tt_class_grid','can_view')",    3000, 'tt', 1),
(@tt_menu_id, 'Teacher Timetable',  'tt_teacher_view',   'tt_teacher_timetable',  'admin/tt/teacher_view',         1, "('tt_teacher_view','can_view')",  3000, 'tt', 1),
(@tt_menu_id, 'Substitution',       'tt_substitution',   'tt_substitution',       'admin/tt/substitution',         1, "('tt_substitution','can_view')",  3000, 'tt', 1),
(@tt_menu_id, 'Reports',            'tt_reports',        'tt_reports',            'admin/tt/reports',              1, "('tt_reports','can_view')",       3000, 'tt', 1);
