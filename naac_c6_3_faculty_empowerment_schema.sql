CREATE TABLE `naac_c6_3_faculty_empowerment` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `welfare_measures_description` TEXT,
    `teachers_received_financial_support` INT,
    `professional_development_programs_organized` INT,
    `teachers_undergoing_fdp` INT,
    `performance_appraisal_system` TEXT,
    `document_link_welfare_policy` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);