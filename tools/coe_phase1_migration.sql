-- =============================================================================
-- CoE (Controller of Examinations) Module - Phase 1 Migration
-- Run this ONLY on local/dev first. Do NOT run on production until module is complete.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- SECTION 1: ALTER EXISTING TABLES
-- =============================================================================

-- Mark an exam_group as an end-semester (CoE) exam
-- exam_category: main=regular end-sem, arrear=repeat attempt, supplementary=mercy/grace
ALTER TABLE `exam_groups`
  ADD COLUMN IF NOT EXISTS `is_end_semester` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'CoE: 1 = end-semester exam managed by CoE module' AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `exam_category` ENUM('main','arrear','supplementary') NOT NULL DEFAULT 'main'
    COMMENT 'CoE: type of end-semester exam (main/arrear/supplementary)' AFTER `is_end_semester`;

-- Mark a class-batch exam as CoE-locked (no further edits once locked)
ALTER TABLE `exam_group_class_batch_exams`
  ADD COLUMN IF NOT EXISTS `is_end_semester` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'CoE: mirrors parent exam_group.is_end_semester' AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `coe_locked` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'CoE: 1 = exam locked, no edits allowed' AFTER `is_end_semester`;

-- =============================================================================
-- SECTION 2: NEW COE TABLES
-- =============================================================================

