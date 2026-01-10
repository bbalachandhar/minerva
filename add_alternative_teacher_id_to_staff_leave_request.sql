-- Migration to add alternative_teacher_id to staff_leave_request table
ALTER TABLE `staff_leave_request`
ADD COLUMN `alternative_teacher_id` INT(11) NULL DEFAULT NULL AFTER `approver_status`;
