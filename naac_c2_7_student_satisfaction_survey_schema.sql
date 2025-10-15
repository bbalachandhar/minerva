CREATE TABLE `naac_c2_7_student_satisfaction_survey` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `survey_methodology` TEXT,
    `total_students_enrolled` INT,
    `total_students_surveyed` INT,
    `sss_analysis_report` TEXT,
    `action_taken_on_sss` TEXT,
    `document_link_survey_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);