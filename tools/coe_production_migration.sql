-- =============================================================================
-- CoE Production Migration — idempotent, safe for all 6 DB instances
-- Run on: mcekknagar, amace, amacedu, maasc, maptc, minervademo
-- Date: 12 May 2026
-- Notes:
--   - All CREATE TABLE uses IF NOT EXISTS
--   - All ALTER TABLE uses IF NOT EXISTS (MySQL 8.0.14+ / MariaDB 10.3.2+)
--   - All INSERTs use INSERT IGNORE (keyed on explicit IDs)
--   - Sidebar uses WHERE NOT EXISTS to avoid duplicate parent menu
--   - sidebar_sub_menus uses INSERT IGNORE on id — safe because IDs 286-303,306,309,310
--     are not used by any other module on any instance
--   - Production mcekknagar has hostel_fee_override at id=15016; CoE uses 15017+
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- SECTION 1: ALTER EXISTING TABLES
-- Add CoE-specific columns to core exam tables
-- Uses stored procedures to guard against "column already exists" (MySQL 8.0
-- does not support ADD COLUMN IF NOT EXISTS — that is MariaDB-only syntax)
-- =============================================================================

DROP PROCEDURE IF EXISTS coe_add_column_if_missing;
DELIMITER //
CREATE PROCEDURE coe_add_column_if_missing(
  IN tbl VARCHAR(64), IN col VARCHAR(64), IN col_def TEXT)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', col_def);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END//
DELIMITER ;

