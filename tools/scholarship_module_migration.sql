-- ============================================================
-- Scholarship Module Migration
-- Run once on each instance that needs this feature.
-- ============================================================

-- 1. Scholarship types master list (admin-managed, institution-neutral)
CREATE TABLE IF NOT EXISTS `scholarship_types` (
    `id`          INT          NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(300) NOT NULL,
    `description` TEXT         NULL,
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`  INT          NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. Applicant scholarship applications
CREATE TABLE IF NOT EXISTS `scholarship_applications` (
    `id`                   INT          NOT NULL AUTO_INCREMENT,
    `online_admission_id`  INT          NOT NULL,
    `scholarship_type_id`  INT          NOT NULL,
    `applicant_remarks`    TEXT         NULL,
    `document`             VARCHAR(255) NULL          COMMENT 'Stored filename under uploads/scholarship_docs/',
    `status`               ENUM('pending','verified','approved','rejected') NOT NULL DEFAULT 'pending',
    `verifier_id`          INT          NULL,
    `verifier_remarks`     TEXT         NULL,
    `verified_at`          DATETIME     NULL,
    `approver_id`          INT          NULL,
    `approver_remarks`     TEXT         NULL,
    `approved_at`          DATETIME     NULL,
    `created_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_applicant_scholarship` (`online_admission_id`, `scholarship_type_id`),
    KEY `fk_sa_type`   (`scholarship_type_id`),
    KEY `fk_sa_verifier` (`verifier_id`),
    KEY `fk_sa_approver` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Scholarship workflow settings (single-row; verifier + approver staff IDs)
CREATE TABLE IF NOT EXISTS `scholarship_settings` (
    `id`           INT NOT NULL AUTO_INCREMENT,
    `verifier_id`  INT NULL COMMENT 'staff.id of the verifier',
    `approver_id`  INT NULL COMMENT 'staff.id of the approver',
    `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default empty settings row
INSERT IGNORE INTO `scholarship_settings` (`id`, `verifier_id`, `approver_id`) VALUES (1, NULL, NULL);

-- 4. Seed the 15 scholarship types from the institution brochure
INSERT INTO `scholarship_types` (`name`, `sort_order`, `is_active`) VALUES
('Merit Scholarship based on HSC Marks (Engineering Cut off) / School Toppers / MAT-SET conducted by our college', 1, 1),
('Merit Scholarship for toppers from Government schools', 2, 1),
('Wards of Single parent based on their Income', 3, 1),
('Siblings of Alumni of Meenakshi Groups of Institutions', 4, 1),
('Siblings of current students studying in MCE, K.K Nagar, Chennai & AMACE, Kanchipuram', 5, 1),
('12th standard students of Meenakshi Group of Schools', 6, 1),
('Current Final Year UG Students of Meenakshi Group of Colleges for PG', 7, 1),
('Current Final year students of Meenakshi Ammal Polytechnic College for Lateral Entry', 8, 1),
('Ward of Faculty / Staff of Meenakshi Group of Institutions', 9, 1),
('Wards of Ex-Servicemen', 10, 1),
('State / District level sports students', 11, 1),
('NCC Students', 12, 1),
('Female Candidates seeking admission in Civil / Mechanical stream', 13, 1),
('Physically challenged candidates', 14, 1),
('Other special category decided by Management', 15, 1);
