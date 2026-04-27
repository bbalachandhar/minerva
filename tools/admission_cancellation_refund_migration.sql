-- ============================================================
-- Admission Cancellation & Refund Feature Migration
-- Date: 2026-04-27
-- Description: Adds admission_status, cancellation tracking
--              columns to online_admissions and creates the
--              admission_refunds table for refund lifecycle management.
-- ============================================================

-- 1. Add cancellation columns to online_admissions (idempotent)
ALTER TABLE `online_admissions`
    ADD COLUMN IF NOT EXISTS `admission_status`      ENUM('active','cancelled') NOT NULL DEFAULT 'active' COMMENT 'active=normal, cancelled=revoked by staff',
    ADD COLUMN IF NOT EXISTS `cancelled_at`          DATETIME     DEFAULT NULL  COMMENT 'Timestamp when admission was cancelled',
    ADD COLUMN IF NOT EXISTS `cancelled_by`          INT(11)      DEFAULT NULL  COMMENT 'Staff ID who cancelled the admission',
    ADD COLUMN IF NOT EXISTS `cancellation_reason`   TEXT         DEFAULT NULL  COMMENT 'Reason provided by staff for cancellation';

-- 2. Index for fast filtered queries (skip cancelled on lists)
ALTER TABLE `online_admissions`
    ADD INDEX IF NOT EXISTS `idx_admission_status` (`admission_status`);

-- 3. Refund tracking table
CREATE TABLE IF NOT EXISTS `admission_refunds` (
    `id`                  INT(11)      NOT NULL AUTO_INCREMENT,
    `online_admission_id` INT(11)      NOT NULL                       COMMENT 'FK → online_admissions.id',
    `reference_no`        VARCHAR(50)  NOT NULL                       COMMENT 'Applicant reference number (denormalised)',
    `applicant_name`      VARCHAR(255) NOT NULL DEFAULT ''            COMMENT 'Applicant full name (denormalised for reports)',
    `total_paid_amount`   DECIMAL(12,2) NOT NULL DEFAULT 0.00        COMMENT 'Total amount paid by applicant at time of cancellation',
    `refund_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0.00        COMMENT 'Amount approved for refund (may be partial after deductions)',
    `refund_mode`         VARCHAR(50)  DEFAULT NULL                   COMMENT 'cash | neft | upi | cheque | dd | online',
    `refund_reference_no` VARCHAR(100) DEFAULT NULL                   COMMENT 'UTR / cheque no. / DD no. filled when processed',
    `refund_status`       ENUM('pending','processed','rejected')
                          NOT NULL DEFAULT 'pending'                  COMMENT 'Refund lifecycle status',
    `cancellation_reason` TEXT         DEFAULT NULL                   COMMENT 'Staff-entered reason for admission cancellation',
    `remarks`             TEXT         DEFAULT NULL                   COMMENT 'Additional notes (e.g. deduction reason)',
    `initiated_by`        INT(11)      DEFAULT NULL                   COMMENT 'Staff ID who initiated the cancellation',
    `initiated_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_by`        INT(11)      DEFAULT NULL                   COMMENT 'Staff ID who marked refund as processed/rejected',
    `processed_at`        DATETIME     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ar_oa_id`      (`online_admission_id`),
    KEY `idx_ar_reference`  (`reference_no`),
    KEY `idx_ar_status`     (`refund_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Tracks refunds for cancelled online admissions';

-- ============================================================
-- Run this script once on the production/dev database.
-- Safe to re-run (uses ADD COLUMN IF NOT EXISTS / CREATE TABLE IF NOT EXISTS).
-- ============================================================

-- 4. Register admission_cancellation module in RBAC permission_category
--    (Only inserts if the short_code does not already exist)
INSERT INTO `permission_category` (`name`, `short_code`)
SELECT 'Admission Cancellation', 'admission_cancellation'
WHERE NOT EXISTS (
    SELECT 1 FROM `permission_category` WHERE `short_code` = 'admission_cancellation'
);
