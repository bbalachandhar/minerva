CREATE TABLE `naac_c2_5_evaluation_process` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `evaluation_reforms_description` TEXT,
    `transparency_in_evaluation` TEXT,
    `grievance_redressal_mechanism` TEXT,
    `document_link_evaluation_policy` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);