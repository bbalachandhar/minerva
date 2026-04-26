-- -------------------------------------------------------
-- Migration: Transport & Hostel per-student fee overrides
-- Date: 2026-04-26
-- Safe to run multiple times (idempotent)
-- -------------------------------------------------------

-- 1. Add fee_override column to student_transport_fees (if not already present)
ALTER TABLE `student_transport_fees`
  ADD COLUMN IF NOT EXISTS `fee_override` float(10,2) DEFAULT NULL
    COMMENT 'Per-student fee override; NULL = use route_pickup_point.fees';

-- 2. Create hostel fee override table (if not already present)
CREATE TABLE IF NOT EXISTS `student_fee_overrides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_session_id` int(11) NOT NULL,
  `fee_groups_feetype_id` int(11) NOT NULL,
  `override_amount` decimal(10,2) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_feetype` (`student_session_id`,`fee_groups_feetype_id`),
  KEY `fee_groups_feetype_id` (`fee_groups_feetype_id`),
  CONSTRAINT `sfo_student_session_ibfk` FOREIGN KEY (`student_session_id`) REFERENCES `student_session` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sfo_fee_groups_feetype_ibfk` FOREIGN KEY (`fee_groups_feetype_id`) REFERENCES `fee_groups_feetype` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
