ALTER TABLE `subject_timetable`
ADD COLUMN `period_id` INT(11) NULL AFTER `subject_group_subject_id`,
DROP COLUMN `time_from`,
DROP COLUMN `time_to`,
DROP COLUMN `start_time`,
DROP COLUMN `end_time`;
