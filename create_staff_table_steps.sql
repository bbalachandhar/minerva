
-- Step 1: Create a minimal staff table
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Step 2: Add columns one by one. If you encounter an error, please let me know which of the following statements caused it.

ALTER TABLE `staff` ADD `prefix` varchar(10) DEFAULT NULL;
ALTER TABLE `staff` ADD `ug_qualification` varchar(100) DEFAULT NULL;
ALTER TABLE `staff` ADD `pg_qualification` varchar(100) DEFAULT NULL;
ALTER TABLE `staff` ADD `higher_qualification` varchar(100) DEFAULT NULL;
ALTER TABLE `staff` ADD `qualified_exam` varchar(100) DEFAULT NULL;
ALTER TABLE `staff` ADD `subject_specialization` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `additional_qualification` text DEFAULT NULL;
ALTER TABLE `staff` ADD `employee_id` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `biometric_id` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `lang_id` int(11) NOT NULL;
ALTER TABLE `staff` ADD `currency_id` int(11) DEFAULT 0;
ALTER TABLE `staff` ADD `department` int(11) DEFAULT NULL;
ALTER TABLE `staff` ADD `designation` int(11) DEFAULT NULL;
ALTER TABLE `staff` ADD `qualification` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `work_exp` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `name` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `surname` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `father_name` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `mother_name` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `contact_no` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `emergency_contact_no` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `email` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `dob` date DEFAULT NULL;
ALTER TABLE `staff` ADD `marital_status` varchar(100) NOT NULL;
ALTER TABLE `staff` ADD `date_of_joining` date DEFAULT NULL;
ALTER TABLE `staff` ADD `date_of_leaving` date DEFAULT NULL;
ALTER TABLE `staff` ADD `local_address` varchar(300) NOT NULL;
ALTER TABLE `staff` ADD `permanent_address` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `note` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `image` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `password` varchar(250) NOT NULL;
ALTER TABLE `staff` ADD `gender` varchar(50) NOT NULL;
ALTER TABLE `staff` ADD `account_title` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `bank_account_no` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `bank_name` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `ifsc_code` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `bank_branch` varchar(100) NOT NULL;
ALTER TABLE `staff` ADD `payscale` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `basic_salary` int(11) DEFAULT NULL;
ALTER TABLE `staff` ADD `epf_no` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `contract_type` varchar(100) NOT NULL;
ALTER TABLE `staff` ADD `shift` varchar(100) NOT NULL;
ALTER TABLE `staff` ADD `location` varchar(100) NOT NULL;
ALTER TABLE `staff` ADD `facebook` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `twitter` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `linkedin` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `instagram` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `resume` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `joining_letter` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `resignation_letter` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `other_document_name` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `other_document_file` varchar(200) NOT NULL;
ALTER TABLE `staff` ADD `user_id` int(11) NOT NULL;
ALTER TABLE `staff` ADD `is_active` int(11) NOT NULL;
ALTER TABLE `staff` ADD `verification_code` varchar(100) NOT NULL;
ALTER TABLE `staff` ADD `disable_at` date DEFAULT NULL;
ALTER TABLE `staff` ADD `created_at` timestamp NOT NULL DEFAULT current_timestamp();
ALTER TABLE `staff` ADD `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp();
ALTER TABLE `staff` ADD `app_key` text DEFAULT NULL;
ALTER TABLE `staff` ADD `previous_institution` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `subject_expertise` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `aadhaar_no` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `religion` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `caste` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `blood_group` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `country` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `state` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `pincode` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `is_visiting_faculty` tinyint(1) DEFAULT NULL;
ALTER TABLE `staff` ADD `is_part_time_faculty` tinyint(1) DEFAULT NULL;
ALTER TABLE `staff` ADD `is_full_time_faculty` tinyint(1) DEFAULT NULL;
ALTER TABLE `staff` ADD `previous_salary` decimal(10,2) DEFAULT NULL;
ALTER TABLE `staff` ADD `uan_no` varchar(255) DEFAULT NULL;
ALTER TABLE `staff` ADD `pan_no` varchar(255) DEFAULT NULL;

-- Step 3: Add Keys and auto-increment
ALTER TABLE `staff`
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `biometric_id` (`biometric_id`),
  ADD KEY `department` (`department`),
  ADD KEY `designation` (`designation`);

-- Step 4: Insert the data
INSERT INTO `staff` (`id`, `prefix`, `ug_qualification`, `pg_qualification`, `higher_qualification`, `qualified_exam`, `subject_specialization`, `additional_qualification`, `employee_id`, `biometric_id`, `lang_id`, `currency_id`, `department`, `designation`, `qualification`, `work_exp`, `name`, `surname`, `father_name`, `mother_name`, `contact_no`, `emergency_contact_no`, `email`, `dob`, `marital_status`, `date_of_joining`, `date_of_leaving`, `local_address`, `permanent_address`, `note`, `image`, `password`, `gender`, `account_title`, `bank_account_no`, `bank_name`, `ifsc_code`, `bank_branch`, `payscale`, `basic_salary`, `epf_no`, `contract_type`, `shift`, `location`, `facebook`, `twitter`, `linkedin`, `instagram`, `resume`, `joining_letter`, `resignation_letter`, `other_document_name`, `other_document_file`, `user_id`, `is_active`, `verification_code`, `disable_at`, `created_at`, `updated_at`, `app_key`, `previous_institution`, `subject_expertise`, `aadhaar_no`, `religion`, `caste`, `blood_group`, `country`, `state`, `pincode`, `is_visiting_faculty`, `is_part_time_faculty`, `is_full_time_faculty`, `previous_salary`, `uan_no`, `pan_no`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9000', '9000', 0, 0, NULL, NULL, '', '', 'Super Admin', '', '', '', '', '', 'b.balachandhar@gmail.com', '2020-01-01', '', NULL, NULL, '', '', '', '', '$2y$10$1e2Ap50oRAHImb90sLHJb.z4BMMHPRBhhQ9qJxobRixixzrFwV4oq', 'Male', '', '', '', '', '', '', NULL, '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 1, '', NULL, '2025-09-25 15:40:56', '2025-12-22 06:59:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