-- Exam regulations per programme (class) per academic year (session)
CREATE TABLE IF NOT EXISTS `coe_exam_regulations` (
  `id`                      INT(11)        NOT NULL AUTO_INCREMENT,
  `session_id`              INT(11)        NOT NULL COMMENT 'FK: sessions.id',
  `class_id`                INT(11)        NOT NULL COMMENT 'FK: classes.id (programme/year)',
  `department_id`           INT(11)        DEFAULT NULL COMMENT 'FK: department.id',
  `regulation_type`         ENUM('affiliated','autonomous') NOT NULL DEFAULT 'affiliated',
  `affiliated_university`   VARCHAR(255)   NOT NULL DEFAULT 'Anna University',
  `min_attendance_pct`      DECIMAL(5,2)   NOT NULL DEFAULT 75.00 COMMENT 'Minimum attendance % for hall ticket eligibility',
  `internal_marks_pct`      DECIMAL(5,2)   NOT NULL DEFAULT 25.00 COMMENT 'Internal (CIA) marks weightage %',
  `external_marks_pct`      DECIMAL(5,2)   NOT NULL DEFAULT 75.00 COMMENT 'External (end-sem) marks weightage %',
  `pass_marks_pct`          DECIMAL(5,2)   NOT NULL DEFAULT 50.00 COMMENT 'Minimum marks % to pass',
  `has_credit_system`       TINYINT(1)     NOT NULL DEFAULT 1 COMMENT 'CBCS: 1 = credits active',
  `grading_scheme`          ENUM('ten_point','seven_point','percentage') NOT NULL DEFAULT 'ten_point',
  `arrear_allowed`          TINYINT(1)     NOT NULL DEFAULT 1,
  `supplementary_allowed`   TINYINT(1)     NOT NULL DEFAULT 0,
  `check_fee_dues`          TINYINT(1)     NOT NULL DEFAULT 0 COMMENT 'Check fee dues for hall ticket eligibility (enable when fee data is reliable)',
  `is_active`               TINYINT(1)     NOT NULL DEFAULT 1,
  `created_by`              INT(11)        DEFAULT NULL COMMENT 'FK: staff.id',
  `created_at`              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_reg_session_class` (`session_id`, `class_id`),
  KEY `idx_coe_reg_session` (`session_id`),
  KEY `idx_coe_reg_class` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: exam regulations config per programme per session';

-- Student exam applications (per subject, per exam event)
CREATE TABLE IF NOT EXISTS `coe_exam_applications` (
  `id`                              INT(11)        NOT NULL AUTO_INCREMENT,
  `exam_group_id`                   INT(11)        NOT NULL COMMENT 'FK: exam_groups.id (must have is_end_semester=1)',
  `exam_group_class_batch_exam_id`  INT(11)        NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `student_id`                      INT(11)        NOT NULL COMMENT 'FK: students.id',
  `student_session_id`              INT(11)        NOT NULL COMMENT 'FK: student_session.id',
  `subject_id`                      INT(11)        NOT NULL COMMENT 'FK: subjects.id',
  `is_arrear`                       TINYINT(1)     NOT NULL DEFAULT 0 COMMENT '1 = arrear/repeat attempt for this subject',
  `cbcs_category`                   ENUM('core','elective','open_elective','audit') NOT NULL DEFAULT 'core'
    COMMENT 'CBCS/NEP2020: subject category (core/elective/open_elective/audit)',
  `application_status`              ENUM('pending','eligible','ineligible','override_eligible') NOT NULL DEFAULT 'pending',
  `ineligible_reason`               ENUM('attendance','fee_dues','both') DEFAULT NULL,
  `attendance_pct`                  DECIMAL(5,2)   DEFAULT NULL COMMENT 'Calculated attendance % at time of eligibility run',
  `applied_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at`                    TIMESTAMP      NULL DEFAULT NULL,
  `processed_by`                    INT(11)        DEFAULT NULL COMMENT 'FK: staff.id',
  `created_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_app_student_subject` (`exam_group_class_batch_exam_id`, `student_id`, `subject_id`),
  KEY `idx_coe_app_exam_group` (`exam_group_id`),
  KEY `idx_coe_app_student` (`student_id`),
  KEY `idx_coe_app_status` (`application_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: per-subject exam applications per student';

-- Manual eligibility overrides by CoE staff
CREATE TABLE IF NOT EXISTS `coe_eligibility_overrides` (
  `id`              INT(11)     NOT NULL AUTO_INCREMENT,
  `application_id`  INT(11)     NOT NULL COMMENT 'FK: coe_exam_applications.id',
  `override_reason` TEXT        NOT NULL,
  `override_by`     INT(11)     NOT NULL COMMENT 'FK: staff.id',
  `override_at`     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_override_app` (`application_id`),
  KEY `idx_coe_override_staff` (`override_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: manual eligibility override log';

-- Hall tickets (one per student per exam event)
CREATE TABLE IF NOT EXISTS `coe_hall_tickets` (
  `id`                              INT(11)        NOT NULL AUTO_INCREMENT,
  `exam_group_id`                   INT(11)        NOT NULL COMMENT 'FK: exam_groups.id',
  `exam_group_class_batch_exam_id`  INT(11)        NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `student_id`                      INT(11)        NOT NULL COMMENT 'FK: students.id',
  `student_session_id`              INT(11)        NOT NULL COMMENT 'FK: student_session.id',
  `hall_ticket_no`                  VARCHAR(30)    NOT NULL COMMENT 'Formatted hall ticket number (e.g. HT2025001)',
  `qr_hash`                         VARCHAR(64)    NOT NULL COMMENT 'AES-256-CBC encrypted hash for QR code verification',
  `is_valid`                        TINYINT(1)     NOT NULL DEFAULT 1 COMMENT '0 = invalidated/cancelled',
  `generated_by`                    INT(11)        DEFAULT NULL COMMENT 'FK: staff.id',
  `generated_at`                    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `printed_at`                      TIMESTAMP      NULL DEFAULT NULL,
  `downloaded_count`                INT(11)        NOT NULL DEFAULT 0,
  `created_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_ht_student_exam` (`exam_group_class_batch_exam_id`, `student_id`),
  UNIQUE KEY `uq_coe_ht_no` (`hall_ticket_no`),
  UNIQUE KEY `uq_coe_ht_qr` (`qr_hash`),
  KEY `idx_coe_ht_exam_group` (`exam_group_id`),
  KEY `idx_coe_ht_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: generated hall tickets with QR hashes';

-- Nominal rolls (snapshot at generation time)
CREATE TABLE IF NOT EXISTS `coe_nominal_rolls` (
  `id`                              INT(11)        NOT NULL AUTO_INCREMENT,
  `exam_group_id`                   INT(11)        NOT NULL COMMENT 'FK: exam_groups.id',
  `exam_group_class_batch_exam_id`  INT(11)        NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `subject_id`                      INT(11)        NOT NULL COMMENT 'FK: subjects.id',
  `exam_date`                       DATE           NOT NULL,
  `total_students`                  INT(11)        NOT NULL DEFAULT 0,
  `generated_by`                    INT(11)        NOT NULL COMMENT 'FK: staff.id',
  `generated_at`                    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_final`                        TINYINT(1)     NOT NULL DEFAULT 0 COMMENT '1 = finalised, no more edits',
  `roll_snapshot`                   LONGTEXT       DEFAULT NULL COMMENT 'JSON snapshot of nominal roll at generation time',
  `created_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coe_nr_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_coe_nr_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: nominal roll snapshots per subject per exam';

-- Exam seating rooms (links halls to a specific exam date/session)
CREATE TABLE IF NOT EXISTS `coe_seating_rooms` (
  `id`                              INT(11)        NOT NULL AUTO_INCREMENT,
  `exam_group_id`                   INT(11)        NOT NULL COMMENT 'FK: exam_groups.id',
  `exam_group_class_batch_exam_id`  INT(11)        NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `hall_id`                         INT(11)        NOT NULL COMMENT 'FK: halls.id',
  `subject_id`                      INT(11)        DEFAULT NULL COMMENT 'FK: subjects.id — NULL = all subjects for this date',
  `exam_date`                       DATE           NOT NULL,
  `session_slot`                    ENUM('FN','AN') NOT NULL DEFAULT 'FN' COMMENT 'FN=Forenoon, AN=Afternoon',
  `capacity_override`               INT(11)        DEFAULT NULL COMMENT 'Override hall capacity for this exam',
  `chief_superintendent_id`         INT(11)        DEFAULT NULL COMMENT 'FK: staff.id',
  `is_active`                       TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coe_sr_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_coe_sr_hall` (`hall_id`),
  KEY `idx_coe_sr_date` (`exam_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: seating rooms — halls assigned to exam dates';

-- Student seat assignments
CREATE TABLE IF NOT EXISTS `coe_seating_assignments` (
  `id`                  INT(11)        NOT NULL AUTO_INCREMENT,
  `seating_room_id`     INT(11)        NOT NULL COMMENT 'FK: coe_seating_rooms.id',
  `student_id`          INT(11)        NOT NULL COMMENT 'FK: students.id',
  `student_session_id`  INT(11)        NOT NULL COMMENT 'FK: student_session.id',
  `hall_ticket_id`      INT(11)        DEFAULT NULL COMMENT 'FK: coe_hall_tickets.id',
  `seat_number`         VARCHAR(20)    NOT NULL,
  `is_present`          TINYINT(1)     NOT NULL DEFAULT 0 COMMENT '0=not yet, 1=present on exam day',
  `created_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_seat` (`seating_room_id`, `seat_number`),
  UNIQUE KEY `uq_coe_seat_student` (`seating_room_id`, `student_id`),
  KEY `idx_coe_sa_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: student seat assignments per room per exam';

-- Invigilation duty assignments
CREATE TABLE IF NOT EXISTS `coe_invigilation_duties` (
  `id`                INT(11)     NOT NULL AUTO_INCREMENT,
  `seating_room_id`   INT(11)     NOT NULL COMMENT 'FK: coe_seating_rooms.id',
  `staff_id`          INT(11)     NOT NULL COMMENT 'FK: staff.id',
  `duty_type`         ENUM('chief_superintendent','invigilator','deputy','flying_squad') NOT NULL DEFAULT 'invigilator',
  `remarks`           TEXT        DEFAULT NULL,
  `created_at`        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_inv_room_staff` (`seating_room_id`, `staff_id`),
  KEY `idx_coe_inv_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: invigilation duty assignments per room';

-- CoE audit log (tamper-proof action log)
CREATE TABLE IF NOT EXISTS `coe_audit_log` (
  `id`          INT(11)     NOT NULL AUTO_INCREMENT,
  `action`      VARCHAR(100) NOT NULL COMMENT 'e.g. hall_ticket_generated, eligibility_override, application_generated',
  `entity`      VARCHAR(50)  NOT NULL COMMENT 'e.g. coe_hall_tickets, coe_exam_applications',
  `entity_id`   INT(11)      DEFAULT NULL,
  `old_value`   TEXT         DEFAULT NULL COMMENT 'JSON of before state',
  `new_value`   TEXT         DEFAULT NULL COMMENT 'JSON of after state',
  `performed_by` INT(11)     NOT NULL COMMENT 'FK: staff.id',
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `performed_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coe_audit_entity` (`entity`, `entity_id`),
  KEY `idx_coe_audit_staff` (`performed_by`),
  KEY `idx_coe_audit_date` (`performed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='CoE: tamper-proof audit log';

-- =============================================================================
-- SECTION 3: RBAC - Permission Group & Categories
-- =============================================================================

-- CoE permission group (id=2000 — well above existing max of 1500)
INSERT INTO `permission_group` (`id`, `name`, `short_code`, `is_active`, `system`)
VALUES (2000, 'CoE (Examinations)', 'coe', 1, 0)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `is_active` = 1;

-- CoE permission categories
INSERT INTO `permission_category` (`id`, `perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`) VALUES
(2001, 2000, 'CoE Exam Regulations',     'coe_setup',         1, 1, 1, 1),
(2002, 2000, 'CoE Exam Applications',    'coe_application',   1, 1, 1, 0),
(2003, 2000, 'CoE Eligibility',          'coe_eligibility',   1, 1, 0, 0),
(2004, 2000, 'CoE Eligibility Override', 'coe_override',      1, 1, 0, 0),
(2005, 2000, 'CoE Hall Tickets',         'coe_hallticket',    1, 1, 0, 0),
(2006, 2000, 'CoE Nominal Roll',         'coe_nominalroll',   1, 1, 0, 0),
(2007, 2000, 'CoE Seating Arrangement',  'coe_seating',       1, 1, 1, 1),
(2008, 2000, 'CoE Invigilation Duties',  'coe_invigilation',  1, 1, 1, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `perm_group_id` = VALUES(`perm_group_id`);

-- =============================================================================
-- SECTION 4: SIDEBAR MENU
-- =============================================================================

-- CoE parent sidebar menu
INSERT INTO `sidebar_menus` (`product_name`, `permission_group_id`, `icon`, `menu`, `activate_menu`, `lang_key`, `system_level`, `level`, `sidebar_display`, `access_permissions`, `is_active`)
VALUES ('minerva', 2000, 'fa fa-university', 'CoE Examinations', 'coe', 'coe_examinations', 0, 1, 1, "('coe_setup', 'can_view')||('coe_application', 'can_view')||('coe_eligibility', 'can_view')||('coe_hallticket', 'can_view')||('coe_nominalroll', 'can_view')||('coe_seating', 'can_view')||('coe_invigilation', 'can_view')", 1);

-- Capture the inserted sidebar_menus ID
SET @coe_menu_id = LAST_INSERT_ID();

-- CoE submenus
INSERT INTO `sidebar_sub_menus` (`sidebar_menu_id`, `menu`, `key`, `lang_key`, `url`, `level`, `access_permissions`, `permission_group_id`, `activate_controller`, `is_active`) VALUES
(@coe_menu_id, 'Exam Regulations',     'coe_setup',        'coe_exam_regulations',  'coe/coe_setup',        1, "('coe_setup', 'can_view')",        1000, 'coe_setup',       1),
(@coe_menu_id, 'Exam Events',          'coe_events',       'coe_exam_events',       'coe/coe_application',  1, "('coe_application', 'can_view')",  1000, 'coe_application', 1),
(@coe_menu_id, 'Eligibility Check',    'coe_eligibility',  'coe_eligibility',       'coe/coe_eligibility',  1, "('coe_eligibility', 'can_view')",  1000, 'coe_eligibility', 1),
(@coe_menu_id, 'Hall Tickets',         'coe_hallticket',   'coe_hallticket',        'coe/coe_hallticket',   1, "('coe_hallticket', 'can_view')",   1000, 'coe_hallticket',  1),
(@coe_menu_id, 'Nominal Roll',         'coe_nominalroll',  'coe_nominal_roll',      'coe/coe_nominalroll',  1, "('coe_nominalroll', 'can_view')",  1000, 'coe_nominalroll', 1),
(@coe_menu_id, 'Seating Arrangement',  'coe_seating',      'coe_seating',           'coe/coe_seating',      1, "('coe_seating', 'can_view')",      1000, 'coe_seating',     1),
(@coe_menu_id, 'Invigilation Duty',    'coe_invigilation', 'coe_invigilation',      'coe/coe_invigilation', 1, "('coe_invigilation', 'can_view')", 1000, 'coe_invigilation',1);

-- =============================================================================
-- SECTION 5: LANGUAGE KEYS
-- Language strings are file-based in this project.
-- Add to: application/language/English/app_files/system_lang.php
-- (done separately — see coe_phase1_lang.php snippet)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- VERIFY (run manually to confirm)
-- =============================================================================
-- SELECT * FROM permission_group WHERE id = 1000;
-- SELECT * FROM permission_category WHERE perm_group_id = 1000;
-- SELECT sm.id, sm.menu, ssm.menu as submenu, ssm.url FROM sidebar_menus sm JOIN sidebar_sub_menus ssm ON ssm.sidebar_menu_id = sm.id WHERE sm.permission_group_id = 1000;
-- SHOW TABLES LIKE 'coe_%';
