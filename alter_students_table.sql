-- IMPORTANT: Backup your database before running these commands.

-- Step 1: Add PRIMARY KEY and AUTO_INCREMENT to the 'id' column
-- This assumes 'id' is currently just a NOT NULL int.
-- If 'id' already has a primary key, this step might fail or be redundant.
-- If 'id' is not unique, this will fail. Ensure 'id' values are unique.
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (`id`);

-- Step 2: Change 'category_id' to INT(11) if it references an INT primary key in the 'categories' table.
-- If you have non-numeric data in 'category_id', this will cause an error.
-- If 'categories.id' is VARCHAR, then skip this step and keep category_id as VARCHAR(100).
ALTER TABLE `students`
  MODIFY `category_id` int(11) DEFAULT NULL;

-- Step 3: Add the missing columns
ALTER TABLE `students`
  ADD COLUMN `register_no` varchar(50) DEFAULT NULL AFTER `id`,
  ADD COLUMN `regulation_id` varchar(50) DEFAULT NULL AFTER `register_no`,
  ADD COLUMN `emis_num` varchar(50) DEFAULT NULL AFTER `regulation_id`,
  ADD COLUMN `hsc_reg_no` varchar(50) DEFAULT NULL AFTER `emis_num`,
  ADD COLUMN `ug_reg_no` varchar(50) DEFAULT NULL AFTER `hsc_reg_no`,
  ADD COLUMN `abc_id` varchar(50) DEFAULT NULL AFTER `adhar_no`, -- Placed after adhar_no as per the full schema
  ADD COLUMN `father_adhar_no` varchar(12) DEFAULT NULL AFTER `abc_id`,
  ADD COLUMN `mother_adhar_no` varchar(12) DEFAULT NULL AFTER `father_adhar_no`,
  ADD COLUMN `migration_cert_num` varchar(50) DEFAULT NULL AFTER `mother_adhar_no`,
  ADD COLUMN `medium` varchar(50) DEFAULT NULL AFTER `migration_cert_num`;

-- Step 4: Add DEFAULT values for existing NOT NULL columns that might not have them
-- This prevents future insert errors if values are not provided.
ALTER TABLE `students`
  MODIFY `blood_group` varchar(200) NOT NULL DEFAULT '',
  MODIFY `guardian_is` varchar(100) NOT NULL DEFAULT '',
  MODIFY `guardian_occupation` varchar(150) NOT NULL DEFAULT '',
  MODIFY `father_pic` varchar(200) NOT NULL DEFAULT '',
  MODIFY `mother_pic` varchar(200) NOT NULL DEFAULT '',
  MODIFY `guardian_pic` varchar(200) NOT NULL DEFAULT '',
  MODIFY `height` varchar(100) NOT NULL DEFAULT '',
  MODIFY `weight` varchar(100) NOT NULL DEFAULT '',
  MODIFY `measurement_date` date DEFAULT NULL,
  MODIFY `dis_reason` int(11) NOT NULL DEFAULT 0,
  MODIFY `note` varchar(200) DEFAULT NULL,
  MODIFY `dis_note` text NOT NULL DEFAULT '';

-- Step 5: Ensure parent_id has a default value if it's NOT NULL
ALTER TABLE `students`
  MODIFY `parent_id` int(11) NOT NULL DEFAULT 0;

-- Step 6: Add the missing `created_at` and `updated_at` columns if they are not already there
-- Your provided schema already has these, but including for completeness if they were missing.
-- If these columns already exist, these ADD COLUMN statements will fail.
-- Please check your table schema before running this step.
ALTER TABLE `students`
  ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();