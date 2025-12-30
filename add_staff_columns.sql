ALTER TABLE `staff`
ADD COLUMN `is_visiting_faculty` TINYINT(1) DEFAULT 0 AFTER `pincode`,
ADD COLUMN `is_part_time_faculty` TINYINT(1) DEFAULT 0 AFTER `is_visiting_faculty`,
ADD COLUMN `is_full_time_faculty` TINYINT(1) DEFAULT 0 AFTER `is_part_time_faculty`,
ADD COLUMN `previous_salary` DECIMAL(10, 2) DEFAULT 0.00 AFTER `is_full_time_faculty`,
ADD COLUMN `uan_no` VARCHAR(20) NULL AFTER `previous_salary`,
ADD COLUMN `pan_no` VARCHAR(20) NULL AFTER `uan_no`,
ADD COLUMN `previous_institution` VARCHAR(255) NULL AFTER `pan_no`,
ADD COLUMN `subject_expertise` VARCHAR(255) NULL AFTER `previous_institution`;