-- =============================================================================
-- CoE Module Sprint 14 — DB Migration
-- New tables for: QPD download log, exam schedule, override approvals,
-- revaluation marks log, flying squad, arrear applications
-- EC2-compatible: uses INFORMATION_SCHEMA checks (no IF NOT EXISTS on ALTER)
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------
-- 1. QPD per-download audit log (IP address tracking)
-- -----------------------------------------------------------------------
SET @tbl = 'coe_qpd_download_log';
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'coe_qpd_download_log') > 0,
  'SELECT ''coe_qpd_download_log already exists''',
  'CREATE TABLE coe_qpd_download_log (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    paper_id INT NOT NULL,
    staff_id INT NOT NULL,
    downloaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    INDEX idx_paper (paper_id),
    INDEX idx_staff (staff_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------------------
-- 2. Batch exam subject schedule (per-subject date/time)
-- -----------------------------------------------------------------------
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'coe_exam_schedule') > 0,
  'SELECT ''coe_exam_schedule already exists''',
  'CREATE TABLE coe_exam_schedule (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    exam_group_class_batch_exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    session_slot ENUM(''FN'',''AN'') NOT NULL DEFAULT ''FN'',
    hall_id INT DEFAULT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sch_sub (exam_group_class_batch_exam_id, subject_id),
    INDEX idx_bid (exam_group_class_batch_exam_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------------------
-- 3. Eligibility override 2-step approval requests
-- -----------------------------------------------------------------------
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'coe_override_approval_requests') > 0,
  'SELECT ''coe_override_approval_requests already exists''',
  'CREATE TABLE coe_override_approval_requests (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    batch_exam_id INT NOT NULL,
    student_id INT NOT NULL,
    requested_by INT NOT NULL,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reason TEXT DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    status ENUM(''pending'',''approved'',''rejected'') NOT NULL DEFAULT ''pending'',
    approver_remarks TEXT DEFAULT NULL,
    INDEX idx_app (application_id),
    INDEX idx_batch (batch_exam_id),
    INDEX idx_status (status)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------------------
-- 4. Revaluation marks delta-audit log
-- -----------------------------------------------------------------------
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'coe_revaluation_marks_log') > 0,
  'SELECT ''coe_revaluation_marks_log already exists''',
  'CREATE TABLE coe_revaluation_marks_log (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    request_id INT NOT NULL,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_group_class_batch_exam_id INT NOT NULL,
    original_external FLOAT(10,2) NOT NULL DEFAULT 0,
    revised_external FLOAT(10,2) NOT NULL DEFAULT 0,
    delta FLOAT(10,2) NOT NULL DEFAULT 0,
    updated_by INT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    applied_to_results TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_req (request_id),
    INDEX idx_student (student_id),
    INDEX idx_batch (exam_group_class_batch_exam_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------------------
-- 5. Flying squad visit records
-- -----------------------------------------------------------------------
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'coe_flying_squad_visits') > 0,
  'SELECT ''coe_flying_squad_visits already exists''',
  'CREATE TABLE coe_flying_squad_visits (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    exam_group_class_batch_exam_id INT NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME DEFAULT NULL,
    observer_staff_id INT NOT NULL,
    hall_id INT DEFAULT NULL,
    hall_name VARCHAR(200) DEFAULT NULL,
    observations TEXT DEFAULT NULL,
    irregularities_found TINYINT(1) NOT NULL DEFAULT 0,
    irregularity_details TEXT DEFAULT NULL,
    action_taken TEXT DEFAULT NULL,
    severity ENUM(''none'',''minor'',''major'') NOT NULL DEFAULT ''none'',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch (exam_group_class_batch_exam_id),
    INDEX idx_date (visit_date)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- -----------------------------------------------------------------------
-- 6. Arrear self-service applications (submitted by student from portal)
-- -----------------------------------------------------------------------
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'coe_arrear_applications') > 0,
  'SELECT ''coe_arrear_applications already exists''',
  'CREATE TABLE coe_arrear_applications (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    exam_group_class_batch_exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    application_type ENUM(''arrear'',''supplementary'') NOT NULL DEFAULT ''arrear'',
    remarks TEXT DEFAULT NULL,
    fee_amount DECIMAL(10,2) DEFAULT NULL,
    fee_paid TINYINT(1) NOT NULL DEFAULT 0,
    fee_ref VARCHAR(100) DEFAULT NULL,
    status ENUM(''pending'',''approved'',''rejected'') NOT NULL DEFAULT ''pending'',
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    reviewer_remarks TEXT DEFAULT NULL,
    applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_arrear_sub (student_id, exam_group_class_batch_exam_id, subject_id),
    INDEX idx_student (student_id),
    INDEX idx_batch (exam_group_class_batch_exam_id),
    INDEX idx_status (status)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET foreign_key_checks = 1;

-- -----------------------------------------------------------------------
-- Verification
-- -----------------------------------------------------------------------
SELECT table_name, table_rows
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'coe_qpd_download_log',
    'coe_exam_schedule',
    'coe_override_approval_requests',
    'coe_revaluation_marks_log',
    'coe_flying_squad_visits',
    'coe_arrear_applications'
  )
ORDER BY table_name;
