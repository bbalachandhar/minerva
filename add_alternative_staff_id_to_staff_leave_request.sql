ALTER TABLE `staff_leave_request`
ADD COLUMN `alternative_staff_id` INT(11) NULL DEFAULT NULL AFTER `approver_status`;