CALL coe_add_column_if_missing('exam_groups', 'is_end_semester', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`');
CALL coe_add_column_if_missing('exam_groups', 'exam_category', "ENUM('main','arrear','supplementary') NOT NULL DEFAULT 'main' AFTER `is_end_semester`");
CALL coe_add_column_if_missing('exam_group_class_batch_exams', 'is_end_semester', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`');
CALL coe_add_column_if_missing('exam_group_class_batch_exams', 'coe_locked', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_end_semester`');
CALL coe_add_column_if_missing('exam_group_class_batch_exams', 'class_id', 'INT(11) DEFAULT NULL AFTER `coe_locked`');

DROP PROCEDURE IF EXISTS coe_add_column_if_missing;

-- =============================================================================
-- SECTION 2: CREATE CoE TABLES (21 tables, all IF NOT EXISTS)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `coe_answer_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `coe_hall_ticket_id` int(11) NOT NULL,
  `seating_room_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `session_slot` enum('FN','AN') DEFAULT 'FN',
  `barcode_token` varchar(64) DEFAULT NULL,
  `scanned_filename` varchar(255) DEFAULT NULL,
  `scan_status` enum('pending','scanned','uploaded') NOT NULL DEFAULT 'pending',
  `page_count` smallint(6) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode_token` (`barcode_token`),
  KEY `idx_batch_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_hall_ticket` (`coe_hall_ticket_id`),
  KEY `idx_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL COMMENT 'e.g. hall_ticket_generated, eligibility_override, application_generated',
  `entity` varchar(50) NOT NULL COMMENT 'e.g. coe_hall_tickets, coe_exam_applications',
  `entity_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL COMMENT 'JSON of before state',
  `new_value` text DEFAULT NULL COMMENT 'JSON of after state',
  `performed_by` int(11) NOT NULL COMMENT 'FK: staff.id',
  `ip_address` varchar(45) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coe_audit_entity` (`entity`,`entity_id`),
  KEY `idx_coe_audit_staff` (`performed_by`),
  KEY `idx_coe_audit_date` (`performed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: tamper-proof audit log';

CREATE TABLE IF NOT EXISTS `coe_eligibility_overrides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL COMMENT 'FK: coe_exam_applications.id',
  `override_reason` text NOT NULL,
  `override_by` int(11) NOT NULL COMMENT 'FK: staff.id',
  `override_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_override_app` (`application_id`),
  KEY `idx_coe_override_staff` (`override_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: manual eligibility override log';

CREATE TABLE IF NOT EXISTS `coe_exam_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_id` int(11) NOT NULL COMMENT 'FK: exam_groups.id (must have is_end_semester=1)',
  `exam_group_class_batch_exam_id` int(11) NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `student_id` int(11) NOT NULL COMMENT 'FK: students.id',
  `student_session_id` int(11) NOT NULL COMMENT 'FK: student_session.id',
  `subject_id` int(11) NOT NULL COMMENT 'FK: subjects.id',
  `is_arrear` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = arrear/repeat attempt for this subject',
  `cbcs_category` enum('core','elective','open_elective','audit') NOT NULL DEFAULT 'core' COMMENT 'CBCS/NEP2020: subject category',
  `application_status` enum('pending','eligible','ineligible','override_eligible') NOT NULL DEFAULT 'pending',
  `ineligible_reason` enum('attendance','fee_dues','both') DEFAULT NULL,
  `attendance_pct` decimal(5,2) DEFAULT NULL COMMENT 'Calculated attendance % at time of eligibility run',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL COMMENT 'FK: staff.id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_app_student_subject` (`exam_group_class_batch_exam_id`,`student_id`,`subject_id`),
  KEY `idx_coe_app_exam_group` (`exam_group_id`),
  KEY `idx_coe_app_student` (`student_id`),
  KEY `idx_coe_app_status` (`application_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: per-subject exam applications per student';

CREATE TABLE IF NOT EXISTS `coe_exam_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coe_hall_ticket_id` int(11) NOT NULL,
  `seating_room_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `session_slot` enum('FN','AN') NOT NULL DEFAULT 'FN',
  `is_present` tinyint(1) DEFAULT 0,
  `marked_by` int(11) DEFAULT NULL,
  `marked_at` datetime DEFAULT NULL,
  `qr_scanned` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance` (`coe_hall_ticket_id`,`exam_date`,`session_slot`),
  KEY `idx_att_room` (`seating_room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_exam_regulations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL COMMENT 'FK: sessions.id',
  `class_id` int(11) NOT NULL COMMENT 'FK: classes.id (programme/year)',
  `department_id` int(11) DEFAULT NULL COMMENT 'FK: department.id',
  `regulation_type` enum('affiliated','autonomous') NOT NULL DEFAULT 'affiliated',
  `affiliated_university` varchar(255) NOT NULL DEFAULT 'Anna University',
  `min_attendance_pct` decimal(5,2) NOT NULL DEFAULT 75.00 COMMENT 'Minimum attendance % for hall ticket eligibility',
  `internal_marks_pct` decimal(5,2) NOT NULL DEFAULT 25.00 COMMENT 'Internal (CIA) marks weightage %',
  `external_marks_pct` decimal(5,2) NOT NULL DEFAULT 75.00 COMMENT 'External (end-sem) marks weightage %',
  `pass_marks_pct` decimal(5,2) NOT NULL DEFAULT 50.00 COMMENT 'Minimum marks % to pass',
  `has_credit_system` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'CBCS: 1 = credits active',
  `grading_scheme` enum('ten_point','seven_point','percentage') NOT NULL DEFAULT 'ten_point',
  `arrear_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `supplementary_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `check_fee_dues` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Check fee dues for hall ticket eligibility (enable when fee data is reliable)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL COMMENT 'FK: staff.id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_reg_session_class` (`session_id`,`class_id`),
  KEY `idx_coe_reg_session` (`session_id`),
  KEY `idx_coe_reg_class` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: exam regulations config per programme per session';

CREATE TABLE IF NOT EXISTS `coe_hall_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_id` int(11) NOT NULL COMMENT 'FK: exam_groups.id',
  `exam_group_class_batch_exam_id` int(11) NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `student_id` int(11) NOT NULL COMMENT 'FK: students.id',
  `student_session_id` int(11) NOT NULL COMMENT 'FK: student_session.id',
  `hall_ticket_no` varchar(30) NOT NULL COMMENT 'Formatted hall ticket number (e.g. HT2025001)',
  `qr_hash` varchar(64) NOT NULL COMMENT 'AES-256-CBC encrypted hash for QR code verification',
  `is_valid` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = invalidated/cancelled',
  `generated_by` int(11) DEFAULT NULL COMMENT 'FK: staff.id',
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `printed_at` timestamp NULL DEFAULT NULL,
  `downloaded_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_ht_student_exam` (`exam_group_class_batch_exam_id`,`student_id`),
  UNIQUE KEY `uq_coe_ht_no` (`hall_ticket_no`),
  UNIQUE KEY `uq_coe_ht_qr` (`qr_hash`),
  KEY `idx_coe_ht_exam_group` (`exam_group_id`),
  KEY `idx_coe_ht_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: generated hall tickets with QR hashes';

CREATE TABLE IF NOT EXISTS `coe_invigilation_duties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seating_room_id` int(11) NOT NULL COMMENT 'FK: coe_seating_rooms.id',
  `staff_id` int(11) NOT NULL COMMENT 'FK: staff.id',
  `duty_type` enum('chief_superintendent','invigilator','deputy','flying_squad') NOT NULL DEFAULT 'invigilator',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_inv_room_staff` (`seating_room_id`,`staff_id`),
  KEY `idx_coe_inv_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: invigilation duty assignments per room';

CREATE TABLE IF NOT EXISTS `coe_moderation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `rule_type` enum('grace','moderation','normalisation','scaling') NOT NULL,
  `value_type` enum('flat','percentage') NOT NULL DEFAULT 'flat',
  `value` decimal(6,2) NOT NULL DEFAULT 0.00,
  `max_cap` decimal(5,2) DEFAULT NULL,
  `applies_to` enum('external','internal','total') NOT NULL DEFAULT 'external',
  `is_applied` tinyint(1) NOT NULL DEFAULT 0,
  `applied_by` int(11) DEFAULT NULL,
  `applied_at` datetime DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_batch_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_nominal_rolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_id` int(11) NOT NULL COMMENT 'FK: exam_groups.id',
  `exam_group_class_batch_exam_id` int(11) NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `subject_id` int(11) NOT NULL COMMENT 'FK: subjects.id',
  `exam_date` date NOT NULL,
  `total_students` int(11) NOT NULL DEFAULT 0,
  `generated_by` int(11) NOT NULL COMMENT 'FK: staff.id',
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_final` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = finalised, no more edits',
  `roll_snapshot` longtext DEFAULT NULL COMMENT 'JSON snapshot of nominal roll at generation time',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coe_nr_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_coe_nr_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: nominal roll snapshots per subject per exam';

CREATE TABLE IF NOT EXISTS `coe_osm_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osm_script_id` int(11) NOT NULL,
  `question_no` tinyint(3) unsigned NOT NULL,
  `sub_question` varchar(5) DEFAULT NULL,
  `max_marks` decimal(5,2) NOT NULL DEFAULT 0.00,
  `marks_awarded` decimal(5,2) NOT NULL DEFAULT 0.00,
  `awarded_by` int(11) DEFAULT NULL,
  `awarded_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_osm_question` (`osm_script_id`,`question_no`,`sub_question`),
  KEY `idx_osm` (`osm_script_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_osm_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `answer_script_id` int(11) NOT NULL,
  `assigned_evaluator` int(11) DEFAULT NULL,
  `stage` tinyint(1) NOT NULL DEFAULT 1,
  `total_marks` decimal(6,2) DEFAULT NULL,
  `status` enum('pending','assigned','marking','done','locked') NOT NULL DEFAULT 'pending',
  `assigned_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `locked_by` int(11) DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_script` (`answer_script_id`),
  KEY `idx_evaluator` (`assigned_evaluator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_qpd_papers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `original_filename` varchar(500) NOT NULL,
  `stored_filename` varchar(500) NOT NULL,
  `encryption_key_iv` varchar(64) NOT NULL COMMENT 'hex-encoded IV for AES-256-CBC',
  `unlock_at` datetime NOT NULL,
  `download_count` int(11) DEFAULT 0,
  `is_distributed` tinyint(1) DEFAULT 0,
  `distributed_at` datetime DEFAULT NULL,
  `distributed_by` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_qpd_batch` (`exam_group_class_batch_exam_id`),
  KEY `idx_qpd_subject` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_revaluation_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `revaluation_request_id` int(11) NOT NULL,
  `assigned_evaluator` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `original_marks` decimal(5,2) DEFAULT NULL,
  `revised_marks` decimal(5,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('assigned','completed','returned') NOT NULL DEFAULT 'assigned',
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_request` (`revaluation_request_id`),
  KEY `idx_evaluator` (`assigned_evaluator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_revaluation_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `original_marks` decimal(5,2) DEFAULT NULL,
  `request_date` date NOT NULL,
  `payment_status` enum('pending','paid','waived') NOT NULL DEFAULT 'pending',
  `payment_ref` varchar(100) DEFAULT NULL,
  `payment_amount` decimal(8,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `stage` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','assigned','completed','rejected') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_batch_exam` (`exam_group_class_batch_exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_seating_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seating_room_id` int(11) NOT NULL COMMENT 'FK: coe_seating_rooms.id',
  `student_id` int(11) NOT NULL COMMENT 'FK: students.id',
  `student_session_id` int(11) NOT NULL COMMENT 'FK: student_session.id',
  `hall_ticket_id` int(11) DEFAULT NULL COMMENT 'FK: coe_hall_tickets.id',
  `seat_number` varchar(20) NOT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=not yet, 1=present on exam day',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coe_seat` (`seating_room_id`,`seat_number`),
  UNIQUE KEY `uq_coe_seat_student` (`seating_room_id`,`student_id`),
  KEY `idx_coe_sa_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: student seat assignments per room per exam';

CREATE TABLE IF NOT EXISTS `coe_seating_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_id` int(11) NOT NULL COMMENT 'FK: exam_groups.id',
  `exam_group_class_batch_exam_id` int(11) NOT NULL COMMENT 'FK: exam_group_class_batch_exams.id',
  `hall_id` int(11) NOT NULL COMMENT 'FK: halls.id',
  `subject_id` int(11) DEFAULT NULL COMMENT 'FK: subjects.id — NULL = all subjects for this date',
  `exam_date` date NOT NULL,
  `session_slot` enum('FN','AN') NOT NULL DEFAULT 'FN' COMMENT 'FN=Forenoon, AN=Afternoon',
  `capacity_override` int(11) DEFAULT NULL COMMENT 'Override hall capacity for this exam',
  `chief_superintendent_id` int(11) DEFAULT NULL COMMENT 'FK: staff.id',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_coe_sr_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_coe_sr_hall` (`hall_id`),
  KEY `idx_coe_sr_date` (`exam_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='CoE: seating rooms — halls assigned to exam dates';

CREATE TABLE IF NOT EXISTS `coe_sgpa_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `total_credits_earned` decimal(6,2) DEFAULT NULL,
  `total_credits_registered` decimal(6,2) DEFAULT NULL,
  `sgpa` decimal(5,2) DEFAULT NULL,
  `cgpa` decimal(5,2) DEFAULT NULL,
  `arrear_count` tinyint(3) unsigned DEFAULT 0,
  `result_status` enum('pass','fail','withheld') NOT NULL DEFAULT 'fail',
  `computed_at` datetime DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `published_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_exam` (`student_id`,`exam_group_class_batch_exam_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_batch_exam` (`exam_group_class_batch_exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_student_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `internal_marks` decimal(5,2) DEFAULT NULL,
  `external_marks` decimal(5,2) DEFAULT NULL,
  `total_marks` decimal(5,2) DEFAULT NULL,
  `max_internal` decimal(5,2) DEFAULT 30.00,
  `max_external` decimal(5,2) DEFAULT 70.00,
  `max_total` decimal(5,2) DEFAULT 100.00,
  `credits` tinyint(3) unsigned DEFAULT NULL,
  `grade` varchar(4) DEFAULT NULL,
  `grade_points` decimal(4,2) DEFAULT NULL,
  `result_status` enum('pass','fail','absent','withheld','detained') NOT NULL DEFAULT 'fail',
  `is_arrear` tinyint(1) NOT NULL DEFAULT 0,
  `moderation_applied` decimal(5,2) DEFAULT 0.00,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_subject` (`student_id`,`exam_group_class_batch_exam_id`,`subject_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_batch_exam` (`exam_group_class_batch_exam_id`),
  KEY `idx_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_subject_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `credits` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `max_internal` decimal(5,2) NOT NULL DEFAULT 30.00,
  `max_external` decimal(5,2) NOT NULL DEFAULT 70.00,
  `pass_internal` decimal(5,2) NOT NULL DEFAULT 12.00,
  `pass_external` decimal(5,2) NOT NULL DEFAULT 28.00,
  `pass_total` decimal(5,2) NOT NULL DEFAULT 50.00,
  `scheme` enum('CBCS','NEP2020') NOT NULL DEFAULT 'NEP2020',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exam_subject` (`exam_group_class_batch_exam_id`,`subject_id`),
  KEY `idx_batch_exam` (`exam_group_class_batch_exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_ufm_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coe_hall_ticket_id` int(11) NOT NULL,
  `seating_room_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `session_slot` enum('FN','AN') NOT NULL DEFAULT 'FN',
  `incident_type` enum('copying','mobile_phone','impersonation','unfair_material','communication','other') NOT NULL,
  `description` text DEFAULT NULL,
  `material_seized` text DEFAULT NULL,
  `reported_by` int(11) NOT NULL,
  `witness_staff_id` int(11) DEFAULT NULL,
  `status` enum('reported','under_review','penalised','dismissed') DEFAULT 'reported',
  `penalty` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ufm_ticket` (`coe_hall_ticket_id`),
  KEY `idx_ufm_room` (`seating_room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `coe_exam_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `session_slot` enum('FN','AN') NOT NULL DEFAULT 'FN',
  `hall_id` int(11) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sch_sub` (`exam_group_class_batch_exam_id`,`subject_id`),
  KEY `idx_bid` (`exam_group_class_batch_exam_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coe_flying_squad_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time DEFAULT NULL,
  `observer_staff_id` int(11) NOT NULL,
  `hall_id` int(11) DEFAULT NULL,
  `hall_name` varchar(200) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `irregularities_found` tinyint(1) NOT NULL DEFAULT 0,
  `irregularity_details` text DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `severity` enum('none','minor','major') NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_batch` (`exam_group_class_batch_exam_id`),
  KEY `idx_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 3: RBAC — permission_group and permission_category
-- =============================================================================

INSERT IGNORE INTO `permission_group` (`id`, `name`, `short_code`, `is_active`, `system`, `created_at`, `updated_at`)
VALUES (2000, 'CoE (Examinations)', 'coe', 1, 0, NOW(), NOW());

-- IDs 2001-2008 already exist on 5 production DBs; INSERT IGNORE skips them safely
-- minervademo has none — INSERT IGNORE will add all
INSERT IGNORE INTO `permission_category` (`id`, `perm_group_id`, `name`, `short_code`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`, `created_at`, `updated_at`) VALUES
(2001, 2000, 'CoE Exam Regulations',            'coe_setup',          1, 1, 1, 1, NOW(), NOW()),
(2002, 2000, 'CoE Exam Applications',           'coe_application',    1, 1, 1, 0, NOW(), NOW()),
(2003, 2000, 'CoE Eligibility',                 'coe_eligibility',    1, 1, 0, 0, NOW(), NOW()),
(2004, 2000, 'CoE Eligibility Override',        'coe_override',       1, 1, 0, 0, NOW(), NOW()),
(2005, 2000, 'CoE Hall Tickets',                'coe_hallticket',     1, 1, 0, 0, NOW(), NOW()),
(2006, 2000, 'CoE Nominal Roll',                'coe_nominalroll',    1, 1, 0, 0, NOW(), NOW()),
(2007, 2000, 'CoE Seating Arrangement',         'coe_seating',        1, 1, 1, 1, NOW(), NOW()),
(2008, 2000, 'CoE Invigilation Duties',         'coe_invigilation',   1, 1, 1, 1, NOW(), NOW()),
-- New categories for Phase 2+ features (safe for all 6 DBs — hostel_fee_override is 15016)
(15017, 2000, 'CoE Question Paper Distribution', 'coe_qpd',           1, 1, 1, 1, NOW(), NOW()),
(15018, 2000, 'CoE Exam Attendance',             'coe_attendance',    1, 1, 1, 0, NOW(), NOW()),
(15019, 2000, 'CoE UFM / Malpractice',           'coe_ufm',           1, 1, 1, 1, NOW(), NOW()),
(15020, 2000, 'CoE Answer Scripts',              'coe_answer_scripts',1, 1, 1, 1, NOW(), NOW()),
(15021, 2000, 'CoE OSM Marking',                 'coe_osm',           1, 1, 1, 0, NOW(), NOW()),
(15022, 2000, 'CoE Revaluation',                 'coe_revaluation',   1, 1, 1, 1, NOW(), NOW()),
(15023, 2000, 'CoE Moderation',                  'coe_moderation',    1, 1, 1, 1, NOW(), NOW()),
(15024, 2000, 'CoE Marks & Results',             'coe_marks',         1, 1, 1, 0, NOW(), NOW()),
(15025, 2000, 'CoE Result Publication',          'coe_results',       1, 1, 0, 0, NOW(), NOW()),
(15026, 2000, 'CoE Exam Events CRUD',            'coe_event',         1, 1, 1, 1, NOW(), NOW()),
(15027, 2000, 'CoE Dashboard',                   'coe_dashboard',     1, 0, 0, 0, NOW(), NOW()),
(15028, 2000, 'CoE Arrear Register',             'coe_arrear',        1, 0, 0, 0, NOW(), NOW()),
(15029, 2000, 'CoE Exam Schedule',               'coe_schedule',      1, 1, 1, 1, NOW(), NOW()),
(15030, 2000, 'CoE Flying Squad',                'coe_flyingsquad',   1, 1, 1, 1, NOW(), NOW());

-- =============================================================================
-- SECTION 4: SIDEBAR MENUS
-- Parent menu: uses WHERE NOT EXISTS to avoid duplicates on DBs that already
-- have the CoE parent (mcekknagar/amace/amacedu/maasc/maptc already have it
-- at id=41 or 42; minervademo does not have it at all)
-- =============================================================================

INSERT INTO `sidebar_menus`
  (`product_name`, `permission_group_id`, `icon`, `menu`, `activate_menu`, `lang_key`,
   `system_level`, `level`, `sidebar_display`, `access_permissions`, `is_active`, `created_at`, `updated_at`)
SELECT
  'minerva', 2000, 'fa fa-university', 'CoE Examinations', 'coe', 'coe_examinations',
  0, 1, 1,
  '(''coe_setup'', ''can_view'')||(''coe_application'', ''can_view'')||(''coe_eligibility'', ''can_view'')||(''coe_hallticket'', ''can_view'')||(''coe_nominalroll'', ''can_view'')||(''coe_seating'', ''can_view'')||(''coe_invigilation'', ''can_view'')',
  1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `sidebar_menus` WHERE `activate_menu` = 'coe');

-- Capture the CoE parent menu ID (works whether it was just inserted or pre-existed)
SET @coe_menu_id = (SELECT `id` FROM `sidebar_menus` WHERE `activate_menu` = 'coe' LIMIT 1);

-- Sub-menus: INSERT IGNORE on id is safe — IDs 286-303, 306, 309, 310 are not used by
-- any other module. The sidebar_menu_id is replaced with @coe_menu_id so it
-- correctly references the existing parent (id=41/42 on 5 prod DBs, or newly
-- inserted id on minervademo)
INSERT IGNORE INTO `sidebar_sub_menus`
  (`id`, `sidebar_menu_id`, `menu`, `key`, `lang_key`, `url`, `level`,
   `access_permissions`, `permission_group_id`, `activate_controller`,
   `activate_methods`, `addon_permission`, `is_active`, `created_at`, `updated_at`)
VALUES
  (302, @coe_menu_id, 'CoE Dashboard',        'coe_dashboard',      'coe_dashboard',         'coe/coe_dashboard',      1,    '(''coe_dashboard'', ''can_view'')',      NULL, 'coe_dashboard',      'index',                                                               NULL, 1, NOW(), NOW()),
  (286, @coe_menu_id, 'Exam Regulations',     'coe_setup',          'coe_exam_regulations',  'coe/coe_setup',          2,    '(''coe_setup'', ''can_view'')',          1000, 'coe_setup',          'index',                                                               NULL, 1, NOW(), NOW()),
  (287, @coe_menu_id, 'Exam Events',          'coe_event',          'coe_exam_events',       'coe/coe_event',          3,    '("coe_event", "can_view")',              1000, 'coe_event',          'index,add,save,edit,update,delete,manage,save_batch,update_batch,delete_batch', NULL, 1, NOW(), NOW()),
  (306, @coe_menu_id, 'Exam Applications',    'coe_application',    'coe_exam_applications', 'coe/coe_application',    4,    '("coe_application", "can_view")',        1000, 'coe_application',    'index,view,generate,mark_end_semester',                               NULL, 1, NOW(), NOW()),
  (288, @coe_menu_id, 'Eligibility Check',    'coe_eligibility',    'coe_eligibility',       'coe/coe_eligibility',    5,    '(''coe_eligibility'', ''can_view'')',    1000, 'coe_eligibility',    'index',                                                               NULL, 1, NOW(), NOW()),
  (289, @coe_menu_id, 'Hall Tickets',         'coe_hallticket',     'coe_hallticket',        'coe/coe_hallticket',     6,    '(''coe_hallticket'', ''can_view'')',     1000, 'coe_hallticket',     'index',                                                               NULL, 1, NOW(), NOW()),
  (290, @coe_menu_id, 'Nominal Roll',         'coe_nominalroll',    'coe_nominal_roll',      'coe/coe_nominalroll',    7,    '(''coe_nominalroll'', ''can_view'')',    1000, 'coe_nominalroll',    'index',                                                               NULL, 1, NOW(), NOW()),
  (309, @coe_menu_id, 'Exam Schedule',        'coe_schedule',       'coe_schedule',          'coe/coe_schedule',       8,    '(''coe_schedule'', ''can_view'')',       1000, 'coe_schedule',       'index',                                                               NULL, 1, NOW(), NOW()),
  (291, @coe_menu_id, 'Seating Arrangement',  'coe_seating',        'coe_seating',           'coe/coe_seating',        9,    '(''coe_seating'', ''can_view'')',        1000, 'coe_seating',        'index',                                                               NULL, 1, NOW(), NOW()),
  (292, @coe_menu_id, 'Invigilation Duty',    'coe_invigilation',   'coe_invigilation',      'coe/coe_invigilation',   10,   '(''coe_invigilation'', ''can_view'')',   1000, 'coe_invigilation',   'index',                                                               NULL, 1, NOW(), NOW()),
  (293, @coe_menu_id, 'Question Paper Dist.', 'coe_qpd',            'coe_qpd',               'coe/coe_qpd',            11,   '(''coe_qpd'', ''can_view'')',            2000, 'coe_qpd',            'index',                                                               NULL, 1, NOW(), NOW()),
  (294, @coe_menu_id, 'Exam Attendance',      'coe_attendance',     'coe_attendance',        'coe/coe_attendance',     12,   '(''coe_attendance'', ''can_view'')',     2000, 'coe_attendance',     'index',                                                               NULL, 1, NOW(), NOW()),
  (295, @coe_menu_id, 'UFM / Malpractice',    'coe_ufm',            'coe_ufm',               'coe/coe_ufm',            13,   '(''coe_ufm'', ''can_view'')',            2000, 'coe_ufm',            'index',                                                               NULL, 1, NOW(), NOW()),
  (310, @coe_menu_id, 'Flying Squad',         'coe_flyingsquad',    'coe_flyingsquad',        'coe/coe_flyingsquad',   14,   '(''coe_flyingsquad'', ''can_view'')',    1000, 'coe_flyingsquad',    'index',                                                               NULL, 1, NOW(), NOW()),
  (296, @coe_menu_id, 'Answer Scripts',       'coe_answer_scripts', NULL,                    'coe/coe_answer_scripts', 15,   '(''coe_answer_scripts'', ''can_view'')', 2000, 'coe_answer_scripts', 'index',                                                               NULL, 1, NOW(), NOW()),
  (297, @coe_menu_id, 'OSM Marking',          'coe_osm',            NULL,                    'coe/coe_osm',            16,   '(''coe_osm'', ''can_view'')',            2000, 'coe_osm',            'index',                                                               NULL, 1, NOW(), NOW()),
  (299, @coe_menu_id, 'Moderation',           'coe_moderation',     NULL,                    'coe/coe_moderation',     17,   '(''coe_moderation'', ''can_view'')',     2000, 'coe_moderation',     'index',                                                               NULL, 1, NOW(), NOW()),
  (298, @coe_menu_id, 'Revaluation',          'coe_revaluation',    NULL,                    'coe/coe_revaluation',    18,   '(''coe_revaluation'', ''can_view'')',    2000, 'coe_revaluation',    'index',                                                               NULL, 1, NOW(), NOW()),
  (300, @coe_menu_id, 'Marks & Results',      'coe_marks',          NULL,                    'coe/coe_marks',          19,   '(''coe_marks'', ''can_view'')',          2000, 'coe_marks',          'index',                                                               NULL, 1, NOW(), NOW()),
  (301, @coe_menu_id, 'Result Publication',   'coe_results',        NULL,                    'coe/coe_results',        20,   '(''coe_results'', ''can_view'')',        2000, 'coe_results',        'index',                                                               NULL, 1, NOW(), NOW()),
  (303, @coe_menu_id, 'Arrear Register',      'coe_arrear',         'coe_arrear',            'coe/coe_arrear',         21,   '(''coe_arrear'', ''can_view'')',         NULL, 'coe_arrear',         'index,student',                                                       NULL, 1, NOW(), NOW());

-- Fix level ordering for already-deployed instances (idempotent UPDATE)
-- This corrects level values that were inserted with old duplicate values.
UPDATE `sidebar_sub_menus` SET `level` = 1  WHERE `id` = 302;
UPDATE `sidebar_sub_menus` SET `level` = 2  WHERE `id` = 286;
UPDATE `sidebar_sub_menus` SET `level` = 3  WHERE `id` = 287;
UPDATE `sidebar_sub_menus` SET `level` = 4  WHERE `id` = 306;
UPDATE `sidebar_sub_menus` SET `level` = 5  WHERE `id` = 288;
UPDATE `sidebar_sub_menus` SET `level` = 6  WHERE `id` = 289;
UPDATE `sidebar_sub_menus` SET `level` = 7  WHERE `id` = 290;
UPDATE `sidebar_sub_menus` SET `level` = 8  WHERE `id` = 309;
UPDATE `sidebar_sub_menus` SET `level` = 9  WHERE `id` = 291;
UPDATE `sidebar_sub_menus` SET `level` = 10 WHERE `id` = 292;
UPDATE `sidebar_sub_menus` SET `level` = 11 WHERE `id` = 293;
UPDATE `sidebar_sub_menus` SET `level` = 12 WHERE `id` = 294;
UPDATE `sidebar_sub_menus` SET `level` = 13 WHERE `id` = 295;
UPDATE `sidebar_sub_menus` SET `level` = 14 WHERE `id` = 310;
UPDATE `sidebar_sub_menus` SET `level` = 15 WHERE `id` = 296;
UPDATE `sidebar_sub_menus` SET `level` = 16 WHERE `id` = 297;
UPDATE `sidebar_sub_menus` SET `level` = 17 WHERE `id` = 299;
UPDATE `sidebar_sub_menus` SET `level` = 18 WHERE `id` = 298;
UPDATE `sidebar_sub_menus` SET `level` = 19 WHERE `id` = 300;
UPDATE `sidebar_sub_menus` SET `level` = 20 WHERE `id` = 301;
UPDATE `sidebar_sub_menus` SET `level` = 21 WHERE `id` = 303;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Verification queries (run manually to confirm success):
-- SELECT COUNT(*) AS coe_tables FROM information_schema.tables
--   WHERE table_schema = DATABASE() AND table_name LIKE 'coe_%';
-- SELECT is_end_semester, exam_category FROM exam_groups LIMIT 0;
-- SELECT is_end_semester, coe_locked, class_id FROM exam_group_class_batch_exams LIMIT 0;
-- SELECT id, name FROM permission_group WHERE id = 2000;
-- SELECT COUNT(*) FROM permission_category WHERE perm_group_id = 2000;
-- SELECT COUNT(*) FROM sidebar_sub_menus
--   WHERE sidebar_menu_id = (SELECT id FROM sidebar_menus WHERE activate_menu='coe');
-- =============================================================================
